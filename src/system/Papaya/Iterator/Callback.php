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
 * This iterator allows convert the values on request. The callback function will be called with
 * the current value and key.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Callback implements \OuterIterator {
  const MODIFY_VALUES = 1;

  const MODIFY_KEYS = 2;

  const MODIFY_BOTH = 3;

  /**
   * Inner Iterator
   *
   * @var \Iterator
   */
  private $_iterator;

  /**
   * Element value convert function
   *
   * @var callable
   */
  private $_callback;

  /**
   * @var int
   */
  private $_target;

  /**
   * Create object and store arguments.
   *
   * @param \Traversable|array $iterator
   * @param \Callable $callback
   * @param int $target
   */
  public function __construct(
    $iterator, $callback = NULL, $target = self::MODIFY_VALUES
  ) {
    Utility\Constraints::assertArrayOrTraversable($iterator);
    Utility\Constraints::assertCallable($callback);
    $this->_iterator = ($iterator instanceof \Iterator) ? $iterator : new TraversableIterator($iterator);
    $this->_callback = $callback;
    $this->_target = \in_array(
      $target,
      [self::MODIFY_VALUES, self::MODIFY_KEYS, self::MODIFY_BOTH],
      FALSE
    ) ? $target : self::MODIFY_VALUES;
  }

  /**
   * OuterIterator interface: return the stored inner iterator
   *
   * @return \Iterator
   */
  public function getInnerIterator() {
    return $this->_iterator;
  }

  /**
   * Rewind the inner iterator
   */
  public function rewind() {
    $this->getInnerIterator()->rewind();
  }

  /**
   * Move inner iterator to next item
   */
  public function next() {
    $this->getInnerIterator()->next();
  }

  /**
   * Convert the current value using the callback function and return the result.
   *
   * @return mixed
   */
  public function current() {
    if (Utility\Bitwise::inBitmask(self::MODIFY_VALUES, $this->_target)) {
      $callback = $this->_callback;
      return $callback(
        $this->getInnerIterator()->current(),
        $this->getInnerIterator()->key(),
        self::MODIFY_VALUES
      );
    }
    return $this->getInnerIterator()->current();
  }

  /**
   * Convert the current key from the inner iterator
   *
   * @return mixed
   */
  public function key() {
    if (Utility\Bitwise::inBitmask(self::MODIFY_KEYS, $this->_target)) {
      $callback = $this->_callback;
      return $callback(
        $this->getInnerIterator()->current(),
        $this->getInnerIterator()->key(),
        self::MODIFY_KEYS
      );
    }
    return $this->getInnerIterator()->key();
  }

  /**
   * Validate the current element of the inner iterator
   */
  public function valid() {
    return $this->getInnerIterator()->valid();
  }
}
