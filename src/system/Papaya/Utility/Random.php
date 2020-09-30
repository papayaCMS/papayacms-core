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
   *
   * @return int
   */
  public static function rand($min = 0, $max = NULL) {
    return self::randomInt($min, $max);
  }

  public static function randomInt($min = 0, $max = NULL) {
    if (function_exists('random_int')) {
      /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
      return random_int($min, $max !== NULL ? $max : 2147483647);
    }
    return mt_rand($min, $max !== NULL ? $max : mt_getrandmax());
  }

  /**
   * @param int $min
   * @param int $max
   * @return float|int|mixed
   */
  public static function randomFloat($min = 0, $max = 1) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
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
