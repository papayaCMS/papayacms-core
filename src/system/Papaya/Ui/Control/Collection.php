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
* A collection list of interface controls with the same superclass. Allows access with array syntax
* and iterations.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCollection
  extends \PapayaUiControl
  implements \IteratorAggregate, \Countable, \ArrayAccess {

  /**
  * Most collection have an owner object, the generic handling stores it in this property
  */
  private $_owner = NULL;

  /**
  * Internal items buffer
  * @var array(\PapayaUiControlCollectionItem)
  */
  protected $_items = array();

  /**
  * Superclass for items, only items of this class may be added.
  * @var string
  */
  protected $_itemClass = \PapayaUiControlCollectionItem::class;

  /**
  * Superclass or interface for the owner, only objects of this class/interface may be set as owner.
  * @var string
  */
  protected $_ownerClass = NULL;

  /**
  * If a tag name is provided, an additional element will be added in
  * {@see \PapayaUiControlCollection::appendTo()) that will wrapp the items.
  * @var string
  */
  protected $_tagName = '';

  /**
  * Append item output to parent element. If a tag name was provided, the items will be wrapped
  * in an additional element.
  *
  * @param \PapayaXmlElement $parent
  * @return \PapayaXmlElement|NULL parent the elements where appended to,
  *    NULL if no items are appended.
  */
  public function appendTo(\PapayaXmlElement $parent) {
    if (count($this->_items) > 0) {
      if (!empty($this->_tagName)) {
        $parent = $parent->appendElement($this->_tagName);
      }
      /** @var \PapayaUiControlCollectionItem $item */
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
   * @throws \LogicException
   * @return object
   */
  public function owner($owner = NULL) {
    if (isset($owner)) {
      \Papaya\Utility\Constraints::assertObject($owner);
      if (isset($this->_ownerClass)) {
        \Papaya\Utility\Constraints::assertInstanceOf($this->_ownerClass, $owner);
      }
      $this->_owner = $owner;
      if ($owner instanceof \Papaya\Application\Access) {
        $this->papaya($owner->papaya());
      }
      foreach ($this->_items as $item) {
        $this->prepareItem($item);
      }
    }
    if (is_null($this->_owner)) {
      throw new \LogicException(
        sprintf(
          'LogicException: Collection "%s" has no owner object.',
          get_class($this)
        )
      );
    }
    return $this->_owner;
  }

  /**
  * Allow the items to check if the collection has an owner.
  *
  * @return boolean
  */
  public function hasOwner() {
    return isset($this->_owner);
  }

  /**
   * Access an item of the internal list defined by $offset. If $offset is negative it will return
   * the item counting from the end of the list.
   *
   * @param integer $offset
   * @throws \OutOfBoundsException
   * @return \PapayaUiControl
   */
  public function get($offset) {
    $offset = $this->prepareOffset($offset);
    if (array_key_exists($offset, $this->_items)) {
      return $this->_items[$offset];
    }
    throw new \OutOfBoundsException(
      sprintf('OutOfBoundsException: Invalid offset "%d".', $offset)
    );
  }

  /**
  * Validate that the $offset defines a valid item. If $offset is negative it will return
  * the item counting from the end of the list.
  *
  * @param integer $offset
  * @return boolean
  */
  public function has($offset) {
    $offset = $this->prepareOffset($offset);
    return array_key_exists($offset, $this->_items);
  }

  /**
  * Add (append) a new control to the item list. The method return the collection itself to
  * provide a fluent api.
  *
  * @param \PapayaUiControlCollectionItem $item
  * @return \PapayaUiControlCollection
  */
  public function add(\PapayaUiControlCollectionItem $item) {
    $this->validateItemClass($item);
    $this->_items[] = $this->prepareItem($item);
    $item->index(count($this->_items) - 1);
    return $this;
  }

  /**
   * Replace the item defined by $offset. If $offset is negative it will replace
   * the item counting from the end of the list. The method return the collection itself to
   * provide a fluent api.
   *
   * @param integer $offset
   * @param \PapayaUiControlCollectionItem $item
   * @throws \OutOfBoundsException
   * @return \PapayaUiControlCollection
   */
  public function set($offset, \PapayaUiControlCollectionItem $item) {
    $offset = $this->prepareOffset($offset);
    if (array_key_exists($offset, $this->_items)) {
      $this->validateItemClass($item);
      $this->_items[$offset] = $this->prepareItem($item);
      $item->index($offset);
      return $this;
    } else {
      throw new \OutOfBoundsException(
        sprintf('OutOfBoundsException: Invalid offset "%d".', $offset)
      );
    }
  }

  /**
   * Insert an additional item before the item defined by $offset. If $offset is negative it will
   * insert the item counting from the end of the list. The method return the collection itself to
   * provide a fluent api.
   *
   * @param integer $offset
   * @param \PapayaUiControlCollectionItem $item
   * @throws \OutOfBoundsException
   * @return \PapayaUiControlCollection
   */
  public function insertBefore($offset, \PapayaUiControlCollectionItem $item) {
    $this->validateItemClass($item);
    $offset = $this->prepareOffset($offset);
    if (isset($this->_items[$offset])) {
      array_splice($this->_items, $offset, 0, array($this->prepareItem($item)));
      $this->updateItemIndex($offset);
      return $this;
    } else {
      throw new \OutOfBoundsException(
        sprintf('OutOfBoundsException: Invalid offset "%d".', $offset)
      );
    }
  }

  /**
   * Remove the item defined by $offset. If $offset is negative it will
   * remove the item counting from the end of the list. The method return the collection itself to
   * provide a fluent api.
   *
   * @param integer $offset
   * @throws \OutOfBoundsException
   * @return \PapayaUiControlCollection
   */
  public function remove($offset) {
    $offset = $this->prepareOffset($offset);
    if (isset($this->_items[$offset])) {
      unset($this->_items[$offset]);
      $this->_items = array_values($this->_items);
      $this->updateItemIndex($offset);
    } else {
      throw new \OutOfBoundsException(
        sprintf('OutOfBoundsException: Invalid offset "%d".', $offset)
      );
    }
    return $this;
  }

  /**
  * Clear all items in the given collection.  The method return the collection itself to
  * provide a fluent api.
  *
  * @return \PapayaUiControlCollection
  */
  public function clear() {
    $this->_items = array();
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
  public function getIterator() {
    return new \ArrayIterator($this->toArray());
  }

  /**
  * Countable Interface, returns the item count.
  *
  * @return integer
  */
  public function count() {
    return count($this->_items);
  }

  /**
  * ArrayAccess interface, return true if an item at $offset exists. Negative values are possible.
  *
  * @param integer $offset
  * @return boolean
  */
  public function offsetExists($offset) {
    return $this->has($offset);
  }

  /**
  * ArrayAccess interface, return tha item at $offset. Negative values are possible.
  *
  * @param integer $offset
  * @return \PapayaUiControlCollectionItem
  */
  public function offsetGet($offset) {
    return $this->get($offset);
  }

  /**
  * ArrayAccess interface, Replace the item defined by $offset. If $offset is NULL, the item will
  * be added to the end of the item list. Negative values are possible.
  *
  * @param integer $offset
  * @param \PapayaUiControlCollectionItem $item
  */
  public function offsetSet($offset, $item) {
    if (is_null($offset)) {
      $this->add($item);
    } else {
      $this->set($offset, $item);
    }
  }

  /**
   * ArrayAccess interface, Remove the item defined by $offset. Negative values are possible.
   *
   * @param integer $offset
   * @return \PapayaUiControlCollection
   */
  public function offsetUnset($offset) {
    return $this->remove($offset);
  }

  /**
  * Prepare the item before adding it to the internal item list. This allows to do things like
  * setting the application object or parameter group in subclasses. It sets the colelction so
  * the item knopws the list it is in.
  *
  * @param \PapayaUiControlCollectionItem $item
  * @return \PapayaUiControlCollectionItem
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
   * @param $item
   * @return bool
   */
  protected function validateItemClass(\PapayaUiControlCollectionItem $item) {
    if (is_a($item, $this->_itemClass)) {
      return TRUE;
    }
    throw new \InvalidArgumentException(
      sprintf(
        'InvalidArgumentException: Invalid item class "%s" expected "%s".',
        get_class($item),
        $this->_itemClass
      )
    );
  }

  /**
  * Make sure that $offset is an integer. If it is an negative value calcluate the real $offset.
  *
  * @param integer $offset
  * @return integer
  */
  protected function prepareOffset($offset) {
    \Papaya\Utility\Constraints::assertInteger($offset);
    if ($offset < 0) {
      return count($this->_items) + $offset;
    } else {
      return $offset;
    }
  }

  /**
  * Store the item index in the item to allow backwards access without loops.
  *
  * @param integer $offset
  */
  protected function updateItemIndex($offset = 0) {
    $count = count($this->_items);
    for ($i = $offset; $i < $count; ++$i) {
      /** @var \PapayaUiControlCollectionItem $item */
      $item = $this->_items[$i];
      $item->index($i);
    }
  }
}
