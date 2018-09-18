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

namespace Papaya\HTTP;

/**
 * Papaya HTTP Client Headers - handles a list of http headers.
 *
 * Headers with the same name replace the original or create an subarray of the values. The header
 * name is normalized to camel case.
 *
 * @package Papaya-Library
 * @subpackage HTTP-Client
 */
class Headers
  implements \IteratorAggregate, \Countable, \ArrayAccess {
  /**
   * Internal header storage array
   *
   * @var array
   */
  protected $_headers = [];

  /**
   * Create object and assign some standard values to the internal storage if provided.
   *
   * @param array $defaults
   */
  public function __construct(array $defaults = NULL) {
    if (isset($defaults)) {
      foreach ($defaults as $name => $value) {
        $this->set($name, $value, TRUE);
      }
    }
  }

  /**
   * Returns the internal headers array
   *
   * @return array
   */
  public function toArray() {
    return $this->_headers;
  }

  /**
   * IteratorAggregate Interface: allow to iterate the headers
   *
   * @return \ArrayIterator
   */
  public function getIterator() {
    return new \ArrayIterator($this->toArray());
  }

  /**
   * Countable Interface: returns the count of the unique headers. Headers with the same name but
   * different values will be counted only once.
   *
   * @return int
   */
  public function count() {
    return \count($this->_headers);
  }

  /**
   * get a request http header value
   *
   * @param string $name
   * @return string|array|null
   */
  public function get($name) {
    if ('' != \trim($name)) {
      $name = $this->normalizeName($name);
      if (!empty($this->_headers[$name])) {
        return $this->_headers[$name];
      }
    }
    return;
  }

  /**
   * set a http header, the second parameter allows to set several headers with the same name.
   *
   * @param string $name header name
   * @param string $value
   * @param bool $allowDuplicates optional, default value FALSE
   * @return bool
   */
  public function set($name, $value, $allowDuplicates = FALSE) {
    if ('' != \trim($name)) {
      $name = $this->normalizeName($name);
      if ($allowDuplicates &&
        isset($this->_headers[$name])) {
        if (!empty($value)) {
          if (isset($this->_headers[$name]) &&
            !\is_array($this->_headers[$name])) {
            $this->_headers[$name] = [
              $this->_headers[$name]
            ];
          }
          $this->_headers[$name][] = $value;
          return TRUE;
        }
      } elseif (empty($value) && isset($this->_headers[$name])) {
        unset($this->_headers[$name]);
        return TRUE;
      } elseif (!empty($value)) {
        $this->_headers[$name] = (string)$value;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * format header names lowercase but each first char
   * (at string start or after a -) has to be uppercase
   *
   * @param string $name
   * @return string
   */
  protected function normalizeName($name) {
    $parts = \explode('-', \strtolower($name));
    return \implode('-', \array_map('ucfirst', $parts));
  }

  /**
   * ArrayAccess Interface: check if an heaer exists
   *
   * @param int $offset
   * @return bool
   */
  public function offsetExists($offset) {
    return isset($this->_headers[$this->normalizeName($offset)]);
  }

  /**
   * ArrayAccess Interface: get an header
   *
   * @param int $offset
   * @return string|array|null
   */
  public function offsetGet($offset) {
    return $this->get($offset);
  }

  /**
   * ArrayAccess Interface: set an header
   *
   * @param int $offset
   * @param mixed $value
   * @internal param $ string|array|NULL
   */
  public function offsetSet($offset, $value) {
    $this->set($offset, $value);
  }

  /**
   * ArrayAccess Interface: remove an header
   *
   * @param int $offset
   */
  public function offsetUnset($offset) {
    unset($this->_headers[$this->normalizeName($offset)]);
  }

  /**
   * Convert the headers array into a string an return it.
   *
   * @return string
   */
  public function __toString() {
    $result = '';
    $lineBreaks = ["\r\n", "\n\r", "\r", "\n"];
    foreach ($this->_headers as $name => $value) {
      if (\is_array($value)) {
        foreach ($value as $subValue) {
          $result .= \str_replace($lineBreaks, ' ', $name.': '.$subValue)."\r\n";
        }
      } else {
        $result .= \str_replace($lineBreaks, ' ', $name.': '.$value)."\r\n";
      }
    }
    return $result;
  }
}
