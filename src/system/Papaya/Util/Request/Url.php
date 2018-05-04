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

/**
* Static utility class to fetch the absolute request url.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilRequestUrl {

  /**
  * fetch the current request url from environment
  *
  * @return string
  */
  public static function get() {
    $host = \PapayaUtilServerName::get();
    $port = \PapayaUtilServerPort::get();
    if (empty($host)) {
      return '';
    } else {
      return sprintf(
        '%s://%s%s%s',
        \PapayaUtilServerProtocol::get(),
        $host,
        $port != \PapayaUtilServerProtocol::getDefaultPort() ? ':'.$port : '',
        empty($_SERVER['REQUEST_URI']) ? '/' : $_SERVER['REQUEST_URI']
      );
    }
  }
}
