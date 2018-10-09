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
 * Papaya Utilities - string functions for Javascript
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Javascript {
  /**
   * Escape and quote javascript string
   *
   * @param string $string
   * @param string $quoteChar
   *
   * @return string
   */
  public static function quote($string, $quoteChar = "'") {
    $result = \str_replace(
      ['\\', "\r", "\n", $quoteChar, '--'],
      ['\\\\', '\\r', '\\n', '\\'.$quoteChar, '-'.$quoteChar.' + '.$quoteChar.'-'],
      $string
    );
    return $quoteChar.$result.$quoteChar;
  }
}
