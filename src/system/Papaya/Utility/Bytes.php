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
namespace Papaya\Utility;

/**
 * A bunch of bytes related utility functions.
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Bytes {
  private static $_exponents = [
    'YB' => 8,
    'ZB' => 7,
    'EB' => 6,
    'PB' => 5,
    'TB' => 4,
    'GB' => 3,
    'MB' => 2,
    'kB' => 1,
    'B' => 0,
  ];

  private static $_mapping = [
    'b' => 'B', 'bytes' => 'B',
    'kb' => 'kB', 'k' => 'kB', 'kilo' => 'kB',
    'mb' => 'MB', 'm' => 'MB', 'mega' => 'MB',
    'gb' => 'GB', 'g' => 'GB', 'giga' => 'GB',
    'tb' => 'TB', 't' => 'TB', 'tera' => 'TB',
    'pb' => 'PB', 'p' => 'PB', 'peta' => 'PB',
    'eb' => 'EB', 'e' => 'EB', 'exa' => 'EB',
    'zb' => 'ZB', 'z' => 'ZB', 'zeta' => 'ZB',
    'yb' => 'YB', 'y' => 'YB', 'yota' => 'YB',
  ];

  /**
   * Format a given bytes value into a human readable string
   *
   * @param int $bytes
   * @param int $decimals
   * @param string $decimalSeparator
   *
   * @return string
   */
  public static function toString($bytes, $decimals = 2, $decimalSeparator = '.') {
    $unit = 'B';
    $size = $bytes;
    foreach (self::$_exponents as $unit => $exponent) {
      if ($exponent > 0) {
        $factor = \pow(1024, $exponent);
        if ($bytes > $factor) {
          $size = $bytes / $factor;
          break;
        }
      } else {
        return \round($bytes).' '.$unit;
      }
    }
    return \number_format($size, $decimals, $decimalSeparator, '').' '.$unit;
  }

  /**
   * Convert a string containing a unit into an integer
   *
   * @param string $string
   *
   * @return int
   */
  public static function fromString($string) {
    $string = \trim($string);
    if (\preg_match('((?P<size>[\d.,]+)\s*(?P<unit>[a-z]*))i', $string, $matches)) {
      $size = \Papaya\Utility\Arrays::get($matches, 'size', 0);
      $unit = \strtolower(\Papaya\Utility\Arrays::get($matches, 'unit', ''));
    } else {
      $size = $string;
      $unit = '';
    }
    if (isset(self::$_mapping[$unit])) {
      $exponent = self::$_exponents[self::$_mapping[$unit]];
    } else {
      $exponent = 0;
    }
    if ($exponent > 0) {
      return (int)$size * \pow(1024, $exponent);
    }
    return (int)$size;
  }
}
