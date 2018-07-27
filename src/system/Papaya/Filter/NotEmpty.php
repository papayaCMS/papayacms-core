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
/**
 * Validate that a value contains at least one character
 *
 * By default whitespace chars are ignored, too.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class NotEmpty implements \Papaya\Filter {

  /**
   * Values with only whitespaces are considered empty, too.
   *
   * @var boolean
   */
  private $_ignoreSpaces = TRUE;

  /**
   * Initialize object and store ignore option.
   *
   * @param boolean $ignoreSpaces
   */
  public function __construct($ignoreSpaces = TRUE) {
    \PapayaUtilConstraints::assertBoolean($ignoreSpaces);
    $this->_ignoreSpaces = $ignoreSpaces;
  }

  /**
   * Check for empty string. If $value is not empty and whitespace are ignored,
   * check the trimmed version, too.
   *
   * @throws \Papaya\Filter\Exception
   * @param mixed $value
   * @return bool
   */
  public function validate($value) {
    if (isset($value) && is_array($value)) {
      if (count($value) <= 0) {
        throw new \Papaya\Filter\Exception\IsEmpty();
      }
    } else {
      $value = (string)$value;
      if ($value === '' ||
        ($this->_ignoreSpaces && trim($value) === '')) {
        throw new \Papaya\Filter\Exception\IsEmpty();
      }
    }
    return TRUE;
  }

  /**
   * If spaces are ignored trim the value. If the value is empty return NULL.
   *
   * @throws \Papaya\Filter\Exception
   * @param mixed $value
   * @return string|NULL
   */
  public function filter($value) {
    if (isset($value) && is_array($value)) {
      return (count($value) > 0) ? $value : NULL;
    } else {
      if ($this->_ignoreSpaces) {
        $value = trim($value);
      }
      return ($value == '') ? NULL : (string)$value;
    }
  }
}
