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
namespace Papaya\Database\Record\Mapping;

use Papaya\Database;

/**
 * Mapper object to convert a database fields into object properties and back. It caches the
 * results of functions call to the original mapping class and the callback functions.
 *
 * It will not cache the result of the property/record value mappings.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Cache implements Database\Interfaces\Mapping {
  /**
   * @var Database\Interfaces\Mapping
   */
  private $_mapping;

  /**
   * @var \Papaya\BaseObject\Callback[]
   */
  private $_callbacks = [];

  /**
   * @var array
   */
  private $_results = [];

  public function __construct(Database\Interfaces\Mapping $mapping) {
    $this->_mapping = $mapping;
    if ($mapping instanceof Database\Record\Mapping) {
      foreach ($mapping->callbacks() as $event => $callback) {
        if (isset($callback->callback) || isset($callback->defaultReturn)) {
          $this->_callbacks[$event] = $callback;
        }
      }
    }
  }

  public function getMapping(): Database\Interfaces\Mapping {
    return $this->_mapping;
  }

  /**
   * Map the database fields of an record to the object properties
   *
   * @param array $record
   *
   * @return array
   */
  public function mapFieldsToProperties(array $record) {
    $callbacks = $this->_callbacks;
    $values = [];
    if (isset($callbacks['onBeforeMappingFieldsToProperties'])) {
      $values = $callbacks['onBeforeMappingFieldsToProperties']->execute(
        $values, $record
      );
    }
    if (isset($callbacks['onBeforeMapping'])) {
      $values = $callbacks['onBeforeMapping']->execute(
        self::FIELD_TO_PROPERTY, $values, $record
      );
    }
    foreach ($record as $field => $value) {
      if ($property = $this->getProperty($field)) {
        if (isset($callbacks['onMapValueFromFieldToProperty'])) {
          $value = $callbacks['onMapValueFromFieldToProperty']->execute(
            $property, $field, $value
          );
        }
        if (isset($callbacks['onMapValue'])) {
          $value = $callbacks['onMapValue']->execute(
            self::FIELD_TO_PROPERTY, $property, $field, $value
          );
        }
        $values[$property] = $value;
      }
    }
    if (isset($callbacks['onAfterMappingFieldsToProperties'])) {
      $values = $callbacks['onAfterMappingFieldsToProperties']->execute(
        $values, $record
      );
    }
    if (isset($callbacks['onAfterMapping'])) {
      $values = $callbacks['onAfterMapping']->execute(
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
    $callbacks = $this->_callbacks;
    $record = [];
    if (isset($callbacks['onBeforeMappingPropertiesToFields'])) {
      $record = $callbacks['onBeforeMappingPropertiesToFields']->execute(
        $values, $record
      );
    }
    if (isset($callbacks['onBeforeMapping'])) {
      $record = $callbacks['onBeforeMapping']->execute(
        self::PROPERTY_TO_FIELD, $values, $record
      );
    }
    foreach ($values as $property => $value) {
      if ($field = $this->getField($property, $withAlias)) {
        if (isset($callbacks['onMapValueFromPropertyToField'])) {
          $value = $callbacks['onMapValueFromPropertyToField']->execute(
            $property, $field, $value
          );
        }
        if (isset($callbacks['onMapValue'])) {
          $value = $callbacks['onMapValue']->execute(
            self::PROPERTY_TO_FIELD, $property, $field, $value
          );
        }
        $record[$field] = $value;
      }
    }
    if (isset($callbacks['onAfterMappingPropertiesToFields'])) {
      $record = $callbacks['onAfterMappingPropertiesToFields']->execute(
        $values, $record
      );
    }
    if (isset($callbacks['onAfterMapping'])) {
      $record = $callbacks['onAfterMapping']->execute(
        self::PROPERTY_TO_FIELD, $values, $record
      );
    }
    return $record;
  }

  /**
   * Get a list of the used database fields
   *
   * @return array
   */
  public function getProperties() {
    if (!isset($this->_results['getProperties'])) {
      $this->_results['getProperties'] = $this->_mapping->getProperties();
    }
    return $this->_results['getProperties'];
  }

  /**
   * Get a list of the used database fields
   *
   * @param bool $withAlias
   *
   * @return array
   */
  public function getFields($withAlias = TRUE) {
    if (!isset($this->_results['getFields'][$withAlias])) {
      $this->_results['getFields'][$withAlias] = $this->_mapping->getFields($withAlias);
    }
    return $this->_results['getFields'][$withAlias];
  }

  /**
   * Get the property name for a field
   *
   * @param $field
   *
   * @return string|false
   */
  public function getProperty($field) {
    if (!isset($this->_results['getProperty'][$field])) {
      $this->_results['getProperty'][$field] = $this->_mapping->getProperty($field);
    }
    return $this->_results['getProperty'][$field];
  }

  /**
   * Get the field name for a property
   *
   * @param $property
   * @param bool $withAlias
   *
   * @return string|false
   */
  public function getField($property, $withAlias = TRUE) {
    if (!isset($this->_results['getField'][$property][$withAlias])) {
      $this->_results['getField'][$property][$withAlias] = $this->_mapping->getField(
        $property, $withAlias
      );
    }
    return $this->_results['getField'][$property][$withAlias];
  }
}
