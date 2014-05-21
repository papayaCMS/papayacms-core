<?php
/**
* Provides some function to get random values
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Util
* @version $Id: Random.php 34538 2010-07-16 11:42:54Z weinert $
*/

/**
* Provides some function to get random values
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilRandom {

  /**
  * Abstraction for PHPs rand and mt_rand functions, uses mt_rand if possible.
  *
  * @param integer $min
  * @param integer $max
  * @return integer
  */
  public static function rand($min = NULL, $max = NULL) {
    $random = function_exists('mt_rand') ? 'mt_rand' : 'rand';
    if (is_null($min)) {
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
    return uniqid(self::rand(), TRUE);
  }
}