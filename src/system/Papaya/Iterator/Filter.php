<?php
/**
 * An filter iterator to filter an given iterator using a papaya filter object.
 *
 * @copyright 2012 by papaya Software GmbH - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 * You can redistribute and/or modify this script under the terms of the GNU General Public
 * License (GPL) version 2, provided that the copyright and license notes, including these
 * lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 * @version $Id: Regex.php 39408 2014-02-27 16:00:49Z weinert $
 */

/**
 * An filter iterator to filter an given iterator using a papaya filter object.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class PapayaIteratorFilter extends FilterIterator {

  const FILTER_VALUES = 1;
  const FILTER_KEYS = 2;
  const FILTER_BOTH = 3;

  private $_filter = '';
  private $_target = self::FILTER_VALUES;

  /**
   * Create object and store iterator, pattern, flags and offset.
   *
   * @param Iterator $iterator
   * @param string $pattern
   * @param integer $offset
   * @param integer $target
   */
  public function __construct(
    Iterator $iterator, PapayaFilter $filter, $target = self::FILTER_VALUES
  ) {
    parent::__construct($iterator);
    $this->_filter = $filter;
    $this->_target = $target;
  }

  /**
   * Validate the current item and/or key using the filter object.
   *
   * @return boolean
   */
  public function accept() {
    if (PapayaUtilBitwise::inBitmask(self::FILTER_VALUES, $this->_target) &&
      !$this->isMatch($this->getInnerIterator()->current())) {
      return FALSE;
    }
    if (PapayaUtilBitwise::inBitmask(self::FILTER_KEYS, $this->_target) &&
      !$this->isMatch($this->getInnerIterator()->key())) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Optionally filter the current value before returning it
   *
   * @return mixed
   */
  public function current() {
    if (PapayaUtilBitwise::inBitmask(self::FILTER_VALUES, $this->_target)) {
      return $this->_filter->filter(parent::current());
    }
    return parent::current();
  }

  /**
   * Optionally filter the key value before returning it
   *
   * @return mixed
   */
  public function key() {
    if (PapayaUtilBitwise::inBitmask(self::FILTER_KEYS, $this->_target)) {
      return $this->_filter->filter(parent::key());
    }
    return parent::current();
  }

  /**
   * Match pattern against a value (key or current). The value will be casted to string
   *
   * @param mixed $value
   * @return boolean
   */
  private function isMatch($value) {
    try {
      $this->_filter->validate($value);
      return TRUE;
    } catch (PapayaFilterException $e) {
    }
    return FALSE;
  }
}