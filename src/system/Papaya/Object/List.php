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
* A list of objects with the same class/interface.
*
* The major reason for this class, is to make sure that only objects with a certain class/interface
* are assigned to the list and all objects in this list can be treated as this class/interface.
*
* The list has an numerical (integer), continuous index. If you remove an item, the index of all
* following items decreases.
*
* @package Papaya-Library
* @subpackage Objects
*/
class PapayaObjectList
  implements Iterator, ArrayAccess, Countable {

  /**
  * List items
  * @var array
  */
  private $_items = array();

  /**
  * Item class/interface
  * @var string
  */
  private $_itemClass = 'stdClass';

  /**
  * Create object an set class/interface restriction
  *
  * @param string $itemClass
  */
  public function __construct($itemClass = NULL) {
    if (isset($itemClass)) {
      $this->setItemClass($itemClass);
    }
  }

  /**
   * Set/Change the item class restriction, this will remove all items in teh internal list.
   *
   * @param string $itemClass
   * @throws InvalidArgumentException
   */
  public function setItemClass($itemClass) {
    if (class_exists($itemClass) ||
        interface_exists($itemClass)) {
      $this->_itemClass = $itemClass;
      $this->_items = array();
    } else {
      throw new \InvalidArgumentException(
        sprintf(
          'Please provide a valid class/interface: "%s" was not found.',
          $itemClass
        )
      );
    }
  }

  /**
  * Get the class/interface name
  *
  * @return string
  */
  public function getItemClass() {
    return $this->_itemClass;
  }

  /**
  * Add a new item to the list
  *
  * This method returns the list to provide the possibility
  * to add several items using a fluent interface.
  *
  * @param object $value
  * @return PapayaObjectList
  */
  public function add($value) {
    $this->offsetSet(NULL, $value);
    return $this;
  }

  /**
  * Removes all items from the list
  *
  * @return void
  */
  public function clear() {
    $this->_items = array();
  }

  /**
  * Remove a single item from the list.
  *
  * @see PapayaObjectList::offsetGet
  *
  * @param integer $index
  */
  public function remove($index) {
    $this->offsetUnset($index);
  }

  /**
  * Check if the list contains no items
  *
  * @return boolean
  */
  public function isEmpty() {
    return empty($this->_items);
  }

  /**
  * Countable interface: return count of items in list
  */
  public function count() {
    return count($this->_items);
  }

  /**
  * Iterator interface: return current item
  *
  * @return object|FALSE
  */
  public function current() {
    return current($this->_items);
  }

  /**
  * Iterator ínterface: return current key
  *
  * @return integer|NULL
  */
  public function key() {
    return key($this->_items);
  }

  /**
  * Iterator interface: move internal pointer to next item
  */
  public function next() {
    next($this->_items);
  }

  /**
  * Iterator interface: move internal pointer to first item
  */
  public function rewind() {
    reset($this->_items);
  }

  /**
  * Iterator interface: check for item at internal pointer position
  *
  * @return boolean
  */
  public function valid() {
    return $this->current() !== FALSE;
  }

  /**
   * ArrayAccess interface: check for item specified by index
   *
   * @param integer $index
   * @return bool
   */
  public function offsetExists($index) {
    return isset($this->_items[$index]);
  }

  /**
   * ArrayAccess interface: get item specified by index
   *
   * @param integer $index
   * @return mixed
   */
  public function offsetGet($index) {
    return $this->_items[$index];
  }

  /**
   * ArrayAccess interface: Set item specified by index
   *
   * Only items of the given class/interface are allowed. If the index is NULl the item is added to
   * the end of the list, if it is an existing key the item is replaced.
   *
   * @param integer $index
   * @param mixed $value
   * @throws InvalidArgumentException
   */
  public function offsetSet($index, $value) {
    $value = $this->prepareItem($value);
    if ($value instanceof $this->_itemClass) {
      if (is_null($index)) {
        $this->_items[] = $value;
      } elseif (isset($this->_items[$index])) {
        $this->_items[$index] = $value;
      } else {
        throw new \InvalidArgumentException(
          sprintf(
            'Item with index "%s" does not exist.',
            $index
          )
        );
      }
    } else {
      throw new \InvalidArgumentException(
        sprintf(
          'Only instances of "%s" are allowed in this list, "%s" given.',
          $this->_itemClass,
          is_object($value) ? get_class($value) : gettype($value)
        )
      );
    }
  }

  /**
  * ArrayAccess interface: Remove the item specified by index
  *
  * @param integer $index
  */
  public function offsetUnset($index) {
    if (isset($this->_items[$index])) {
      unset($this->_items[$index]);
      $this->_items = array_values($this->_items);
    }
  }

  /**
  * Prepare item before adding it to the list. Overriding this method allows type conversions.
  *
  * @param mixed $value
  * @return mixed
  */
  protected function prepareItem($value) {
    return $value;
  }
}
