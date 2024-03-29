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

use Papaya\Utility;

/**
 * An iterator that converts any traversable into an iterator. Not unlike IteratorIterator but
 * with a lazy initialization.
 *
 * The iterator for the traversable is fetched/create on data access not directly
 * in the constructor.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class TraversableIterator implements \OuterIterator {
  /**
   * @var array|\Traversable
   */
  private $_traversable;

  /**
   * @var \Iterator
   */
  private $_iterator;

  /**
   * Store the traversable to a member variable.
   *
   * @param \Traversable|array $traversable
   */
  public function __construct($traversable) {
    Utility\Constraints::assertArrayOrTraversable($traversable);
    $this->_traversable = $traversable;
  }

  /**
   * Fetches/Creates an iterator for the stored traversable. This memtod will be called
   * by the methods of the iterator interface. The methods use the $useCached argument
   * to reuse an already fetched/created iterator.
   *
   * @param bool $useCached
   *
   * @return \Iterator
   */
  public function getIteratorForTraversable($useCached = FALSE) {
    if (!$useCached || NULL === $this->_iterator) {
      $this->_iterator = $this->createIteratorForTraversable($this->_traversable);
    }
    return $this->_iterator;
  }

  protected function createIteratorForTraversable($traversable) {
    if ($traversable instanceof \Iterator) {
      return $traversable;
    }
    if ($traversable instanceof \IteratorAggregate) {
      return $traversable->getIterator();
    }
    if (\is_array($traversable)) {
      return new \ArrayIterator($traversable);
    }
    return new \IteratorIterator($traversable);
  }

  /**
   * Return the original stored iterator, if here is one
   *
   */
  public function getInnerIterator(): ?\Iterator {
    return $this->_traversable instanceof \Traversable ? $this->_traversable : null;
  }

  /**
   * Return the original stored traversable or array, like in IteratorIterator
   */
  #[\ReturnTypeWillChange]
  public function getInnerSource() {
    return $this->_traversable;
  }

  /**
   * Return current element
   *
   * @return mixed
   */
  #[\ReturnTypeWillChange]
  public function current() {
    return $this->getIteratorForTraversable(TRUE)->current();
  }

  /**
   * Return current key
   *
   * @return mixed
   */
  #[\ReturnTypeWillChange]
  public function key() {
    return $this->getIteratorForTraversable(TRUE)->key();
  }

  /**
   * Fetch next element
   */
  public function next(): void {
    $this->getIteratorForTraversable(TRUE)->next();
  }

  /**
   * Rewind iterator to first element
   */
  public function rewind(): void {
    $this->getIteratorForTraversable(TRUE)->rewind();
  }

  /**
   * Valid if the current element is valid.
   *
   * @return bool
   */
  public function valid(): bool {
    return $this->getIteratorForTraversable(TRUE)->valid();
  }
}
