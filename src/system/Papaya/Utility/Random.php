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
 * Provides some function to get random values
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Random {
  /**
   * Abstraction for PHPs rand and mt_rand functions, uses mt_rand if possible.
   *
   * @param int $min
   * @param int $max
   * @return int
   */
  public static function rand($min = NULL, $max = NULL) {
    $random = \function_exists('mt_rand') ? 'mt_rand' : 'rand';
    if (\is_null($min)) {
      return $random();
    } else {
      return $random($min, $max);
    }
  }

  /**
   * Get a randomized id string
   *
   * @return string
   */
  public static function getId() {
    return \uniqid(self::rand(), TRUE);
  }
}
