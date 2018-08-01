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

namespace Papaya\Utility\Request;
/**
 * Static utility class to fetch the absolute request url.
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Url {

  /**
   * fetch the current request url from environment
   *
   * @return string
   */
  public static function get() {
    $host = \Papaya\Utility\Server\Name::get();
    $port = \Papaya\Utility\Server\Port::get();
    if (empty($host)) {
      return '';
    } else {
      return sprintf(
        '%s://%s%s%s',
        \Papaya\Utility\Server\Protocol::get(),
        $host,
        $port != \Papaya\Utility\Server\Protocol::getDefaultPort() ? ':'.$port : '',
        empty($_SERVER['REQUEST_URI']) ? '/' : $_SERVER['REQUEST_URI']
      );
    }
  }
}
