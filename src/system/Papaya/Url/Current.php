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

namespace Papaya\Url;
/**
 * Papaya URL representation, representing the current url
 *
 * @package Papaya-Library
 * @subpackage URL
 */
class Current extends \Papaya\Url {

  /**
   * If no $url is provided, the object will compile it from server environment
   *
   * @param string $url
   */
  public function __construct($url = NULL) {
    parent::__construct(
      empty($url) ? $this->getUrlFromEnvironment() : $url
    );
  }

  /**
   * Compile url string from server environment variables
   *
   * @return string|NULL
   */
  public function getUrlFromEnvironment() {
    $scheme = \PapayaUtilServerProtocol::get();
    $port = $this->_getServerValue(
      'SERVER_PORT', ':', \PapayaUtilServerProtocol::getDefaultPort()
    );
    $host = $this->_getServerValue(array('HTTP_HOST', 'SERVER_NAME'));
    $requestUri = $this->_getServerValue('REQUEST_URI');
    if (!empty($host)) {
      return $scheme.'://'.$host.$port.$requestUri;
    } else {
      return NULL;
    }
  }

  /**
   * Get server value
   *
   * @param array|string $keys
   * @param string $prefix
   * @param string $ignoreValue
   * @return string
   */
  private function _getServerValue($keys, $prefix = '', $ignoreValue = '') {
    if (!is_array($keys)) {
      $keys = array($keys);
    }
    foreach ($keys as $key) {
      if (!empty($_SERVER[$key]) &&
        $ignoreValue != $_SERVER[$key]) {
        $result = $_SERVER[$key];
      }
    }
    if (!empty($result)) {
      return $prefix.$result;
    } else {
      return '';
    }
  }
}
