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

namespace Papaya\Iterator\Repeat;
/**
 * This iterator uses a callback to fetch the next entry as long as the callback returns new
 * elements.
 *
 * The callback gets the current value and current key, it return value should be an array containing
 * the value and key or FALSE.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Callback implements \Iterator {

  private $_callback;
  private $_startValue;
  private $_startKey;

  private $_currentValue;
  private $_currentKey = -1;
  private $_valid = FALSE;

  /**
   * Create object store callback, start value and key.
   *
   * @throws \InvalidArgumentException
   * @param callable $callback
   * @param mixed $startValue
   * @param mixed $startKey
   */
  public function __construct($callback, $startValue = NULL, $startKey = -1) {
    if (!is_callable($callback)) {
      throw new \InvalidArgumentException(
        'Invalid callback provided.'
      );
    }
    $this->_callback = $callback;
    $this->_startValue = $startValue;
    $this->_startKey = $startKey;
  }

  /**
   * Rewind iterator to start values, an fetch the first element
   */
  public function rewind() {
    $this->_currentValue = $this->_startValue;
    $this->_currentKey = $this->_startKey;
    $this->next();
  }

  /**
   * Use the callback to fetch the element
   */
  public function next() {
    $result = call_user_func(
      $this->_callback, $this->_currentValue, $this->_currentKey
    );
    if (is_array($result) && count($result) > 1) {
      $this->_currentValue = $result[0];
      $this->_currentKey = $result[1];
      $this->_valid = TRUE;
    } else {
      $this->_valid = FALSE;
    }
  }

  /**
   * return the current element value
   *
   * @return mixed
   */
  public function current() {
    return $this->_currentValue;
  }

  /**
   * return the current element key
   *
   * @return mixed
   */
  public function key() {
    return $this->_currentKey;
  }

  /**
   * return the if the last call toi next() fetched an element.
   *
   * @return boolean
   */
  public function valid() {
    return $this->_valid;
  }
}
