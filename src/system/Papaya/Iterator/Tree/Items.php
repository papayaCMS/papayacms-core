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

namespace Papaya\Iterator\Tree;

/**
 * An iterator that converts any traversable or array into an RecursiveIterator you can attach
 * other Traverables or arrays as children to each element.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Items implements \OuterIterator, \RecursiveIterator {

  const ATTACH_TO_KEYS = 0;
  const ATTACH_TO_VALUES = 1;

  private $_mode = self::ATTACH_TO_KEYS;

  /**
   * @var \Traversable|array
   */
  private $_traversable = NULL;
  /**
   * @var \Iterator
   */
  private $_iterator = NULL;
  /**
   * @var array
   */
  private $_itemIterators = array();

  /**
   * Create object and store the traversable (or array)
   *
   * @param \Traversable|array $traversable
   * @param integer $mode
   */
  public function __construct($traversable, $mode = self::ATTACH_TO_KEYS) {
    \Papaya\Utility\Constraints::assertArrayOrTraversable($traversable);
    $this->_traversable = $traversable;
    $this->_mode = $mode;
  }

  /**
   * Returns the (created if necessary) iterator instance for the provided traversable.
   * It it already was an iterator it is returned. It it is an Traversable or array the first
   * call will created an {@see \Papaya\Iterator\Traversable} for it.
   *
   * return Iterator
   */
  public function getInnerIterator() {
    if (NULL === $this->_iterator) {
      $this->_iterator = ($this->_traversable instanceof \Iterator)
        ? $this->_traversable : new \Papaya\Iterator\TraversableIterator($this->_traversable);
    }
    return $this->_iterator;
  }

  /**
   * Attach an Traversalbe as children to an element.
   *
   * @param int|float|string|boolean $target
   * @param \Traversable|array $traversable
   */
  public function attachItemIterator($target, $traversable) {
    \Papaya\Utility\Constraints::assertArrayOrTraversable($traversable);
    $this->_itemIterators[(string)$target] = $traversable;
  }

  /**
   * Remove the children from an element
   *
   * @param int|float|string|boolean $target
   */
  public function detachItemIterator($target) {
    $target = (string)$target;
    if (isset($this->_itemIterators[$target])) {
      unset($this->_itemIterators[$target]);
    }
  }

  /**
   * Return if here is an Traversable attached for the current target
   *
   * @see \RecursiveIterator::hasChildren()
   * @return boolean
   */
  public function hasChildren() {
    return isset($this->_itemIterators[$this->getCurrentTarget()]);
  }

  /**
   * Return an RecursiveIterator for the attached Traversable of the current target. If
   * The attached Traversable is an RecursiveIterator it will be returned. If it is not
   * The method creates a new Instance of this class for the Traversable.   *
   *
   * @see \RecursiveIterator::hasChildren()
   * @return boolean
   */
  public function getChildren() {
    $iterator = $this->_itemIterators[$this->getCurrentTarget()];
    return ($iterator instanceof \RecursiveIterator) ? $iterator : new self($iterator);
  }

  /**
   * Depending on the $mode the current target is the key or the value.
   *
   * @return mixed
   */
  private function getCurrentTarget() {
    if ($this->_mode == self::ATTACH_TO_VALUES) {
      $result = $this->getInnerIterator()->current();
    } else {
      $result = $this->getInnerIterator()->key();
    }
    return (string)$result;
  }

  /**
   * Rewind iterator to first element
   */
  public function rewind() {
    $this->getInnerIterator()->rewind();
  }

  /**
   * Fetch next element
   */
  public function next() {
    $this->getInnerIterator()->next();
  }

  /**
   * Return current key
   *
   * @return mixed
   */
  public function key() {
    return $this->getInnerIterator()->key();
  }

  /**
   * Return current element
   *
   * @return mixed
   */
  public function current() {
    return $this->getInnerIterator()->current();
  }

  /**
   * Valid if the current element is valid.
   *
   * @return boolean
   */
  public function valid() {
    return $this->getInnerIterator()->valid();
  }
}
