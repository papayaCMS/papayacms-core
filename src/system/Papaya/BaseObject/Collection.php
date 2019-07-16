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
namespace Papaya\BaseObject;

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
class Collection
  implements \Iterator, \ArrayAccess, \Countable {

  const MODE_NUMERIC = 0;
  const MODE_ASSOCIATIVE = 1;
  /**
   * List items
   *
   * @var array
   */
  private $_items = [];

  /**
   * Item class/interface
   *
   * @var string
   */
  private $_itemClass = \stdClass::class;

  /**
   * @var int
   */
  private $_mode;

  /**
   * Create object an set class/interface restriction
   *
   * @param string $itemClass
   * @param int $mode
   */
  public function __construct($itemClass = NULL, $mode = self::MODE_NUMERIC) {
    if (NULL !== $itemClass) {
      $this->setItemClass($itemClass);
    }
    $this->_mode = $mode;
  }

  /**
   * Clone items as well, not just the collection instance
   */
  public function __clone() {
    foreach ($this->_items as $index => $item) {
      $this->_items[$index] = clone $item;
    }
  }

  /**
   * Set/Change the item class restriction, this will remove all items in teh internal list.
   *
   * @param string $itemClass
   * @throws \InvalidArgumentException
   */
  public function setItemClass($itemClass) {
    if (\class_exists($itemClass) || \interface_exists($itemClass)) {
      $this->_itemClass = $itemClass;
      $this->_items = [];
    } else {
      throw new \InvalidArgumentException(
        \sprintf(
          'Please provide a valid class/interface: "%s" was not found.',
          $itemClass
        )
      );
    }
  }

  /**
   * Get the class/interface name
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
   * @return $this
   */
  public function add($value) {
    $this->offsetSet(NULL, $value);
    return $this;
  }

  /**
   * Removes all items from the list
   */
  public function clear() {
    $this->_items = [];
  }

  /**
   * Remove a single item from the list.
   *
   * @param int $index
   */
  public function remove($index) {
    $this->offsetUnset($index);
  }

  /**
   * Check if the list contains no items
   *
   * @return bool
   */
  public function isEmpty() {
    return empty($this->_items);
  }

  /**
   * Countable interface: return count of items in list
   * @return int
   */
  public function count() {
    return \count($this->_items);
  }

  /**
   * Iterator interface: return current item
   *
   * @return object|false
   */
  public function current() {
    return \current($this->_items);
  }

  /**
   * Iterator interface: return current key
   *
   * @return int|string|null
   */
  public function key() {
    return \key($this->_items);
  }

  /**
   * Iterator interface: return current key
   *
   * @return string[]|int[]
   */
  public function keys() {
    return array_keys($this->_items);
  }

  /**
   * Iterator interface: move internal pointer to next item
   */
  public function next() {
    \next($this->_items);
  }

  /**
   * Iterator interface: move internal pointer to first item
   */
  public function rewind() {
    \reset($this->_items);
  }

  /**
   * @return mixed
   */
  public function first() {
    return \reset($this->_items);
  }

  /**
   * @return mixed
   */
  public function last() {
    return \end($this->_items);
  }

  /**
   * Iterator interface: check for item at internal pointer position
   *
   * @return bool
   */
  public function valid() {
    return FALSE !== $this->current();
  }

  /**
   * ArrayAccess interface: check for item specified by index
   *
   * @param int|string $index
   * @return bool
   */
  public function offsetExists($index) {
    $index = $this->prepareKey($index);
    return isset($this->_items[$index]);
  }

  /**
   * ArrayAccess interface: get item specified by index
   *
   * @param int|string $index
   * @return object|NULL
   */
  public function offsetGet($index) {
    $index = $this->prepareKey($index);
    return $this->_items[$index];
  }

  /**
   * ArrayAccess interface: Set item specified by index
   *
   * Only items of the given class/interface are allowed. If the index is NULl the item is added to
   * the end of the list, if it is an existing key the item is replaced.
   *
   * @param int|string $index
   * @param mixed $value
   *
   * @throws \InvalidArgumentException
   */
  public function offsetSet($index, $value) {
    $value = $this->prepareItem($value);
    $index = $this->prepareKey($index, $value);
    if ($value instanceof $this->_itemClass) {
      if (NULL === $index) {
        $this->_items[] = $value;
      } elseif ( $this->_mode === self::MODE_ASSOCIATIVE || isset($this->_items[$index])) {
        $this->_items[$index] = $value;
      } else {
        throw new \InvalidArgumentException(
          \sprintf(
            'Item with index "%s" does not exist.',
            $index
          )
        );
      }
    } else {
      throw new \InvalidArgumentException(
        \sprintf(
          'Only instances of "%s" are allowed in this list, "%s" given.',
          $this->_itemClass,
          \is_object($value) ? \get_class($value) : \gettype($value)
        )
      );
    }
  }

  /**
   * ArrayAccess interface: Remove the item specified by index
   *
   * @param int|string $index
   */
  public function offsetUnset($index) {
    $index = $this->prepareKey($index);
    if (isset($this->_items[$index])) {
      unset($this->_items[$index]);
      if ($this->_mode === self::MODE_NUMERIC) {
        $this->_items = \array_values($this->_items);
      }
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

  /**
   * Prepare key before adding it to the list. Overriding this method allows type conversions.
   *
   * @param string|int|NULL $index
   * @param mixed $value
   * @return string
   */
  protected function prepareKey($index, $value = NULL) {
    return $index;
  }
}
