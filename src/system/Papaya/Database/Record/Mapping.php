<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Database\Record;

use Papaya\Database;

/**
 * Mapper object to convert a database fields into object properties and back
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Mapping implements Database\Interfaces\Mapping {
  /**
   * Properties to fields mapping
   *
   * @var array(string=>string|NULL)
   */
  private $_properties = [];

  /**
   * Field to properties mapping
   *
   * @var array(string=>string|NULL)
   */
  private $_fields = [];

  /**
   * Field to properties mapping excluding table aliases, this is only used if the original
   * field name contains an . - indicating the use of an table alias.
   *
   * @var array(string=>string|NULL)
   */
  private $_fieldsWithoutAlias = [];

  /**
   * Callbacks to modify the mapping behaviour
   *
   * @var Mapping\Callbacks
   */
  private $_callbacks;

  /**
   * Create object and define mapping
   *
   * @param array(string=>string|NULL) $definition
   */
  public function __construct(array $definition) {
    $this->setDefinition($definition);
  }

  /**
   * Define mapping
   *
   * @param array(string=>string|NULL) $definition
   *
   * @throws \LogicException
   */
  private function setDefinition($definition) {
    $this->_properties = [];
    $this->_fields = [];
    foreach ($definition as $property => $field) {
      $this->_properties[$property] = $field;
      if (!empty($field)) {
        if (isset($this->_fields[$field])) {
          throw new \LogicException(
            \sprintf(
              'Duplicate database field "%s" in mapping definition.',
              $field
            )
          );
        }
        $this->_fields[$field] = $property;
        $this->_fieldsWithoutAlias[$this->stripAliasFromField($field)] = $property;
      }
    }
  }

  /**
   * Strip the alias from the field if here was one.
   *
   * @param $field
   *
   * @return string
   */
  private function stripAliasFromField($field) {
    if (FALSE !== ($position = \strpos($field, '.'))) {
      return \substr($field, $position + 1);
    }
    return $field;
  }

  /**
   * Map the database fields of an record to the object properties
   *
   * @param array $record
   *
   * @return array
   */
  public function mapFieldsToProperties(array $record) {
    $callbacks = $this->callbacks();
    $values = [];
    if (isset($callbacks->onBeforeMappingFieldsToProperties)) {
      $values = $callbacks->onBeforeMappingFieldsToProperties(
        $values, $record
      );
    }
    if (isset($callbacks->onBeforeMapping)) {
      $values = $callbacks->onBeforeMapping(
        self::FIELD_TO_PROPERTY, $values, $record
      );
    }
    $onMapValueFromFieldToProperty = NULL;
    $onMapValue = NULL;
    foreach ($record as $field => $value) {
      if ($property = $this->getProperty($field)) {
        if (NULL === $onMapValueFromFieldToProperty) {
          $onMapValueFromFieldToProperty = isset($callbacks->onMapValueFromFieldToProperty)
            ? $callbacks->onMapValueFromFieldToProperty
            : FALSE;
        }
        if (NULL === $onMapValue) {
          $onMapValue = isset($callbacks->onMapValue)
            ? $callbacks->onMapValue
            : FALSE;
        }
        if ($onMapValueFromFieldToProperty) {
          $value = $onMapValueFromFieldToProperty->execute(
            $property, $field, $value
          );
        }
        if ($onMapValue) {
          $value = $onMapValue->execute(
            self::FIELD_TO_PROPERTY, $property, $field, $value
          );
        }
        $values[$property] = $value;
      }
    }
    if (isset($callbacks->onAfterMappingFieldsToProperties)) {
      $values = $callbacks->onAfterMappingFieldsToProperties(
        $values, $record
      );
    }
    if (isset($callbacks->onAfterMapping)) {
      $values = $callbacks->onAfterMapping(
        self::FIELD_TO_PROPERTY, $values, $record
      );
    }
    return $values;
  }

  /**
   * Map the object properties to database fields
   *
   * @param array $values
   * @param bool $withAlias
   *
   * @return array
   */
  public function mapPropertiesToFields(array $values, $withAlias = TRUE) {
    $callbacks = $this->callbacks();
    $record = [];
    if (isset($callbacks->onBeforeMappingPropertiesToFields)) {
      $record = $callbacks->onBeforeMappingPropertiesToFields(
        $values, $record
      );
    }
    if (isset($callbacks->onBeforeMapping)) {
      $record = $callbacks->onBeforeMapping(
        self::PROPERTY_TO_FIELD, $values, $record
      );
    }
    $onMapValueFromPropertyToField = NULL;
    $onMapValue = NULL;
    foreach ($values as $property => $value) {
      if ($field = $this->getField($property, $withAlias)) {
        if (NULL === $onMapValueFromPropertyToField) {
          $onMapValueFromPropertyToField = isset($callbacks->onMapValueFromPropertyToField)
            ? $callbacks->onMapValueFromPropertyToField
            : FALSE;
        }
        if (NULL === $onMapValue) {
          $onMapValue = isset($callbacks->onMapValue)
            ? $callbacks->onMapValue
            : FALSE;
        }
        if ($onMapValueFromPropertyToField) {
          $value = $onMapValueFromPropertyToField->execute(
            $property, $field, $value
          );
        }
        if ($onMapValue) {
          $value = $onMapValue->execute(
            self::PROPERTY_TO_FIELD, $property, $field, $value
          );
        }
        $record[$field] = $value;
      }
    }
    if (isset($callbacks->onAfterMappingPropertiesToFields)) {
      $record = $callbacks->onAfterMappingPropertiesToFields(
        $values, $record
      );
    }
    if (isset($callbacks->onAfterMapping)) {
      $record = $callbacks->onAfterMapping(
        self::PROPERTY_TO_FIELD, $values, $record
      );
    }
    return $record;
  }

  /**
   * Get a list of the used properties
   *
   * @return array
   */
  public function getProperties() {
    return \array_keys($this->_properties);
  }

  public function getPropertiesMap(): array {
    return $this->_properties;
  }

  /**
   * Get a list of the used database fields
   *
   * @param bool|string $withAlias
   *
   * @return array
   */
  public function getFields($withAlias = TRUE) {
    return array_keys($this->getFieldsMap($withAlias));
  }

  /**
   * @param bool|string $withAlias
   * @return array
   */
  public function getFieldsMap($withAlias = TRUE): array {
    if ($withAlias) {
      if (is_string($withAlias)) {
        $prefix = $withAlias.'.';
        $prefixLength = \strlen($prefix);
        $result = [];
        foreach ($this->_fields as $field => $property) {
          if (0 === \strpos($field, $prefix)) {
            $result[\substr($field, $prefixLength)] = $property;
          }
        }
        return $result;
      }
      return $this->_fields;
    }
    return $this->_fieldsWithoutAlias;
  }

  /**
   * Get the database field name for a property
   *
   * @param string $property
   * @param bool|string $withAlias
   *
   * @return string|null
   */
  public function getField($property, $withAlias = TRUE) {
    $callbacks = $this->callbacks();
    $result = NULL;
    if (isset($callbacks->onGetFieldForProperty)) {
      $result = $callbacks->onGetFieldForProperty($property, $withAlias);
    }
    if (NULL === $result && isset($this->_properties[$property])) {
      $field = $this->_properties[$property];
      if ($withAlias) {
        if (\is_string($withAlias)) {
          return (0 === \strpos($field, $withAlias.'.'))
            ? $this->stripAliasFromField($field)
            : NULL;
        }
        return $field;
      }
      return $this->stripAliasFromField($field);
    }
    return $result;
  }

  /**
   * Get the property name for a database fields
   *
   * @param string $field
   *
   * @return string|null
   */
  public function getProperty($field) {
    $callbacks = $this->callbacks();
    $result = NULL;
    if (isset($callbacks->onGetPropertyForField)) {
      $result = $callbacks->onGetPropertyForField($field);
    }
    if (NULL === $result) {
      if (isset($this->_fields[$field])) {
        return $this->_fields[$field];
      }
      if (isset($this->_fieldsWithoutAlias[$field])) {
        return $this->_fieldsWithoutAlias[$field];
      }
    }
    return $result;
  }

  /**
   * Getter/Setter for the possible callbacks, to modify the behaviour of the mapping
   *
   * @param Mapping\Callbacks $callbacks
   *
   * @return Mapping\Callbacks
   */
  public function callbacks(Mapping\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new Mapping\Callbacks();
    }
    return $this->_callbacks;
  }
}
