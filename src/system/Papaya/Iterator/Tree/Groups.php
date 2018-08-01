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
* An iterator that group items using a callback function on them.
*
* @package Papaya-Library
* @subpackage Iterator
*/
class PapayaIteratorTreeGroups implements \RecursiveIterator {

  /**
   * @var Iterator
   */
  private $_iterator = NULL;

  /**
   * @var array
   */
  private $_tree = NULL;

  /**
   * @var array
   */
  private $_positions = array();

  /**
   * @var array
   */
  private $_children = array();

  /**
   * Store item traversable and callback function for group generation
   *
   * @param \Traversable|array $traversable
   * @param callback $callback
   */
  public function __construct($traversable, $callback) {
    $this->_iterator = new \Papaya\Iterator\Traversable($traversable);
    \PapayaUtilConstraints::assertCallable($callback);
    $this->_callback = $callback;
  }

  /**
   * Fill groups and children arrays it they are still empty
   *
   * @throws \LogicException
   */
  private function prepareGroupsLazy() {
    if (is_null($this->_tree)) {
      $this->_tree = array();
      $this->_positions = array();
      $this->_children = array();
      foreach ($this->_iterator as $key => $item) {
        $group = call_user_func($this->_callback, $item, $key);
        if (!isset($group)) {
          $this->_tree[$key] = $item;
          continue;
        } elseif (is_scalar($group)) {
          $groupKey = $group;
        } else {
          $groupKey = md5(serialize($group));
        }
        if (isset($this->_positions[$groupKey])) {
          $position = $this->_positions[$groupKey];
        } else {
          $position = count($this->_tree);
          $this->_tree[$position] = $group;
          $this->_positions[$groupKey] = $position;
        }
        $this->_children[$position][$key] = $item;
      }
    }
  }

  /**
   * Reset array pointer in tree
   *
   * @see \RecursiveIterator::rewind()
   */
  public function rewind() {
    $this->prepareGroupsLazy();
    reset($this->_tree);
  }

  /**
   * Move to next element
   *
   * @see \RecursiveIterator::next()
   */
  public function next() {
    $this->prepareGroupsLazy();
    next($this->_tree);
  }

  /**
   * @see \RecursiveIterator::current()
   * @return mixed
   */
  public function current() {
    $this->prepareGroupsLazy();
    return current($this->_tree);
  }

  /**
   * @see \RecursiveIterator::key()
   * @return integer
   */
  public function key() {
    $this->prepareGroupsLazy();
    return key($this->_tree);
  }

  /**
   * @see \RecursiveIterator::valid()
   * @return boolean
   */
  public function valid() {
    $this->prepareGroupsLazy();
    $key = $this->key();
    return ($key !== NULL && $key !== FALSE);
  }

  /**
   * @see \RecursiveIterator::hasChildren()
   * @return boolean
   */
  public function hasChildren() {
    return isset($this->_children[$this->key()]);
  }

  /**
   * @see \RecursiveIterator::getChildren()
   * @return \RecursiveIterator
   */
  public function getChildren() {
    $key = $this->key();
    return new \Papaya\Iterator\Tree\Items(
      isset($this->_children[$key]) ? $this->_children[$key] : array()
    );
  }
}
