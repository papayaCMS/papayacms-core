<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
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

use Papaya\Iterator\RecursiveTraversableIterator;

/**
 * An iterator that limit the recursion to a specified maximum depth
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class DepthLimit implements \OuterIterator, \RecursiveIterator {

  private $_maximumDepth;

  /**
   * @var \Iterator
   */
  private $_iterator;

  /**
   * @param \RecursiveIterator $iterator
   * @param int $maximumDepth
   */
  public function __construct(\RecursiveIterator $iterator, $maximumDepth) {
    $this->_iterator = $iterator;
    $this->_maximumDepth = (int)$maximumDepth;
  }

  /**
   * return Iterator
   */
  public function getInnerIterator() {
    return $this->_iterator;
  }

  /**
   * Return TRUE if here are children on the inner iterator and the maximum level is larger 1.
   *
   * @see \RecursiveIterator::hasChildren()
   *
   * @return bool
   */
  public function hasChildren() {
    return $this->_maximumDepth > 1 && $this->getInnerIterator()->hasChildren();
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
    if ($this->hasChildren()) {
      return new self($this->getInnerIterator()->getChildren(), $this->_maximumDepth - 1);
    }
    return new \RecursiveArrayIterator([]);
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
