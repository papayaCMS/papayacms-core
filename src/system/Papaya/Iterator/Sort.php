<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Iterator {

  use Papaya\Utility\Bitwise;
  use Papaya\Utility\Constraints;

  class Sort implements \OuterIterator {

    /**
     * Default behaviour - sort values
     */
    const SORT_VALUES = 0;
    /**
     * Sort by key
     */
    const SORT_KEYS = 1;
    /**
     * treat array as list, replace keys with numerical index, only for sorting values
     */
    const IGNORE_KEYS = 2;

    /**
     * @var \ArrayIterator
     */
    private $_sortedIterator;

    /**
     * @var \Iterator|TraversableIterator
     */
    private $_innerIterator;

    /**
     * @var NULL|\Closure
     */
    private $_compare;
    /**
     * @var int
     */
    private $_flags;

    public function __construct(
      $iterator, \Closure $compare = NULL, $flags = self::SORT_VALUES
    ) {
      Constraints::assertArrayOrTraversable($iterator);
      $this->_innerIterator = ($iterator instanceof \Iterator) ? $iterator : new TraversableIterator($iterator);
      $this->_compare = $compare;
      $this->_flags = (int)$flags;
    }

    public function getInnerIterator() {
      return $this->_innerIterator;
    }

    private function getSortedIterator() {
      if ($this->_sortedIterator === NULL) {
        if (Bitwise::inBitmask(self::SORT_KEYS, $this->_flags)) {
          $values = iterator_to_array($this->_innerIterator);
          uksort(
            $values,
            $this->_compare instanceof \Closure ? $this->_compare : 'strcmp'
          );
        } else {
          $values = iterator_to_array(
            $this->_innerIterator,
            !Bitwise::inBitmask(self::IGNORE_KEYS, $this->_flags)
          );
          uasort(
            $values,
            $this->_compare instanceof \Closure ? $this->_compare : 'strcmp'
          );
        }
        $this->_sortedIterator = new \ArrayIterator($values);
      }
      return $this->_sortedIterator;
    }

    public function rewind() {
      $this->getSortedIterator()->rewind();
    }

    public function current() {
      return $this->getSortedIterator()->current();
    }

    public function next() {
      $this->getSortedIterator()->next();
    }

    public function key() {
      return $this->getSortedIterator()->key();
    }

    public function valid() {
      return $this->getSortedIterator()->valid();
    }
  }
}


