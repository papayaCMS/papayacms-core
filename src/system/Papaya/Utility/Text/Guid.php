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

namespace Papaya\Utility\Text;
/**
 * Papaya Utilities - guid validation and normalization
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Guid {

  /**
   * Validate if a string is a guid, by default an exception is thrown, but this can be supressed
   * with the second argument.
   *
   * @param string $guid
   * @param boolean $silent
   * @throws \UnexpectedValueException
   * @return boolean
   */
  public static function validate($guid, $silent = FALSE) {
    if (!preg_match('(^[a-fA-F\d]{32}$)D', $guid)) {
      if ($silent) {
        return FALSE;
      }
      throw new \UnexpectedValueException(
        sprintf('Invalid guid: "%s".', $guid)
      );
    }
    return TRUE;
  }

  /**
   * Normalize a given guid to lowercase letters
   *
   * @param string $guid
   * @param boolean $silent
   * @return string
   */
  public static function toLower($guid, $silent = FALSE) {
    return self::validate($guid, $silent) ? strtolower($guid) : '';
  }

  /**
   * Normalize a given guid to uppercase letters
   *
   * @param string $guid
   * @param boolean $silent
   * @return string
   */
  public static function toUpper($guid, $silent = FALSE) {
    return self::validate($guid, $silent) ? strtoupper($guid) : '';
  }
}
