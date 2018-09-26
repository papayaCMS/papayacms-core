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

use Papaya\Iterator;
use Papaya\Utility;
/**
 * A CachingIterator that fills itself using a callback on first rewind or a class to getCache.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Caching extends \CachingIterator {
  private $_callback;

  private $_cached = FALSE;

  /**
   * Create object and store innter iterator.
   *
   * @param \Traversable $iterator
   * @param null|\Callable Callback
   */
  public function __construct(\Traversable $iterator, $callback = NULL) {
    parent::__construct(
      $iterator instanceof \Iterator ? $iterator : new TraversableIterator($iterator),
      \CachingIterator::FULL_CACHE
    );
    $this->setCallback($callback);
  }

  /**
   * Validate and store callback function
   *
   * @param \Callable|null $callback
   *
   * @throws \InvalidArgumentException
   */
  public function setCallback(callable $callback = NULL) {
    $this->_callback = $callback;
  }

  /**
   * Get the current callback function
   *
   * @return null|\Callable
   */
  public function getCallback() {
    return $this->_callback;
  }

  /**
   * Execute callback function
   */
  public function getCache() {
    if ($callback = $this->_callback) {
      $callback();
    }
    parent::getCache();
    $this->_cached = TRUE;
  }

  /**
   * Rewind iterator to first element and initialize cache if that has not already happened once.
   */
  public function rewind() {
    if (!$this->_cached) {
      $this->getCache();
    }
    parent::rewind();
  }
}
