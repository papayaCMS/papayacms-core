<?php
/**
* Papaya Utiltities - string normalization
*
* @copyright 2009-2011 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Normalize.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilStringNormalize {

  /**
   * Format http header names lowercase but each first char
   * (at string start or after a -) has to be uppercase
   *
   * @param string $string
   * @return string
   */
  public static function toHttpHeaderName($string) {
    $parts = explode('-', strtolower($string));
    return implode('-', array_map('ucfirst', $parts));
  }
}