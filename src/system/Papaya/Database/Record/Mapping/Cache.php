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

/**
* Mapper object to convert a database fields into object properties and back. It caches the
* results of functions call to the orginal mapping class and the callback functions.
*
* It will not cache the result of the property/record value mappings.
*
* @package Papaya-Library
* @subpackage Database
*/
class PapayaDatabaseRecordMappingCache implements \Papaya\Database\Interfaces\Mapping {

  /**
   * @var \Papaya\Database\Interfaces\Mapping
   */
  private $_mapping = NULL;
  /**
   * @var array(PapayaObjectCallback)
   */
  private $_callbacks = array();

  /**
   * @var array
   */
  private $_results = array();

  public function __construct(\Papaya\Database\Interfaces\Mapping $mapping) {
    $this->_mapping = $mapping;
    if ($mapping instanceof \PapayaDatabaseRecordMapping) {
      foreach ($mapping->callbacks() as $event => $callback) {
        if (isset($callback->callback) || isset($callback->defaultReturn)) {
          $this->_callbacks[$event] = $callback;
        }
      }
    }
  }

  /**
  * Map the database fields of an record to the object properties
  *
  * @param array $record
  * @return array
  */
  function mapFieldsToProperties(array $record) {
    $callbacks = $this->_callbacks;
    $values = array();
    if (isset($callbacks['onBeforeMappingFieldsToProperties'])) {
      /** @noinspection PhpUndefinedMethodInspection */
      $values = $callbacks['onBeforeMappingFieldsToProperties']->execute(
        $values, $record
      );
    }
    if (isset($callbacks['onBeforeMapping'])) {
      /** @noinspection PhpUndefinedMethodInspection */
      $values = $callbacks['onBeforeMapping']->execute(
        self::FIELD_TO_PROPERTY, $values, $record
      );
    }
    foreach ($record as $field => $value) {
      if ($property = $this->getProperty($field)) {
        if (isset($callbacks['onMapValueFromFieldToProperty'])) {
          /** @noinspection PhpUndefinedMethodInspection */
          $value = $callbacks['onMapValueFromFieldToProperty']->execute(
            $property, $field, $value
          );
        }
        if (isset($callbacks['onMapValue'])) {
          /** @noinspection PhpUndefinedMethodInspection */
          $value = $callbacks['onMapValue']->execute(
            self::FIELD_TO_PROPERTY, $property, $field, $value
          );
        }
        $values[$property] = $value;
      }
    }
    if (isset($callbacks['onAfterMappingFieldsToProperties'])) {
      /** @noinspection PhpUndefinedMethodInspection */
      $values = $callbacks['onAfterMappingFieldsToProperties']->execute(
        $values, $record
      );
    }
    if (isset($callbacks['onAfterMapping'])) {
      /** @noinspection PhpUndefinedMethodInspection */
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
   * @return array
   */
  function mapPropertiesToFields(array $values, $withAlias = TRUE) {
    $callbacks = $this->_callbacks;
    $record = array();
    if (isset($callbacks['onBeforeMappingPropertiesToFields'])) {
      /** @noinspection PhpUndefinedMethodInspection */
      $record = $callbacks['onBeforeMappingPropertiesToFields']->execute(
        $values, $record
      );
    }
    if (isset($callbacks['onBeforeMapping'])) {
      /** @noinspection PhpUndefinedMethodInspection */
      $record = $callbacks['onBeforeMapping']->execute(
        self::PROPERTY_TO_FIELD, $values, $record
      );
    }
    foreach ($values as $property => $value) {
      if ($field = $this->getField($property, $withAlias)) {
        if (isset($callbacks['onMapValueFromPropertyToField'])) {
          /** @noinspection PhpUndefinedMethodInspection */
          $value = $callbacks['onMapValueFromPropertyToField']->execute(
            $property, $field, $value
          );
        }
        if (isset($callbacks['onMapValue'])) {
          /** @noinspection PhpUndefinedMethodInspection */
          $value = $callbacks['onMapValue']->execute(
            self::PROPERTY_TO_FIELD, $property, $field, $value
          );
        }
        $record[$field] = $value;
      }
    }
    if (isset($callbacks['onAfterMappingPropertiesToFields'])) {
      /** @noinspection PhpUndefinedMethodInspection */
      $record = $callbacks['onAfterMappingPropertiesToFields']->execute(
        $values, $record
      );
    }
    if (isset($callbacks['onAfterMapping'])) {
      /** @noinspection PhpUndefinedMethodInspection */
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
  function getProperties() {
    if (!isset($this->_results['getProperties'])) {
      $this->_results['getProperties'] = $this->_mapping->getProperties();
    }
    return $this->_results['getProperties'];
  }

  /**
   * Get a list of the used database fields
   *
   * @param bool $withAlias
   * @return array
   */
  function getFields($withAlias = TRUE) {
    if (!isset($this->_results['getFields'][$withAlias])) {
      $this->_results['getFields'][$withAlias] = $this->_mapping->getFields($withAlias);
    }
    return $this->_results['getFields'][$withAlias];
  }

  /**
   * Get the property name for a field
   *
   * @param $field
   * @return string|FALSE
   */
  function getProperty($field) {
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
   * @return string|FALSE
   */
  function getField($property, $withAlias = TRUE) {
    if (!isset($this->_results['getField'][$property][$withAlias])) {
      $this->_results['getField'][$property][$withAlias] = $this->_mapping->getField(
        $property, $withAlias
      );
    }
    return $this->_results['getField'][$property][$withAlias];
  }
}
