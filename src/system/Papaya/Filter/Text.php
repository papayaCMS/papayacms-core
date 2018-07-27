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

/**
* Papaya filter class for validate text optionally including digits
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterText implements \Papaya\Filter {

  const ALLOW_SPACES = 1;
  const ALLOW_LINES = 2;
  const ALLOW_DIGITS = 4;

  /**
   * @var integer
   */
  private $_options = self::ALLOW_SPACES;

  /**
   * Create object and store options to match additional character groups
   *
   * @param integer $options
   */
  public function __construct($options = self::ALLOW_SPACES) {
    $this->_options = $options;
  }

  /**
   * Return a pattern matching invali characters depending on the stored options.
   *
   * @return string^
   */
  private function getPattern() {
    $result = '([^\\pL\\pP';
    if (\PapayaUtilBitwise::inBitmask(self::ALLOW_SPACES, $this->_options)) {
      $result .= '\\p{Zs} ';
    }
    if (\PapayaUtilBitwise::inBitmask(self::ALLOW_LINES, $this->_options)) {
      $result .= '\\p{Zl}\\r\\n';
    }
    if (\PapayaUtilBitwise::inBitmask(self::ALLOW_DIGITS, $this->_options)) {
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
   * @throws \PapayaFilterExceptionEmpty
   * @throws \Papaya\Filter\Exception\InvalidCharacter
   * @return TRUE
   */
  public function validate($value) {
    if (is_array($value)) {
      throw new \PapayaFilterExceptionType('string');
    }
    if (trim($value) == '') {
      throw new \PapayaFilterExceptionEmpty();
    }
    $pattern = $this->getPattern();
    if (preg_match($pattern, $value, $matches, PREG_OFFSET_CAPTURE)) {
      throw new \Papaya\Filter\Exception\InvalidCharacter($value, $matches[0][1]);
    }
    return TRUE;
  }


  /**
   * Remove all invalid characters from the value, return NULL if the value is empty after that
   *
   * @param mixed|NULL $value
   * @return string|NULL
   */
  public function filter($value) {
    if (is_array($value)) {
      return NULL;
    }
    $value = preg_replace($this->getPattern(), '', $value);
    return empty($value) ? NULL : $value;
  }

}
