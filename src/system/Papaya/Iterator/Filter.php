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

use Papaya\Filter as InputFilter;
use Papaya\Utility;

/**
 * An filter iterator to filter an given iterator using a papaya filter object.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class Filter extends \FilterIterator {
  const FILTER_VALUES = 1;

  const FILTER_KEYS = 2;

  const FILTER_BOTH = 3;

  /**
   * @var Filter
   */
  private $_filter;

  /**
   * @var int
   */
  private $_target;

  /**
   * Create object and store iterator, pattern, flags and offset.
   *
   * @param \Traversable $traversable
   * @param InputFilter $filter
   * @param int $target
   */
  public function __construct(
    \Traversable $traversable, InputFilter $filter, $target = self::FILTER_VALUES
  ) {
    parent::__construct(
      $traversable instanceof \Iterator ? $traversable : new TraversableIterator($traversable)
    );
    $this->_filter = $filter;
    $this->_target = (int)$target;
  }

  /**
   * Validate the current item and/or key using the filter object.
   *
   * @return bool
   */
  public function accept() {
    if (
      Utility\Bitwise::inBitmask(self::FILTER_VALUES, $this->_target) &&
      !$this->isMatch($this->getInnerIterator()->current())) {
      return FALSE;
    }
    if (
      Utility\Bitwise::inBitmask(self::FILTER_KEYS, $this->_target) &&
      !$this->isMatch($this->getInnerIterator()->key())) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Optionally filter the current value before returning it
   *
   * @return mixed
   */
  public function current() {
    if (Utility\Bitwise::inBitmask(self::FILTER_VALUES, $this->_target)) {
      return $this->_filter->filter(parent::current());
    }
    return parent::current();
  }

  /**
   * Optionally filter the key value before returning it
   *
   * @return mixed
   */
  public function key() {
    if (Utility\Bitwise::inBitmask(self::FILTER_KEYS, $this->_target)) {
      return $this->_filter->filter(parent::key());
    }
    return parent::current();
  }

  /**
   * Match pattern against a value (key or current). The value will be casted to string
   *
   * @param mixed $value
   *
   * @return bool
   */
  private function isMatch($value) {
    try {
      $this->_filter->validate($value);
      return TRUE;
    } catch (InputFilter\Exception $e) {
    }
    return FALSE;
  }
}
