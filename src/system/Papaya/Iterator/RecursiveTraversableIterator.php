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

namespace Papaya\Iterator {

  /**
   * A lazy initialized \RecursiveIteratorIterator
   *
   * @method \RecursiveIteratorIterator getIteratorForTraversable($useCached = FALSE);
   */
  class RecursiveTraversableIterator extends TraversableIterator {
    const LEAVES_ONLY = \RecursiveIteratorIterator::LEAVES_ONLY;

    const SELF_FIRST = \RecursiveIteratorIterator::SELF_FIRST;

    const CHILD_FIRST = \RecursiveIteratorIterator::CHILD_FIRST;

    const CATCH_GET_CHILD = \RecursiveIteratorIterator::CATCH_GET_CHILD;

    private $_flags;

    private $_mode;

    /**
     * @param array|\RecursiveIterator|\IteratorAggregate $traversable
     * @param int $mode
     * @param int $flags
     */
    public function __construct($traversable, $mode = self::LEAVES_ONLY, $flags = 0) {
      parent::__construct($traversable);
      $this->_mode = $mode;
      $this->_flags = $flags;
    }

    /**
     * @param $traversable
     * @return \RecursiveIteratorIterator
     */
    protected function createIteratorForTraversable($traversable) {
      $iterator = NULL;
      if ($traversable instanceof \RecursiveIterator) {
        $iterator = $traversable;
      }
      if ($traversable instanceof \IteratorAggregate) {
        $iterator = $traversable->getIterator();
      }
      if (\is_array($traversable)) {
        $iterator = new \RecursiveArrayIterator($traversable);
      }
      if (!$iterator instanceof \RecursiveIterator) {
        throw new \UnexpectedValueException(
          \sprintf(
            'Could not get RecursiveIterator for/from provided %s.',
            \is_object($traversable) ? \get_class($traversable) : \gettype($traversable)
          )
        );
      }
      return new \RecursiveIteratorIterator($iterator, $this->_mode, $this->_flags);
    }

    public function getDepth() {
      return $this->getIteratorForTraversable(TRUE)->getDepth();
    }

    public function getSubIterator($level) {
      return $this->getIteratorForTraversable(TRUE)->getSubIterator($level);
    }

    public function beginIteration() {
      $this->getIteratorForTraversable(TRUE)->beginIteration();
    }

    public function endIteration() {
      $this->getIteratorForTraversable(TRUE)->endIteration();
    }

    public function callHasChildren() {
      return $this->getIteratorForTraversable(TRUE)->callHasChildren();
    }

    public function callGetChildren() {
      return $this->getIteratorForTraversable(TRUE)->callGetChildren();
    }

    public function beginChildren() {
      $this->getIteratorForTraversable(TRUE)->beginChildren();
    }

    public function endChildren() {
      $this->getIteratorForTraversable(TRUE)->endChildren();
    }

    public function nextElement() {
      $this->getIteratorForTraversable(TRUE)->nextElement();
    }

    public function setMaxDepth($maxDepth) {
      $this->getIteratorForTraversable(TRUE)->setMaxDepth($maxDepth);
    }

    public function getMaxDepth() {
      return $this->getIteratorForTraversable(TRUE)->getDepth();
    }
  }
}
