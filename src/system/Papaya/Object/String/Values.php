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
* A list of strings that is castable to a string using a default offset
*
* If you cast is to a string the default element will be casted to string and returned.
*
* But you can treat it like an array, too.
*
* @package Papaya-Library
* @subpackage Objects
*/
class PapayaObjectStringValues implements ArrayAccess, Countable, IteratorAggregate {

  /**
   * @var ArrayObject
   */
  private $_values = NULL;

  /**
   * @var int|string
   */
  private $_defaultOffset = 0;

  /**
   * Create object store values and default offset
   *
   * @param mixed $values
   * @param int|string $defaultOffset
   */
  public function __construct($values = array(), $defaultOffset = 0) {
    $this->_defaultOffset = $defaultOffset;
    $this->assign($values);
  }

  /**
   * Assign values to the internal list, if it is a single value it will be added as only value
   * to the list using the default offset.
   *
   * @param mixed $values
   */
  public function assign($values) {
    $this->_values = new \ArrayObject;
    if (is_array($values) || $values instanceof \Traversable) {
      foreach ($values as $key => $value) {
        $this->_values[$key] = (string)$value;
      }
    } else {
      $this->_values[$this->_defaultOffset] = (string)$values;
    }
  }

  /**
   * Get the value with the provided offset, if the value does not exists the provided default
   * value ist returned.
   *
   * @param string|int $offset
   * @param string $defaultValue
   * @return string
   */
  public function get($offset, $defaultValue = NULL) {
    return $this->offsetExists($offset) ? $this->offsetGet($offset) : $defaultValue;
  }

  /**
   * Fetches the element specified by the default offset and returns it as string
   *
   * @return string
   */
  public function __toString() {
    return (string)$this->get($this->_defaultOffset, '');
  }

  /**
   * @see \ArrayAccess::offsetExists()
   */
  public function offsetExists($offset) {
    return $this->_values->offsetExists($offset);
  }

  /**
   * @see \ArrayAccess::offsetGet()
   */
  public function offsetGet($offset) {
    return $this->_values->offsetGet($offset);
  }

  /**
   * @see \ArrayAccess::offsetSet()
   */
  public function offsetSet($offset, $value) {
    $this->_values->offsetSet($offset, $value);
  }

  /**
   * @see \ArrayAccess::offsetUnset()
   */
  public function offsetUnset($offset) {
    $this->_values->offsetUnset($offset);
  }

  /**
   * @see \Countable::count()
   */
  public function count() {
    return count($this->_values);
  }

  /**
   * @see \IteratorAggregate::getIterator()
   */
  public function getIterator() {
    return $this->_values->getIterator();
  }
}
