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
 * This iterator allows to iterate over several given inner iterators. In other words
 * it combines the elements of multiple iterators.
 *
 * You can specify if it should use the keys or generate a new integer key.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Union implements \OuterIterator {
  const MIT_NEED_ANY = 0;

  const MIT_KEYS_NUMERIC = 0;

  const MIT_KEYS_ASSOC = 2;

  const MIT_FLAGS_DEFAULT = 0;

  /**
   * @var array
   */
  private $_iterators = [];

  /**
   * @var int
   */
  private $_position = 0;

  /**
   * @var int
   */
  private $_flags = self::MIT_FLAGS_DEFAULT;

  /**
   * Create iterator, store flags and attach all given iterators or arrays.
   *
   * All parameters are optional, and you can give as many iterators you like directly.
   *
   * @param int|array|\Traversable $flags
   * @param array $traversable
   */
  public function __construct($flags = NULL, ...$traversable) {
    if ($flags instanceof \Traversable || \is_array($flags)) {
      \array_unshift($traversable, $flags);
    } elseif (is_int($flags)) {
      $this->setFlags($flags);
    }
    $this->attachIterators(...$traversable);
  }

  /**
   * Return internal flags
   *
   * @return int
   */
  public function getFlags() {
    return $this->_flags;
  }

  /**
   * Set internal flags
   *
   * @param $flags
   */
  public function setFlags($flags) {
    Utility\Constraints::assertInteger($flags);
    $this->_flags = $flags;
  }

  /**
   * Return how many iterators are attached
   *
   * @return int
   */
  public function countIterators() {
    return \count($this->_iterators);
  }

  /**
   * Attach one or more iterators. All parameters of this method will be attaches as iterators
   *
   * @param array $traversable
   */
  public function attachIterators(...$traversable) {
    foreach ($traversable as $innerTraversable) {
      $this->attachIterator($innerTraversable);
    }
  }

  /**
   * Attach one iterator
   *
   * @param array|\Traversable $iterator
   */
  public function attachIterator($iterator) {
    $this->_iterators[$this->getIteratorIdentifier($iterator)] = ($iterator instanceof \Iterator)
      ? $iterator : new TraversableIterator($iterator);
  }

  /**
   * Validate if an interator is attached.
   *
   * @param \Traversable|array $iterator
   *
   * @return bool
   */
  public function containsIterator($iterator) {
    return \array_key_exists($this->getIteratorIdentifier($iterator), $this->_iterators);
  }

  /**
   * Detach an iterator
   *
   * @param \Traversable|array $iterator
   */
  public function detachIterator($iterator) {
    $identifier = $this->getIteratorIdentifier($iterator);
    if (\array_key_exists($identifier, $this->_iterators)) {
      unset($this->_iterators[$identifier]);
    }
  }

  /**
   * Return the currently activ inner iterator
   */
  public function getInnerIterator() {
    return \current($this->_iterators);
  }

  /**
   * Rewind to the first element in the first iterator
   */
  public function rewind() {
    $this->_position = -1;
    $iterator = \reset($this->_iterators);
    if ($iterator instanceof \Iterator) {
      $iterator->rewind();
      if ($iterator->valid()) {
        $this->_position = 0;
      } else {
        $this->next();
      }
    }
  }

  /**
   * Validate if the current iterator element is valid
   */
  public function valid() {
    $iterator = $this->getInnerIterator();
    if ($iterator instanceof \Iterator) {
      return $iterator->valid();
    }
    return FALSE;
  }

  /**
   * Return the key of the current element. Depending on the flags this is the position or
   * the real key of the element in the inner iterator.
   *
   * @return mixed
   */
  public function key() {
    if (self::MIT_KEYS_ASSOC === ($this->getFlags() & self::MIT_KEYS_ASSOC)) {
      $iterator = $this->getInnerIterator();
      return ($iterator instanceof \Iterator) ? $iterator->key() : NULL;
    }
    return $this->_position;
  }

  /**
   * Return the current element from the current iterator.
   *
   * @return mixed
   */
  public function current() {
    $iterator = $this->getInnerIterator();
    return ($iterator instanceof \Iterator) ? $iterator->current() : NULL;
  }

  /**
   * Move the internal pointer to the next element in the current iterator. If here is no next
   * element in the current iterator move to the next iterator and rewind it until a valid element
   * ist found or here is no next iterator available.
   */
  public function next() {
    $iterator = $this->getInnerIterator();
    if ($iterator instanceof \Iterator) {
      $iterator->next();
    }
    while ($iterator instanceof \Iterator) {
      if ($iterator->valid()) {
        $this->_position++;
        return;
      }
      $iterator = \next($this->_iterators);
      if ($iterator instanceof \Iterator) {
        $iterator->rewind();
      }
    }
  }

  /**
   * Generate an identifier for the provided iterator data.
   *
   * If is is an array a md5 hash of the serialized array will be used.
   *
   * If it is an object, the spl object hash will be used.
   *
   * @param \Traversable|array $iterator
   *
   * @return string
   */
  private function getIteratorIdentifier($iterator) {
    Utility\Constraints::assertArrayOrTraversable($iterator);
    return \is_array($iterator) ? \md5(\serialize($iterator)) : \spl_object_hash($iterator);
  }
}
