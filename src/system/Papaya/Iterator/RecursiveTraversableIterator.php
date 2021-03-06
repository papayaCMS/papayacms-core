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

    /**
     * @var int
     */
    private $_flags;

    /**
     * @var int
     */
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
     *
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

    /**
     * @return int
     */
    public function getDepth() {
      return $this->getIteratorForTraversable(TRUE)->getDepth();
    }

    public function setMaxDepth($maxDepth) {
      $this->getIteratorForTraversable(TRUE)->setMaxDepth($maxDepth);
    }

    /**
     * @return int
     */
    public function getMaxDepth() {
      return $this->getIteratorForTraversable(TRUE)->getMaxDepth();
    }

    /**
     * @param int $level
     * @return \RecursiveIterator
     */
    public function getSubIterator($level) {
      return $this->getIteratorForTraversable(TRUE)->getSubIterator($level);
    }
  }
}
