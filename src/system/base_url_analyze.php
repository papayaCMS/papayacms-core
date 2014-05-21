<?php
/**
* url parsing and comparsion
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Core
* @version $Id: base_url_analyze.php 39654 2014-03-20 11:41:46Z weinert $
*/

/**
* url parsing and comparsion
*
* @package Papaya
* @subpackage Core
*/
class base_url_analyze {

  /**
   * This method parses an url similar to parse_url without checking its validity.
   *
   * @param string $url an url
   * @param string|null $component
   * @return array $result list of url parts:
   *         scheme::/user:pass@host:port/path?query#fragment
   */
  public static function parseURL($url, $component = NULL) {
    $result = array(
      'scheme' => '',
      'host' => '',
      'port' => '',
      'user' => '',
      'pass' => '',
      'path' => '',
      'query' => '',
      'fragment' => '',
    );
    $pos = strpos($url, '#');
    if ($pos !== FALSE) {
      $result['fragment'] = substr($url, $pos + 1);
      $url = substr($url, 0, $pos);
    }
    $pos = strpos($url, '?');
    if ($pos !== FALSE) {
      $result['query'] = substr($url, $pos + 1);
      $url = substr($url, 0, $pos);
    }
    $pos = strpos($url, '://');
    if ($pos !== FALSE) {
      $result['scheme'] = substr($url, 0, $pos);
      $url = substr($url, $pos + 3);
    }
    $pos = strpos($url, '/');
    if ($pos !== FALSE) {
      $result = array_merge(
        $result,
        self::parseDomain(substr($url, 0, $pos))
      );
      $url = substr($url, $pos);
    } else {
      $result = array_merge(
        $result,
        self::parseDomain($url)
      );
      $url = '';
    }
    $result['path'] = $url;
    if (isset($component)) {
      switch ($component) {
      case PHP_URL_SCHEME :
        return $result['scheme'];
      case PHP_URL_HOST :
        return $result['host'];
      case PHP_URL_PORT :
        return $result['port'];
      case PHP_URL_USER :
        return $result['user'];
      case PHP_URL_PASS :
        return $result['pass'];
      case PHP_URL_PATH :
        return $result['path'];
      case PHP_URL_QUERY :
        return $result['query'];
      case PHP_URL_FRAGMENT :
        return $result['fragment'];
      }
    }
    return $result;
  }

  /**
  * This method parses the domain part of an url without checking its validity.
  *
  * @param string $domain
  * @access public
  * @return array $result list of domain parts:
  *         user:pass@host:port
  */
  public static function parseDomain($domain) {
    $result = array();
    if ($pos = strpos($domain, '@')) {
      $auth = substr($domain, 0, $pos);
      $domain = substr($domain, $pos + 1);
      if ($pos = strpos($auth, ':')) {
        $result['user'] = substr($auth, 0, $pos);
        $result['pass'] = substr($auth, $pos + 1);
      } else {
        $result['user'] = $auth;
      }
    }
    if ($pos = strpos($domain, ':')) {
      $result['port'] = substr($domain, $pos + 1);
      $result['host'] = substr($domain, 0, $pos);
    } else {
      $result['host'] = $domain;
    }
    return $result;
  }

  /**
  * this method compares the path depth of two urls (it counts the / in the path part)
  *
  * @param string $url1
  * @param string $url2
  * @access public
  * @return integer | FALSE
  *    an integer result is the difference from $url1 to $url2
  *    FALSE means that the host:port of the urls is different
  */
  public static function comparePathDepth($url1, $url2) {
    $urlParts1 = self::parseURL($url1);
    $urlParts2 = self::parseURL($url2);
    if ($urlParts1['scheme'] == $urlParts2['scheme'] &&
        $urlParts1['host'] == $urlParts2['host'] &&
        $urlParts1['port'] == $urlParts2['port']) {
      return substr_count($urlParts1['path'], '/') - substr_count($urlParts2['path'], '/');
    } else {
      return FALSE;
    }
  }
}

