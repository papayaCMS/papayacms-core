<?php
/**
* Static utility class to fetch the request method.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Method.php 39638 2014-03-19 18:37:54Z weinert $
*/

/**
* Static utility class to fetch the request method. Includes several validation shortcut method
* for the most used request methods.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilRequestMethod {

  const FORMAT_UPPERCASE = 0;
  const FORMAT_LOWERCASE = 1;

  /**
  * fetch the current request method from environment
  *
  * @param integer $format
  * @return string
  */
  public static function get($format = self::FORMAT_LOWERCASE) {
    $method = empty($_SERVER['REQUEST_METHOD']) ? 'GET' : $_SERVER['REQUEST_METHOD'];
    return $format ? strtolower($method) : strtoupper($method);
  }

  /**
   * Validation shortcut to check if the request method is GET
   *
   * @return boolean
   */
  public static function isGet() {
    return self::get() == 'get';
  }

  /**
   * Validation shortcut to check if the request method is POST
   *
   * @return boolean
   */
  public static function isPost() {
    return self::get() == 'post';
  }

  /**
   * Validation shortcut to check if the request method is PUT
   *
   * @return boolean
   */
  public static function isPut() {
    return self::get() == 'put';
  }
}