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

namespace Papaya\Database\Record\Key;
/**
 * An multiple field key that represents a link table index
 *
 * @package Papaya-Library
 * @subpackage Database
 * @version $Id: Fields.php 39197 2014-02-11 13:36:56Z weinert $
 */
class Fields implements \Papaya\Database\Interfaces\Key {

  /**
   * the key values
   *
   * @var array
   */
  private $_values = array();

  /**
   * Attached record for this key
   *
   * @var \Papaya\Database\Record
   */
  private $_record = NULL;

  /**
   * Key table name
   *
   * @var string
   */
  private $_tableName = '';

  /**
   * Create object and set the identifier property, the default
   *
   * @param \Papaya\Database\Record $record
   * @param $tableName
   * @param array $properties
   * @internal param int|NULL $
   */
  public function __construct(\Papaya\Database\Record $record, $tableName, array $properties) {
    $this->_record = $record;
    $this->_tableName = $tableName;
    foreach ($properties as $property) {
      $this->_values[$property] = NULL;
    }
  }

  /**
   * Provide information about the key
   *
   * @var integer
   * @return int
   */
  public function getQualities() {
    return 0;
  }

  /**
   * Assign data to the key.
   *
   * @param array $data
   * @return bool
   */
  public function assign(array $data) {
    $result = FALSE;
    foreach ($data as $name => $value) {
      if (array_key_exists($name, $this->_values)) {
        $this->_values[$name] = $value;
        $result = TRUE;
      }
    }
    return $result;
  }

  /**
   * Validate if the record exists. This will require an query to the database.
   *
   * @return boolean
   */
  public function exists() {
    $filter = array();
    $values = $this->getFilter();
    foreach ($this->_record->mapping()->mapPropertiesToFields($values, FALSE) as $field => $value) {
      if (isset($value)) {
        $filter[$field] = $value;
      }
    }
    if (empty($filter) || count($filter) != count($values)) {
      return FALSE;
    }
    $databaseAccess = $this->_record->getDatabaseAccess();
    $condition = $databaseAccess->getSqlCondition($filter);
    $sql = "SELECT COUNT(*) FROM %s WHERE $condition";
    $parameters = array(
      $databaseAccess->getTableName($this->_tableName)
    );
    if ($databaseResult = $databaseAccess->queryFmt($sql, $parameters)) {
      return $databaseResult->fetchField() > 0;
    }
    return FALSE;
  }

  /**
   * Clear the key values
   */
  public function clear() {
    $this->_values = array();
  }

  /**
   * Convert the key values into an string, that can be used in array keys.
   *
   * @return string
   */
  public function __toString() {
    return implode('|', $this->_values);
  }

  /**
   * Get the property names of the key.
   *
   * @return array(string)
   */
  public function getProperties() {
    return array_keys($this->_values);
  }

  /**
   * Get the a property=>value array to use it. A mapping is used to convert it into acutal database
   * fields
   *
   * @param integer $for the action the filter ist fetched for
   * @return array(string)
   */
  public function getFilter($for = self::ACTION_FILTER) {
    $values = $this->_values;
    foreach ($values as $property => $value) {
      if (!isset($value) && isset($this->_record[$property])) {
        $values[$property] = $this->_record[$property];
      }
    }
    return $values;
  }
}
