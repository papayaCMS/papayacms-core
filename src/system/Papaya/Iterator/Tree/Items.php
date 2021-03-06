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

use Papaya\Iterator;
use Papaya\Utility;

/**
 * An iterator that converts any traversable or array into an RecursiveIterator you can attach
 * other Traversable or arrays as children to each element.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Items implements \OuterIterator, \RecursiveIterator {
  const ATTACH_TO_KEYS = 0;

  const ATTACH_TO_VALUES = 1;

  private $_mode;

  /**
   * @var \Traversable|array
   */
  private $_traversable;

  /**
   * @var \Iterator
   */
  private $_iterator;

  /**
   * @var array
   */
  private $_itemIterators = [];

  /**
   * Create object and store the traversable (or array)
   *
   * @param \Traversable|array $traversable
   * @param int $mode
   */
  public function __construct($traversable, $mode = self::ATTACH_TO_KEYS) {
    Utility\Constraints::assertArrayOrTraversable($traversable);
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
        ? $this->_traversable : new Iterator\TraversableIterator($this->_traversable);
    }
    return $this->_iterator;
  }

  /**
   * Attach an Traversable as children to an element.
   *
   * @param int|float|string|bool $target
   * @param \Traversable|array $traversable
   */
  public function attachItemIterator($target, $traversable) {
    Utility\Constraints::assertArrayOrTraversable($traversable);
    $this->_itemIterators[(string)$target] = $traversable;
  }

  /**
   * Remove the children from an element
   *
   * @param int|float|string|bool $target
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
   *
   * @return bool
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
   *
   * @return \RecursiveIterator
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
    if (self::ATTACH_TO_VALUES === $this->_mode) {
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
   * @return bool
   */
  public function valid() {
    return $this->getInnerIterator()->valid();
  }
}
