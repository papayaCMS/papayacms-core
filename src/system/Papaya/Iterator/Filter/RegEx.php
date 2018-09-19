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

/**
 * An filter iterator to filter an given iterator using a pcre pattern.
 *
 * The elements of the inner iterator are casted to string, so they can be objects implemening
 * the __toString method.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class RegEx extends \FilterIterator {
  const FILTER_VALUES = 1;

  const FILTER_KEYS = 2;

  const FILTER_BOTH = 3;

  private $_pattern = '';

  private $_offset = 0;

  private $_target = self::FILTER_VALUES;

  /**
   * Create object and store iterator, pattern, flags and offset.
   *
   * @param \Iterator $iterator
   * @param string $pattern
   * @param int $offset
   * @param int $target
   */
  public function __construct(
    \Iterator $iterator, $pattern, $offset = 0, $target = self::FILTER_VALUES
  ) {
    \Papaya\Utility\Constraints::assertString($pattern);
    \Papaya\Utility\Constraints::assertInteger($offset);
    \Papaya\Utility\Constraints::assertInteger($target);
    parent::__construct($iterator);
    $this->_pattern = $pattern;
    $this->_offset = $offset;
    $this->_target = $target;
  }

  /**
   * Validate the current item and/or key using the regex pattern.
   *
   * @return bool
   */
  public function accept() {
    if (\Papaya\Utility\Bitwise::inBitmask(self::FILTER_VALUES, $this->_target) &&
      !$this->isMatch($this->getInnerIterator()->current())) {
      return FALSE;
    }
    if (\Papaya\Utility\Bitwise::inBitmask(self::FILTER_KEYS, $this->_target) &&
      !$this->isMatch($this->getInnerIterator()->key())) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Match pattern agains a value (key or current). The value will be casted to string
   *
   * @param mixed $value
   *
   * @return int
   */
  private function isMatch($value) {
    $matches = [];
    return \preg_match($this->_pattern, (string)$value, $matches, 0, $this->_offset);
  }
}
