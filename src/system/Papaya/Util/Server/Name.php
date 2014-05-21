<?php
/**
* Static utility class to fetch the server name.
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
* @version $Id: Name.php 36434 2011-11-21 16:06:07Z weinert $
*/

/**
* Static utility class to fetch the server name.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilServerName {

  /**
  * fetch the current server name from environment
  *
  * @return string
  */
  public static function get() {
    if (!empty($_SERVER['HTTP_HOST'])) {
      return $_SERVER['HTTP_HOST'];
    } elseif (!empty($_SERVER['SERVER_NAME'])) {
      return $_SERVER['SERVER_NAME'];
    } else {
      return '';
    }
  }
}