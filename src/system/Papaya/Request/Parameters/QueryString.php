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
namespace Papaya\Request\Parameters;

use Papaya\Request;

/**
 * Decode a query string into an array or encode an array into an query string
 *
 * @package Papaya-Library
 * @subpackage Request
 */
class QueryString {
  /**
   * Additional group separator ([] is always supported)
   *
   * @var string
   */
  private $_separator = '';

  /**
   * Values object
   *
   * @var Request\Parameters
   */
  private $_values;

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
   *
   * @param string $groupSeparator
   */
  public function setSeparator($groupSeparator) {
    $groupSeparator = \trim($groupSeparator);
    if (\in_array($groupSeparator, GroupSeparator::CHARACTERS)) {
      $this->_separator = $groupSeparator;
    } elseif ($groupSeparator === '' || $groupSeparator === GroupSeparator::ARRAY_SYNTAX) {
      $this->_separator = '';
    } else {
      throw new \InvalidArgumentException(
        \sprintf('Invalid separator value "%s".', $groupSeparator)
      );
    }
  }

  public function getSeparator(): string {
    return $this->_separator;
  }

  /**
   * Get/set the values object
   *
   * @param Request\Parameters $values
   *
   * @return Request\Parameters
   */
  public function values(Request\Parameters $values = NULL) {
    if (NULL !== $values) {
      $this->_values = $values;
    } elseif (NULL === $this->_values) {
      $this->_values = new Request\Parameters();
    }
    return $this->_values;
  }

  /**
   * Set the query string (parse into values)
   *
   * @param string $queryString
   * @param bool $stripSlashes
   *
   * @return self
   */
  public function setString($queryString, $stripSlashes = FALSE) {
    if (NULL !== $queryString) {
      $this->_values = new Request\Parameters();
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
   * @param bool $stripSlashes
   */
  private function _decode($queryString, $stripSlashes = FALSE) {
    if (!empty($queryString)) {
      $parts = \explode('&', $queryString);
      foreach ($parts as $part) {
        if (FALSE !== ($pos = \strpos($part, '='))) {
          $name = \urldecode(\substr($part, 0, $pos));
          $value = \urldecode(\substr($part, $pos + 1));
          $this->_values->set($name, $this->_prepare($value, $stripSlashes));
        } else {
          $name = \urldecode($part);
          $this->_values->set($name, TRUE);
        }
      }
    }
  }

  /**
   * Prepare parameters, make sure it is utf8 and strip slashes if needed
   *
   * @param string|array $parameter
   * @param bool $stripSlashes
   *
   * @return array|string
   */
  private function _prepare($parameter, $stripSlashes = FALSE) {
    if ($stripSlashes) {
      $parameter = \stripslashes($parameter);
    }
    return \Papaya\Utility\Text\UTF8::ensure($parameter);
  }

  /**
   * Encode recursive parameters array
   *
   * @param string $prefix
   * @param array $parameters
   * @param int $maxRecursions
   *
   * @return string
   */
  private function _encode($prefix, $parameters, $maxRecursions = 10) {
    $result = '';
    if (\is_array($parameters)) {
      \uksort($parameters, 'strnatcasecmp');
      foreach ($parameters as $name => $value) {
        if (empty($prefix)) {
          $fullName = \urlencode($name);
        } elseif (GroupSeparator::ARRAY_SYNTAX === $this->_separator || empty($this->_separator)) {
          $fullName = $prefix.'['.\urlencode($name).']';
        } else {
          $fullName = $prefix.$this->_separator.\urlencode($name);
        }
        if (\is_array($value) && !empty($value)) {
          $result .= '&'.$this->_encode(
              $fullName, $value, $maxRecursions - 1
            );
        } elseif (\is_scalar($value)) {
          $result .= '&'.$fullName.'='.\urlencode($value);
        } elseif (\is_object($value) && \method_exists($value, '__toString')) {
          $result .= '&'.$fullName.'='.\urlencode((string)$value);
        }
      }
    }
    return \substr($result, 1);
  }
}
