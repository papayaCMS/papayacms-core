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

namespace Papaya;
/**
 * Papaya URL representation
 *
 * @package Papaya-Library
 * @subpackage URL
 *
 * @property string $scheme
 * @property string $user
 * @property string $pass
 * @property string $password
 * @property string $host
 * @property string $port
 * @property string $path
 * @property string $query
 * @property string $fragment
 * @method string getScheme()
 * @method string getUser()
 * @method string getPass()
 * @method string getPassword()
 * @method string getHost()
 * @method string getPort()
 * @method string getPath()
 * @method string getQuery()
 * @method string getFragment()
 */
class Url {

  /**
   * Parsed url elements
   *
   * @var array|NULL
   */
  protected $_elements = NULL;

  private $_parts = array(
    'scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'
  );

  /**
   * Constructor
   *
   * @param string $url
   * @return \PapayaUrl
   */
  public function __construct($url = '') {
    if (!empty($url)) {
      $this->setUrl($url);
    }
  }

  /**
   * Get request url as string
   *
   * @return string
   */
  public function __toString() {
    try {
      return $this->getUrl();
    } catch (\BadMethodCallException $e) {
    } catch (\InvalidArgumentException $e) {
    }
    return '';
  }

  /**
   * Get request url
   *
   * @return string
   */
  public function getUrl() {
    $result = $this->getPathUrl();
    if (!is_null($query = $this->getQuery())) {
      $result .= '?'.$query;
    }
    if (!is_null($fragment = $this->getFragment())) {
      $result .= '#'.$fragment;
    }
    return $result;
  }

  /**
   * Get request url without query and fragment
   *
   * @return string
   */
  public function getPathUrl() {
    return $this->getHostUrl().$this->getPath();
  }

  /**
   * Get request url without path, query and fragment
   *
   * @return string
   */
  public function getHostUrl() {
    $scheme = $this->getScheme();
    $host = $this->getHost();
    if (!empty($scheme) &&
      !empty($host)) {
      $result = $scheme.'://';
      $user = $this->getUser();
      $password = $this->getPassword();
      if (!empty($user)) {
        $result .= $user.':'.$password.'@';
      }
      $result .= $host;
      $port = $this->getPort();
      if (!empty($port)) {
        $result .= ':'.$port;
      }
      return $result;
    }
    return '';
  }

  /**
   * Set request url attribute
   *
   * @param string $url
   * @return void
   */
  public function setUrl($url) {
    if (!empty($url)) {
      $this->_elements = @parse_url($url);
    }
    if (!is_array($this->_elements)) {
      $this->_elements = array();
    }
  }

  /**
   * Implement generic getter methods using __call
   *
   * @param string $method
   * @param array $arguments
   * @throws \BadMethodCallException
   * @return mixed
   */
  public function __call($method, $arguments) {
    $action = substr($method, 0, 3);
    if ($action == 'get') {
      $property = strtolower(substr($method, 3));
      if ($property == 'password') {
        $property = 'pass';
      }
      return $this->$property;
    }
    throw new \BadMethodCallException(
      sprintf('Invalid method call "%s" on "%s"', $method, __CLASS__)
    );
  }

  /**
   * Map the parts of the url to object properties
   *
   * @param string $name
   * @throws \BadMethodCallException
   * @return mixed
   */
  public function __get($name) {
    if ($name == 'password') {
      $name = 'pass';
    }
    if (in_array($name, $this->_parts)) {
      return empty($this->_elements[$name]) ? NULL : $this->_elements[$name];
    } else {
      throw new \BadMethodCallException(sprintf('Invalid property "%s::%s".', __CLASS__, $name));
    }
  }

  /**
   * check if the property has a setter and call it
   *
   * @param string $name
   * @param $value
   * @throws \BadMethodCallException
   */
  public function __set($name, $value) {
    if (in_array($name, $this->_parts)) {
      $setter = 'set'.ucfirst($name);
      if (method_exists($this, $setter)) {
        $this->$setter($value);
      } else {
        throw new \BadMethodCallException(
          sprintf('Property "%s::%s" is not writeable.', __CLASS__, $name)
        );
      }
    } else {
      throw new \BadMethodCallException(sprintf('Invalid property "%s::%s".', __CLASS__, $name));
    }
  }

  /**
   * set scheme if it is valid, throw an exception if not.
   *
   * @throws \InvalidArgumentException
   * @param string $scheme
   */
  public function setScheme($scheme) {
    if (preg_match('(^[a-z_()-]+$)D', $scheme)) {
      $this->_elements['scheme'] = $scheme;
    } else {
      throw new \InvalidArgumentException(
        sprintf('Invalid argument #0 for %s::%s.', __CLASS__, __METHOD__)
      );
    }
  }

  /**
   * set host if it is valid, throw an exception if not.
   *
   * @throws \InvalidArgumentException
   * @param string $host
   */
  public function setHost($host) {
    $regex = '((^[0-9a-z-_\.]+\.[a-z]+$)|(^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$))Di';
    if (preg_match($regex, $host)) {
      $this->_elements['host'] = $host;
    } else {
      throw new \InvalidArgumentException(
        sprintf('Invalid argument #0 for %s: "%s"', __METHOD__, $host)
      );
    }
  }

  /**
   * set port if it is valid, throw an exception if not.
   *
   *
   * @param $port
   * @throws \InvalidArgumentException
   */
  public function setPort($port) {
    if (preg_match('(^[0-9]+$)D', $port)) {
      $this->_elements['port'] = $port;
    } else {
      throw new \InvalidArgumentException(
        sprintf('Invalid argument #0 for %s::%s.', __CLASS__, __METHOD__)
      );
    }
  }

  /**
   * set path if it is valid, throw an exception if not.
   *
   * @throws \InvalidArgumentException
   * @param string $path
   */
  public function setPath($path) {
    if (preg_match('(^/[^?#\r\n]*$)D', $path)) {
      $this->_elements['path'] = $path;
    } else {
      throw new \InvalidArgumentException(
        sprintf('Invalid argument #0 for %s::%s.', __CLASS__, __METHOD__)
      );
    }
  }

  /**
   * set query if it is valid, throw an exception if not.
   *
   *
   * @param $query
   * @throws \InvalidArgumentException
   * @internal param string $path
   * @internal param string $query
   */
  public function setQuery($query) {
    if (preg_match('(^[^?#\r\n]*$)D', $query)) {
      $this->_elements['query'] = $query;
    } else {
      throw new \InvalidArgumentException(
        sprintf('Invalid argument #0 for %s::%s.', __CLASS__, __METHOD__)
      );
    }
  }

  /**
   * set fragment if it is valid, throw an exception if not.
   *
   *
   * @param string $fragment
   * @throws \InvalidArgumentException
   * @internal param string $query
   */
  public function setFragment($fragment) {
    if (preg_match('(^[^?#\r\n]*$)D', $fragment)) {
      $this->_elements['fragment'] = $fragment;
    } else {
      throw new \InvalidArgumentException(
        sprintf('Invalid argument #0 for %s::%s.', __CLASS__, __METHOD__)
      );
    }
  }
}
