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

use Papaya\Application;
use Papaya\Database;
use Papaya\Utility;

/**
 * Papaya Database Record List, a list of records from a database.
 *
 * A mapping property defines simple names for the fields.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
abstract class Records
   implements Application\Access, Database\Interfaces\Access, \IteratorAggregate, \Countable {
  use Database\Interfaces\Access\Delegation;

  /**
   * Absolute record count (for paging)
   *
   * @var int
   */
  protected $_recordCount = 0;

  /**
   * Record storage
   *
   * @var array(array())
   */
  protected $_records = [];

  /**
   * Map fields to application names
   *
   * @var array($fieldName => $name)
   */
  protected $_fieldMapping = [];

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
   * @return int
   */
  public function count() {
    return \count($this->_records);
  }

  /**
   * Get count without limits, returns {@see \Papaya\Database\BaseObject\Collection::count()} if
   * this value is larger.
   *
   * @return int
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
   * @param string|int $offset
   *
   * @return array|null
   */
  public function item($offset) {
    return isset($this->_records[$offset]) ? $this->_records[$offset] : NULL;
  }

  /**
   * Get an item by its position in the records array
   *
   * @param int $position
   *
   * @return array
   */
  public function itemAt($position) {
    $list = \array_values($this->_records);
    if ($position < 0) {
      $position = \count($list) + $position;
    }
    return isset($list[$position]) ? $list[$position] : NULL;
  }

  /**
   * Assign an array to this object as the replacement for all records.
   *
   * @param array|\Traversable $data
   */
  public function assign($data) {
    $this->_records = [];
    foreach (Utility\Arrays::ensure($data) as $id => $row) {
      $record = [];
      foreach ($row as $field => $value) {
        if (\in_array($field, $this->_fieldMapping, TRUE)) {
          $record[$field] = $value;
        }
      }
      $this->_records[$id] = $record;
    }
    $this->_recordCount = \count($this->_records);
  }

  /**
   * Load records from database using a sql and parameters.
   *
   * If an $idProperty is provided, it will be used as the index.
   *
   * @param string $sql
   * @param array $parameters
   * @param string|null $idField
   * @param int|null $limit
   * @param int|null $offset
   *
   * @return bool TRUE on success otherwise FALSE
   */
  protected function _loadRecords(
    $sql, $parameters, $idField = NULL, $limit = NULL, $offset = NULL
  ) {
    $this->_records = [];
    $this->_recordCount = 0;
    if ($databaseResult = $this->databaseQueryFmt($sql, $parameters, $limit, $offset)) {
      $this->_fetchRecords($databaseResult, $idField);
      $this->_recordCount = $databaseResult->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Converts the record from database into a values array using the mapping array.
   *
   * @param Database\Result $databaseResult
   * @param string $idField
   */
  protected function _fetchRecords($databaseResult, $idField = '') {
    $this->_records = [];
    while ($row = $databaseResult->fetchRow(Database\Result::FETCH_ASSOC)) {
      $record = [];
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
