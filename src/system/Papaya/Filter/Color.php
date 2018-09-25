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
 * This filter class checks a color.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Color implements Filter {
  /**
   * Pattern to check for a linebreak
   *
   * @var string
   */
  private $_patternCheck = '(
      ^\\#
      (?:
        [a-fA-F\\d]{3}|
        [a-fA-F\\d]{6}
      )$
    )uxD';

  /**
   * Check the value if it's a valid color, if not throw an exception.
   *
   * @throws Exception\UnexpectedType
   *
   * @param string $value
   *
   * @return true
   */
  public function validate($value) {
    if (!\preg_match($this->_patternCheck, $value)) {
      throw new Exception\UnexpectedType('color');
    }
    return TRUE;
  }

  /**
   * The filter function is used to read an input value if it is valid.
   *
   * @param string $value
   *
   * @return string
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (Exception $e) {
      return NULL;
    }
  }
}
