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

use Papaya\Database;

/**
 * An multiple field key that represents a link table index
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Fields implements Database\Interfaces\Key {
  /**
   * the key values
   *
   * @var array
   */
  private $_values = [];

  /**
   * Attached record for this key
   *
   * @var Database\Record
   */
  private $_record;

  /**
   * Key table name
   *
   * @var string
   */
  private $_tableName;

  /**
   * Create object and set the identifier property, the default
   *
   * @param Database\Record $record
   * @param $tableName
   * @param array $properties
   *
   * @internal param int|NULL $
   */
  public function __construct(Database\Record $record, $tableName, array $properties) {
    $this->_record = $record;
    $this->_tableName = $tableName;
    foreach ($properties as $property) {
      $this->_values[$property] = NULL;
    }
  }

  /**
   * Provide information about the key
   *
   * @var int
   *
   * @return int
   */
  public function getQualities() {
    return 0;
  }

  /**
   * Assign data to the key.
   *
   * @param array $data
   *
   * @return bool
   */
  public function assign(array $data) {
    $result = FALSE;
    foreach ($data as $name => $value) {
      if (\array_key_exists($name, $this->_values)) {
        $this->_values[$name] = $value;
        $result = TRUE;
      }
    }
    return $result;
  }

  /**
   * Validate if the record exists. This will require an query to the database.
   *
   * @return bool
   */
  public function exists() {
    $filter = [];
    $values = $this->getFilter();
    foreach ($this->_record->mapping()->mapPropertiesToFields($values, FALSE) as $field => $value) {
      if (NULL !== $value) {
        $filter[$field] = $value;
      }
    }
    if (empty($filter) || \count($filter) !== \count($values)) {
      return FALSE;
    }
    $databaseAccess = $this->_record->getDatabaseAccess();
    $condition = $databaseAccess->getSQLCondition($filter);
    $sql = "SELECT COUNT(*) FROM %s WHERE $condition";
    $parameters = [
      $databaseAccess->getTableName($this->_tableName)
    ];
    if ($databaseResult = $databaseAccess->queryFmt($sql, $parameters)) {
      return $databaseResult->fetchField() > 0;
    }
    return FALSE;
  }

  /**
   * Clear the key values
   */
  public function clear() {
    $this->_values = [];
  }

  /**
   * Convert the key values into an string, that can be used in array keys.
   *
   * @return string
   */
  public function __toString() {
    return \implode('|', $this->_values);
  }

  /**
   * Get the property names of the key.
   *
   * @return array(string)
   */
  public function getProperties() {
    return \array_keys($this->_values);
  }

  /**
   * Get the a property=>value array to use it. A mapping is used to convert it into acutal database
   * fields
   *
   * @param int $for the action the filter ist fetched for
   *
   * @return array(string)
   */
  public function getFilter($for = self::ACTION_FILTER) {
    $values = $this->_values;
    foreach ($values as $property => $value) {
      if (NULL === $value && isset($this->_record[$property])) {
        $values[$property] = $this->_record[$property];
      }
    }
    return $values;
  }
}
