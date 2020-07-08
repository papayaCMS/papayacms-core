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
namespace Papaya\Iterator\Filter;

use Papaya\Iterator\TraversableIterator;
use Papaya\Utility;

/**
 * An filter iterator to filter a given iterator using a callback function.
 *
 * Unlike PHP 5.4 {@see FilterIteratorCallback} this class allows to iterate a traversable, too.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Callback extends \FilterIterator {
  /**
   * @var callable
   */
  private $_callback;

  /**
   * Create filter iterator and store values, if the provided Iterator is only a
   * Traversable, wrap it using IteratorIterator.
   *
   *
   * @param \Traversable|array $traversable
   * @param callable $callback
   */
  public function __construct($traversable, $callback) {
    parent::__construct(
      $traversable instanceof \Iterator ? $traversable : new TraversableIterator($traversable)
    );
    $this->setCallback($callback);
  }

  /**
   * Validate and store the callback
   *
   * @param callable $callback
   */
  public function setCallback(callable $callback) {
    Utility\Constraints::assertCallable($callback);
    $this->_callback = $callback;
  }

  /**
   * return stored the callback
   *
   * @return callable
   */
  public function getCallback() {
    return $this->_callback;
  }

  /**
   * Use the callback to validate an element
   *
   * @return bool
   */
  public function accept() {
    $callback = $this->_callback;
    return $callback(
      $this->getInnerIterator()->current(),
      $this->getInnerIterator()->key()
    );
  }
}
