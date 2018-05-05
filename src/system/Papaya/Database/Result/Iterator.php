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
* Papaya Database Result Iterator, allows to iterate on an object implementing PapayaDatabaseResult
*
* You can specifify it the records will be fetched with numerical index, field names or both.
*
* @package Papaya-Library
* @subpackage Database
*/
class PapayaDatabaseResultIterator implements \Iterator {

  private $_databaseResult = NULL;
  private $_mapping = NULL;
  private $_fetchMode = \PapayaDatabaseResult::FETCH_ASSOC;
  private $_current = NULL;
  private $_offset = -1;

  /**
  * Create object, store result object and fetch mode
  *
  * @param \PapayaDatabaseResult $databaseResult
  * @param integer $mode
  */
  public function __construct(
    \PapayaDatabaseResult $databaseResult = NULL, $mode = \PapayaDatabaseResult::FETCH_ASSOC
  ) {
    $this->_databaseResult = $databaseResult;
    $this->_fetchMode = $mode;
  }

  /**
  * Setter for the mapping subobject. This is used to convert the property values into
  * a database record and back.
  *
  * If no mapping is provided, the mapping object will be removed.
  *
  * @param \PapayaDatabaseInterfaceMapping $mapping
  */
  public function setMapping(\PapayaDatabaseInterfaceMapping $mapping = NULL) {
    $this->_mapping = $mapping;
  }

  /**
   * Getter for the mapping subobject. This is used to convert the property values into
   * a database record and back.
   *
   * @internal param \PapayaDatabaseInterfaceMapping $mapping
   * @return \PapayaDatabaseInterfaceMapping
   */
  public function getMapping() {
    return $this->_mapping;
  }

  /**
  * Rewind the iterator, eg seek the internal pointer in the database result and fetch the first
  * record.
  */
  public function rewind() {
    $this->_offset = -1;
    $this->_databaseResult->seek(0);
    $this->next();
  }

  /**
  * Return the current record, use the mapping subobject if set.
  */
  public function current() {
    if (isset($this->_current) && ($mapping = $this->getMapping())) {
      return $this->getMapping()->mapFieldsToProperties($this->_current);
    }
    return $this->_current;
  }

  /**
  * Return the current record index
  */
  public function key() {
    return $this->_offset;
  }

  /**
  * Fetch the next record and store it in the object
  */
  public function next() {
    if ($this->_offset < 0 || is_array($this->_current)) {
      $this->_current = $this->_databaseResult->fetchRow($this->_fetchMode);
      ++$this->_offset;
    }
  }

  /**
  * Check if a valid record was fetched
  */
  public function valid() {
    return is_array($this->_current);
  }
}
