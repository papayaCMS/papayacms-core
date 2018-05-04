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
* Static utility class to check if thttp or http is used.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilServerProtocol {

  const BOTH = 0;
  const HTTP = 1;
  const HTTPS = 2;

  /**
  * Return TRUE if 'https' is used, FALSE if not.
  *
  * If the custom header X-PAPAYA-HTTPS is provided it is compared to the constant
  * PAPAYA_HEADER_HTTPS_TOKEN. If the header equals the token and the token is 32 bytes the
  * request is threated as HTTPS always.
  *
  * @return boolean
  */
  public static function isSecure() {
    if (isset($_SERVER['X_PAPAYA_HTTPS'])) {
      $header = $_SERVER['X_PAPAYA_HTTPS'];
    } elseif (isset($_SERVER['HTTP_X_PAPAYA_HTTPS'])) {
      $header = $_SERVER['HTTP_X_PAPAYA_HTTPS'];
    } else {
      $header = NULL;
    }
    if (isset($header) &&
        defined('PAPAYA_HEADER_HTTPS_TOKEN') &&
        strlen(PAPAYA_HEADER_HTTPS_TOKEN) == 32 &&
        $header == PAPAYA_HEADER_HTTPS_TOKEN) {
      return TRUE;
    }
    return (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on');
  }

  /**
  * Return the currently used protocol. "http" or "https".
  *
  * @param integer $mode
  * @return string
  */
  public static function get($mode = self::BOTH) {
    if ($mode != self::BOTH) {
      return $mode == self::HTTPS ? 'https' : 'http';
    } else {
      return self::isSecure() ? 'https' : 'http';
    }
  }

  /**
  * Return the default port
  *
  * @return string
  */
  public static function getDefaultPort() {
    return (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on')
      ? 443 : 80;
  }
}
