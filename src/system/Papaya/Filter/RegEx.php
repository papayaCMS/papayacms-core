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
namespace Papaya\Filter;

use Papaya\Filter;

/**
 * Papaya filter class for using a pcre pattern
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class RegEx implements Filter {
  /**
   * Minimum password length
   *
   * @var int
   */
  private $_pattern = 0;

  /**
   * Maximum password length
   *
   * @var int|string
   */
  private $_subMatch = 0;

  /**
   * Construct object and initialize pattern and submatch identifier (for filter result)
   *
   * The submatch identifier can be a string (named subpattern) or an integer (index)
   *
   * @param string $pattern
   * @param int|string $subMatch
   */
  public function __construct($pattern, $subMatch = 0) {
    $this->_pattern = $pattern;
    $this->_subMatch = $subMatch;
  }

  /**
   * Check the input value and throw an exception if it does not match the condition.
   *
   * @throws \Papaya\Filter\Exception
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    if (!\preg_match($this->_pattern, $value)) {
      throw new Exception\RegEx\NoMatch($this->_pattern);
    }
    return TRUE;
  }

  /**
   * The filter function is used to read an input value if it is valid.
   *
   * If a submatch identifier is available, it returns the submatch.
   *
   * @param mixed $value
   *
   * @return string|null
   */
  public function filter($value) {
    if (\preg_match($this->_pattern, $value, $matches) && isset($matches[$this->_subMatch])) {
      return $matches[$this->_subMatch];
    }
    return NULL;
  }
}
