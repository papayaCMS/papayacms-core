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
 * This iterator allows to iterator over a parent-child tree using a list of elements
 * and an list of children for each element.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Children implements \RecursiveIterator {
  private $_elements = [];

  private $_tree = [];

  private $_list = [];

  /**
   * Create iterator, store elements, tree and child-ids
   *
   * @param array $elements
   * @param array $tree
   * @param int|string $id
   */
  public function __construct(array $elements, array $tree, $id = 0) {
    $this->_elements = $elements;
    $this->_tree = $tree;
    $this->_list = (FALSE !== $id && isset($this->_tree[$id]))
      ? $this->_tree[$id] : [];
  }

  /**
   * reset the current element id list
   */
  public function rewind() {
    \reset($this->_list);
  }

  /**
   * move to the next element until the end of the list. Stop if an valid element is found.
   */
  public function next() {
    while (FALSE !== ($key = \next($this->_list))) {
      if (\array_key_exists($this->key(), $this->_elements)) {
        return;
      }
    }
  }

  /**
   * return the current element
   *
   * @return mixed
   */
  public function current() {
    return $this->_elements[$this->key()];
  }

  /**
   * return the current element key
   *
   * @return int|float|string
   */
  public function key() {
    return \current($this->_list);
  }

  /**
   * return if here is a valid element to return
   *
   * @return bool
   */
  public function valid() {
    $key = $this->key();
    return isset($key) &&
      FALSE != $key &&
      \array_key_exists($this->key(), $this->_elements);
  }

  /**
   * validate if the current element has children
   *
   * @return bool
   */
  public function hasChildren() {
    return isset($this->_tree[$this->key()]);
  }

  /**
   * Get an iterator for the children of the current element
   *
   * @return self
   */
  public function getChildren() {
    return new self($this->_elements, $this->_tree, $this->key());
  }
}
