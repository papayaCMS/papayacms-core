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

namespace Papaya\Iterator;

/**
 * An IteratorAggregate implementation that uses a callback to create the iterator if needed.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Generator implements \IteratorAggregate {
  /**
   * @var callback
   */
  private $_callback;

  /**
   * @var array
   */
  private $_arguments = [];

  /**
   * @var \Iterator
   */
  private $_iterator;

  /**
   * Store callback and arguments for later use.
   *
   * @param callback $callback
   * @param array $arguments
   */
  public function __construct($callback, array $arguments = []) {
    \Papaya\Utility\Constraints::assertCallable($callback);
    $this->_callback = $callback;
    $this->_arguments = $arguments;
  }

  /**
   * IteratorAggregate interface: Trigger callback if not already done and store the
   * created iterator. Return the Iterator.
   *
   * @return \Iterator
   */
  public function getIterator() {
    if (NULL == $this->_iterator) {
      $this->_iterator = $this->createIterator();
    }
    return $this->_iterator;
  }

  /**
   * Create and return the Iterator. If possible remove abstraction layers.
   *
   * If the callback returns an array, an ArrayIterator is created.
   * If the callback returns an Iterator, that iterator is returned.
   * If the callback returns an IteratorAggregate, the inner iterator is returned.
   * If the callback returns an Traversable, a \Papaya\Iterator\Traversable is returned.
   *
   * In all other cases an EmptyIterator is returned.
   *
   * @return \Iterator
   */
  private function createIterator() {
    $traversable = \call_user_func_array($this->_callback, $this->_arguments);
    if (\is_array($traversable)) {
      return new \ArrayIterator($traversable);
    } elseif ($traversable instanceof \Iterator) {
      return $traversable;
    } elseif ($traversable instanceof \IteratorAggregate) {
      return $traversable->getIterator();
    } else {
      return ($traversable instanceof \Traversable)
        ? new \Papaya\Iterator\TraversableIterator($traversable)
        : new \EmptyIterator();
    }
  }
}
