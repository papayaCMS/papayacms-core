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

namespace Papaya\Database\BaseObject;
/**
 * Papaya Database Record List, a list of records from a database.
 *
 * A mapping property defines simple names for the fields.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
abstract class Records
  extends \Papaya\Database\BaseObject
  implements \IteratorAggregate, \Countable {

  /**
   * Absolute record count (for paging)
   *
   * @var integer
   */
  protected $_recordCount = 0;

  /**
   * Record storage
   *
   * @var array(array())
   */
  protected $_records = array();

  /**
   * Map fields to application names
   *
   * @var array($fieldName => $name)
   */
  protected $_fieldMapping = array();

  /**
   * IteratorAggregate interface: Get a ArrayIterator for the records
   *
   * @return \ArrayIterator
   */
  public function getIterator() {
    return new \ArrayIterator($this->_records);
  }

  /**
   * Countable interface: Get count of loaded records
   *
   * @return integer
   */
  public function count() {
    return count($this->_records);
  }

  /**
   * Get count without limits, returns {@see \Papaya\Database\BaseObject\PapayaDatabaseObjectList::count()} if
   * this value is larger.
   *
   * @return integer
   */
  public function countAll() {
    $current = $this->count();
    return ($this->_recordCount > $current) ? $this->_recordCount : $current;
  }

  /**
   * Get an element by offset (allow direct access without the iterator)
   *
   * Returns NULL if the offset is invalid.
   *
   * @param string|integer $offset
   * @return array|NULL
   */
  public function item($offset) {
    return (isset($this->_records[$offset])) ? $this->_records[$offset] : NULL;
  }

  /**
   * Get an item by its position in the records array
   *
   * @param integer $position
   * @return array
   */
  public function itemAt($position) {
    $list = array_values($this->_records);
    if ($position < 0) {
      $position = count($list) + $position;
    }
    return (isset($list[$position])) ? $list[$position] : NULL;
  }

  /**
   * Assign an array to this object as the replacement for all records.
   *
   * @param array|\Traversable $data
   * @return array
   */
  public function assign($data) {
    $this->_records = array();
    foreach (\PapayaUtilArray::ensure($data) as $id => $row) {
      $record = array();
      foreach ($row as $field => $value) {
        if (in_array($field, $this->_fieldMapping)) {
          $record[$field] = $value;
        }
      }
      $this->_records[$id] = $record;
    }
    $this->_recordCount = count($this->_records);
  }

  /**
   * Load records from database using a sql and parameters.
   *
   * If an $idProperty is provided, it will be used as the index.
   *
   * @param string $sql
   * @param array $parameters
   * @param string|NULL $idField
   * @param integer|NULL $limit
   * @param integer|NULL $offset
   * @return boolean TRUE on success otherwise FALSE
   */
  protected function _loadRecords(
    $sql, $parameters, $idField = NULL, $limit = NULL, $offset = NULL
  ) {
    $this->_records = array();
    $this->_recordCount = 0;
    if ($databaseResult = $this->databaseQueryFmt($sql, $parameters, $limit, $offset)) {
      $this->_fetchRecords($databaseResult, $idField);
      $this->_recordCount = $databaseResult->absCount();
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Converts the record from database into a values array using the mapping array.
   *
   * @param \Papaya\Database\Result $databaseResult
   * @param string $idField
   */
  protected function _fetchRecords($databaseResult, $idField = '') {
    $this->_records = array();
    while ($row = $databaseResult->fetchRow(\Papaya\Database\Result::FETCH_ASSOC)) {
      $record = array();
      foreach ($row as $field => $value) {
        if (!empty($this->_fieldMapping[$field])) {
          $record[$this->_fieldMapping[$field]] = $value;
        }
      }
      if (empty($idField)) {
        $this->_records[] = $record;
      } else {
        $this->_records[$row[$idField]] = $record;
      }
    }
  }
}
