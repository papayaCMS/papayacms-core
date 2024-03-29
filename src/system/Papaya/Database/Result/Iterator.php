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
namespace Papaya\Database\Result;

use Papaya\Database;

/**
 * Papaya Database Result Iterator, allows to iterate on an object implementing \Papaya\Database\Result
 *
 * You can specify it the records will be fetched with numerical index, field names or both.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Iterator implements \Iterator {
  /**
   * @var Database\Result
   */
  private $_databaseResult;

  /**
   * @var Database\Interfaces\Mapping
   */
  private $_mapping;

  /**
   * @var int
   */
  private $_fetchMode;

  /**
   * @var array|null
   */
  private $_current;

  /**
   * @var int
   */
  private $_offset = -1;

  /**
   * Create object, store result object and fetch mode
   *
   * @param Database\Result $databaseResult
   * @param int $mode
   */
  public function __construct(
    Database\Result $databaseResult = NULL, $mode = Database\Result::FETCH_ASSOC
  ) {
    $this->_databaseResult = $databaseResult;
    $this->_fetchMode = $mode;
  }

  public function getDatabaseResult(): Database\Result {
    return $this->_databaseResult;
  }

  public function getFetchMode(): int {
    return $this->_fetchMode;
  }

  /**
   * Setter for the mapping subobject. This is used to convert the property values into
   * a database record and back.
   *
   * If no mapping is provided, the mapping object will be removed.
   *
   * @param Database\Interfaces\Mapping $mapping
   */
  public function setMapping(Database\Interfaces\Mapping $mapping = NULL) {
    $this->_mapping = $mapping;
  }

  /**
   * Getter for the mapping subobject. This is used to convert the property values into
   * a database record and back.
   *
   * @return Database\Interfaces\Mapping
   */
  public function getMapping() {
    return $this->_mapping;
  }

  /**
   * Rewind the iterator, eg seek the internal pointer in the database result and fetch the first
   * record.
   */
  public function rewind(): void {
    $this->_offset = -1;
    $this->_databaseResult->seek(0);
    $this->next();
  }

  /**
   * Return the current record, use the mapping subobject if set.
   */
  public function current(): ?array {
    if (
      NULL !== $this->_current &&
      Database\Result::FETCH_ASSOC === $this->_fetchMode &&
      ($mapping = $this->getMapping())
    ) {
      return $mapping->mapFieldsToProperties($this->_current);
    }
    return $this->_current;
  }

  /**
   * Return the current record index
   */
  public function key(): int {
    return $this->_offset;
  }

  /**
   * Fetch the next record and store it in the object
   */
  public function next(): void {
    if ($this->_offset < 0 || \is_array($this->_current)) {
      $this->_current = $this->_databaseResult->fetchRow($this->_fetchMode);
      ++$this->_offset;
    }
  }

  /**
   * Check if a valid record was fetched
   */
  public function valid(): bool {
    return \is_array($this->_current);
  }
}
