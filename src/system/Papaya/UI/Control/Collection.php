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
namespace Papaya\UI\Control;

use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * A collection list of interface controls with the same superclass. Allows access with array syntax
 * and iterations.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Collection
  extends UI\Control
  implements \IteratorAggregate, \Countable, \ArrayAccess {
  /**
   * Most collection have an owner object, the generic handling stores it in this property
   */
  private $_owner;

  /**
   * Internal items buffer
   *
   * @var \Papaya\UI\Control\Collection\Item[]
   */
  protected $_items = [];

  /**
   * Superclass for items, only items of this class may be added.
   *
   * @var string
   */
  protected $_itemClass = Collection\Item::class;

  /**
   * Superclass or interface for the owner, only objects of this class/interface may be set as owner.
   *
   * @var string
   */
  protected $_ownerClass;

  /**
   * If a tag name is provided, an additional element will be added in
   * {@see \Papaya\UI\Control\Collection::appendTo()) that will wrapp the items.
   *
   * @var string
   */
  protected $_tagName = '';

  /**
   * Append item output to parent element. If a tag name was provided, the items will be wrapped
   * in an additional element.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element|null parent the elements where appended to,
   *                          NULL if no items are appended.
   */
  public function appendTo(XML\Element $parent) {
    if (\count($this->_items) > 0) {
      if (!empty($this->_tagName)) {
        $parent = $parent->appendElement($this->_tagName);
      }
      /** @var Collection\Item $item */
      foreach ($this->_items as $item) {
        $item->appendTo($parent);
      }
      return $parent;
    }
    return NULL;
  }

  /**
   * Most lists are owned by another object and the items need access to theat owner. So implement
   * some generic handling for that.
   *
   * @param object $owner
   *
   * @throws \LogicException
   *
   * @return object
   */
  public function owner($owner = NULL) {
    if (NULL !== $owner) {
      Utility\Constraints::assertObject($owner);
      if (NULL !== $this->_ownerClass) {
        Utility\Constraints::assertInstanceOf($this->_ownerClass, $owner);
      }
      $this->_owner = $owner;
      if ($owner instanceof \Papaya\Application\Access) {
        $this->papaya($owner->papaya());
      }
      foreach ($this->_items as $item) {
        $this->prepareItem($item);
      }
    }
    if (NULL === $this->_owner) {
      throw new \LogicException(
        \sprintf(
          'LogicException: Collection "%s" has no owner object.',
          \get_class($this)
        )
      );
    }
    return $this->_owner;
  }

  /**
   * Allow the items to check if the collection has an owner.
   *
   * @return bool
   */
  public function hasOwner() {
    return NULL !== $this->_owner;
  }

  /**
   * Access an item of the internal list defined by $offset. If $offset is negative it will return
   * the item counting from the end of the list.
   *
   * @param int $offset
   *
   * @throws \OutOfBoundsException
   *
   * @return UI\Control
   */
  public function get($offset) {
    $offset = $this->prepareOffset($offset);
    if (\array_key_exists($offset, $this->_items)) {
      return $this->_items[$offset];
    }
    throw new \OutOfBoundsException(
      \sprintf('OutOfBoundsException: Invalid offset "%d".', $offset)
    );
  }

  /**
   * Validate that the $offset defines a valid item. If $offset is negative it will return
   * the item counting from the end of the list.
   *
   * @param int $offset
   *
   * @return bool
   */
  public function has($offset) {
    $offset = $this->prepareOffset($offset);
    return \array_key_exists($offset, $this->_items);
  }

  /**
   * Add (append) a new control to the item list. The method return the collection itself to
   * provide a fluent api.
   *
   * @param Collection\Item $item
   *
   * @return self
   */
  public function add(Collection\Item $item) {
    $this->validateItemClass($item);
    $this->_items[] = $this->prepareItem($item);
    $item->index(\count($this->_items) - 1);
    return $this;
  }

  /**
   * Replace the item defined by $offset. If $offset is negative it will replace
   * the item counting from the end of the list. The method return the collection itself to
   * provide a fluent api.
   *
   * @param int $offset
   * @param Collection\Item $item
   *
   * @throws \OutOfBoundsException
   *
   * @return self
   */
  public function set($offset, Collection\Item $item) {
    $offset = $this->prepareOffset($offset);
    if (\array_key_exists($offset, $this->_items)) {
      $this->validateItemClass($item);
      $this->_items[$offset] = $this->prepareItem($item);
      $item->index($offset);
      return $this;
    }
    throw new \OutOfBoundsException(
      \sprintf('OutOfBoundsException: Invalid offset "%d".', $offset)
    );
  }

  /**
   * Insert an additional item before the item defined by $offset. If $offset is negative it will
   * insert the item counting from the end of the list. The method return the collection itself to
   * provide a fluent api.
   *
   * @param int $offset
   * @param Collection\Item $item
   *
   * @throws \OutOfBoundsException
   *
   * @return self
   */
  public function insertBefore($offset, Collection\Item $item) {
    $this->validateItemClass($item);
    $offset = $this->prepareOffset($offset);
    if (isset($this->_items[$offset])) {
      \array_splice($this->_items, $offset, 0, [$this->prepareItem($item)]);
      $this->updateItemIndex($offset);
      return $this;
    }
    throw new \OutOfBoundsException(
      \sprintf('OutOfBoundsException: Invalid offset "%d".', $offset)
    );
  }

  /**
   * Remove the item defined by $offset. If $offset is negative it will
   * remove the item counting from the end of the list. The method return the collection itself to
   * provide a fluent api.
   *
   * @param int $offset
   *
   * @throws \OutOfBoundsException
   *
   * @return self
   */
  public function remove($offset) {
    $offset = $this->prepareOffset($offset);
    if (isset($this->_items[$offset])) {
      unset($this->_items[$offset]);
      $this->_items = \array_values($this->_items);
      $this->updateItemIndex($offset);
    } else {
      throw new \OutOfBoundsException(
        \sprintf('OutOfBoundsException: Invalid offset "%d".', $offset)
      );
    }
    return $this;
  }

  /**
   * Clear all items in the given collection.  The method return the collection itself to
   * provide a fluent api.
   *
   * @return self
   */
  public function clear() {
    $this->_items = [];
    return $this;
  }

  /**
   * Return an array containing all items.
   *
   * @return array
   */
  public function toArray() {
    return $this->_items;
  }

  /**
   * IteratorAggregate Interface, allow to iterator the items.
   *
   * @return \ArrayIterator
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->toArray());
  }

  /**
   * Countable Interface, returns the item count.
   *
   * @return int
   */
  public function count(): int {
    return \count($this->_items);
  }

  /**
   * ArrayAccess interface, return true if an item at $offset exists. Negative values are possible.
   *
   * @param int $offset
   *
   * @return bool
   */
  public function offsetExists($offset): bool {
    return $this->has($offset);
  }

  /**
   * ArrayAccess interface, return tha item at $offset. Negative values are possible.
   *
   * @param int $offset
   *
   * @return Collection\Item|UI\Control
   */
  public function offsetGet($offset) {
    return $this->get($offset);
  }

  /**
   * ArrayAccess interface, Replace the item defined by $offset. If $offset is NULL, the item will
   * be added to the end of the item list. Negative values are possible.
   *
   * @param int $offset
   * @param Collection\Item $item
   */
  public function offsetSet($offset, $item) {
    if (NULL === $offset) {
      $this->add($item);
    } else {
      $this->set($offset, $item);
    }
  }

  /**
   * ArrayAccess interface, Remove the item defined by $offset. Negative values are possible.
   *
   * @param int $offset
   *
   * @return self
   */
  public function offsetUnset($offset) {
    return $this->remove($offset);
  }

  public function indexOf($item) {
    $position = array_search($item, $this->_items, TRUE);
    return (NULL !== $position) ? $position : -1;
  }

  /**
   * Prepare the item before adding it to the internal item list. This allows to do things like
   * setting the application object or parameter group in subclasses. It sets the collection so
   * the item knows the list it is in.
   *
   * @param Collection\Item $item
   *
   * @return Collection\Item
   */
  protected function prepareItem($item) {
    $item->collection($this);
    $item->papaya($this->papaya());
    return $item;
  }

  /**
   * Validate the class of an item.
   *
   * @throws \InvalidArgumentException
   *
   * @param $item
   *
   * @return bool
   */
  protected function validateItemClass(Collection\Item $item) {
    if (\is_a($item, $this->_itemClass)) {
      return TRUE;
    }
    throw new \InvalidArgumentException(
      \sprintf(
        'InvalidArgumentException: Invalid item class "%s" expected "%s".',
        \get_class($item),
        $this->_itemClass
      )
    );
  }

  /**
   * Make sure that $offset is an integer. If it is an negative value calcluate the real $offset.
   *
   * @param int $offset
   *
   * @return int
   */
  protected function prepareOffset($offset) {
    Utility\Constraints::assertInteger($offset);
    if ($offset < 0) {
      return \count($this->_items) + $offset;
    }
    return $offset;
  }

  /**
   * Store the item index in the item to allow backwards access without loops.
   *
   * @param int $offset
   */
  protected function updateItemIndex($offset = 0) {
    $count = \count($this->_items);
    for ($i = $offset; $i < $count; ++$i) {
      /** @var Collection\Item $item */
      $item = $this->_items[$i];
      $item->index($i);
    }
  }
}
