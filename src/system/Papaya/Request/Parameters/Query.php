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
* Decode a query string into an array or encode an array into an query string
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParametersQuery {

  /**
  * Additional group separator ([] is always supported)
  * @var string
  */
  private $_separator = '';

  /**
  * Values object
  * @var PapayaRequestParameters
  */
  private $_values = NULL;

  /**
  * Initialize object and set group separator.
  *
  * @param string $groupSeparator
  */
  public function __construct($groupSeparator = '') {
    $this->setSeparator($groupSeparator);
  }

  /**
  * Set the group separator
  *
  * @throws \InvalidArgumentException
  * @param string $groupSeparator
  */
  public function setSeparator($groupSeparator) {
    if (in_array($groupSeparator, array(',', ':', '/', '*', '!'))) {
      $this->_separator = $groupSeparator;
    } elseif (in_array($groupSeparator, array('', '[]'))) {
      $this->_separator = '';
    } else {
      throw new \InvalidArgumentException(
        sprintf('Invalid separator value "%s".', $groupSeparator)
      );
    }
  }

  /**
  * Get/set the values object
  *
  * @param \PapayaRequestParameters $values
  * @return \PapayaRequestParameters
  */
  public function values(\PapayaRequestParameters $values = NULL) {
    if (isset($values)) {
      $this->_values = $values;
    }
    if (is_null($this->_values)) {
      $this->_values = new \PapayaRequestParameters();
    }
    return $this->_values;
  }

  /**
  * Set the query string (parse into values)
  *
  * @param string $queryString
  * @param boolean $stripSlashes
  * @return \PapayaRequestParametersQuery
  */
  public function setString($queryString, $stripSlashes = FALSE) {
    if (isset($queryString)) {
      $this->_values = new \PapayaRequestParameters();
      $this->_decode($queryString, $stripSlashes);
    }
    return $this;
  }

  /**
  * Get the query string
  *
  * @return string
  */
  public function getString() {
    return $this->_encode(NULL, $this->values()->toArray());
  }

  /**
   * Load parameters from urlencoded string (query string)
   *
   * @param string $queryString
   * @param boolean $stripSlashes
   */
  private function _decode($queryString, $stripSlashes = FALSE) {
    if (!empty($queryString)) {
      $parts = explode('&', $queryString);
      foreach ($parts as $part) {
        if (FALSE !== ($pos = strpos($part, '='))) {
          $name = urldecode(substr($part, 0, $pos));
          $value = urldecode(substr($part, $pos + 1));
          $this->_values->set($name, $this->_prepare($value, $stripSlashes), $this->_separator);
        } else {
          $name = urldecode($part);
          $this->_values->set($name, TRUE, $this->_separator);
        }
      }
    }
  }

  /**
  * Prepare parameters, make sure it is utf8 and strip slashes if needed
  *
  * @param string|array $parameter
  * @param boolean $stripSlashes
  * @return array|string
  */
  private function _prepare($parameter, $stripSlashes = FALSE) {
    if ($stripSlashes) {
      $parameter = stripslashes($parameter);
    }
    return \PapayaUtilStringUtf8::ensure($parameter);
  }

  /**
  * Encode recursive parameters array
  *
  * @param string $prefix
  * @param array $parameters
  * @param integer $maxRecursions
  * @return string
  */
  private function _encode($prefix, $parameters, $maxRecursions = 10) {
    $result = '';
    if (is_array($parameters)) {
      uksort($parameters, 'strnatcasecmp');
      foreach ($parameters as $name => $value) {
        if (empty($prefix)) {
          $fullName = urlencode($name);
        } elseif ($this->_separator == '[]' || empty($this->_separator)) {
          $fullName = $prefix.'['.urlencode($name).']';
        } else {
          $fullName = $prefix.$this->_separator.urlencode($name);
        }
        if (is_array($value) && !empty($value)) {
          $result .= '&'.$this->_encode(
            $fullName, $value, $maxRecursions - 1
          );
        } elseif (is_scalar($value)) {
          $result .= '&'.$fullName.'='.urlencode($value);
        } elseif (is_object($value) && method_exists($value, '__toString')) {
          $result .= '&'.$fullName.'='.urlencode((string)$value);
        }
      }
    }
    return substr($result, 1);
  }
}
