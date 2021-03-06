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
 * Papaya filter class for validate text optionally including digits
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Text implements Filter {
  const ALLOW_SPACES = 1;

  const ALLOW_LINES = 2;

  const ALLOW_DIGITS = 4;

  /**
   * @var int
   */
  private $_options;

  /**
   * Create object and store options to match additional character groups
   *
   * @param int $options
   */
  public function __construct($options = self::ALLOW_SPACES) {
    $this->_options = $options;
  }

  /**
   * Return a pattern matching invalid characters depending on the stored options.
   *
   * @return string
   */
  private function getPattern() {
    $result = '([^\\pL\\pP';
    if (\Papaya\Utility\Bitwise::inBitmask(self::ALLOW_SPACES, $this->_options)) {
      $result .= '\\p{Zs} ';
    }
    if (\Papaya\Utility\Bitwise::inBitmask(self::ALLOW_LINES, $this->_options)) {
      $result .= '\\p{Zl}\\r\\n';
    }
    if (\Papaya\Utility\Bitwise::inBitmask(self::ALLOW_DIGITS, $this->_options)) {
      $result .= '\\pN';
    }
    $result .= ']+)u';
    return $result;
  }

  /**
   * Validate the given value and throw an filter exception with the position of the invalid
   * character.
   *
   * @param mixed $value
   *
   * @throws Exception
   *
   * @return true
   */
  public function validate($value) {
    if (\is_array($value)) {
      throw new Exception\UnexpectedType('string');
    }
    if ('' === \trim($value)) {
      throw new Exception\IsEmpty();
    }
    $pattern = $this->getPattern();
    if (\preg_match($pattern, $value, $matches, PREG_OFFSET_CAPTURE)) {
      throw new Exception\InvalidCharacter($value, $matches[0][1]);
    }
    return TRUE;
  }

  /**
   * Remove all invalid characters from the value, return NULL if the value is empty after that
   *
   * @param mixed $value
   *
   * @return string|null
   */
  public function filter($value) {
    if (\is_array($value)) {
      return NULL;
    }
    $value = \preg_replace($this->getPattern(), '', $value);
    return empty($value) ? NULL : $value;
  }
}
