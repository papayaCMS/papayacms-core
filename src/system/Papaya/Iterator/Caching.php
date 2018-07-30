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
 * A CachingIterator that fills itself using a callback on first rewind or a class to getCache.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Caching extends \CachingIterator {

  private $_callback = NULL;
  private $_cached = FALSE;

  /**
   * Create object and store innter iterator.
   *
   * @param \Traversable $iterator
   * @param NULL|\Callable Callback
   */
  public function __construct(\Traversable $iterator, $callback = NULL) {
    parent::__construct(
      $iterator instanceof \Iterator ? $iterator : new \PapayaIteratorTraversable($iterator),
      \CachingIterator::FULL_CACHE
    );
    $this->setCallback($callback);
  }

  /**
   * Validate and store callback function
   *
   * @param \Callable|NULL $callback
   * @throws \InvalidArgumentException
   */
  public function setCallback($callback) {
    if (is_null($callback) || is_callable($callback)) {
      $this->_callback = $callback;
    } else {
      throw new \InvalidArgumentException(
        'Provided callback parameter is not valid.'
      );
    }
  }

  /**
   * Get the current callback function
   *
   * @return NULL|\Callable
   */
  public function getCallback() {
    return $this->_callback;
  }

  /**
   * Execute callback function
   */
  public function getCache() {
    if (isset($this->_callback)) {
      call_user_func($this->_callback);
    }
    parent::getCache();
    $this->_cached = TRUE;
  }

  /**
   * Rewind iterator to first element and initailize cache if that has not already happend once.
   */
  public function rewind() {
    if (!$this->_cached) {
      $this->getCache();
    }
    parent::rewind();
  }
}
