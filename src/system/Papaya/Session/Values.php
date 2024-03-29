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
namespace Papaya\Session;

use Papaya\Filter;

/**
 * Provide an array like access to session values. Allow to use complex identifiers. Handle
 * sessions that are not started.
 *
 * @package Papaya-Library
 * @subpackage Session
 */
class Values implements \ArrayAccess {
  /**
   * Linked session object
   *
   * @var \Papaya\Session
   */
  private $_session;

  private $_fallback = [];

  /**
   * Initialize object and link session object
   *
   * @param \Papaya\Session $session
   */
  public function __construct(\Papaya\Session $session) {
    $this->_session = $session;
  }

  /**
   * Check if the session variable exists
   *
   * @param mixed $identifier
   *
   * @return bool
   */
  public function offsetExists($identifier): bool {
    $key = $this->_compileKey($identifier);
    if ($this->_session->wrapper()->hasValue($key)) {
      return TRUE;
    }
    return \array_key_exists($key, $this->_fallback);
  }

  /**
   * Get a session value if the session is active and the value exists. Return NULL otherwise.
   *
   * @param mixed $identifier
   *
   * @return mixed
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($identifier) {
    $key = $this->_compileKey($identifier);
    if ($this->_session->isActive()) {
      return $this->_session->wrapper()->hasValue($key) ? $this->_session->wrapper()->readValue($key) : NULL;
    }
    if (isset($this->_fallback[$key])) {
      return $this->_fallback[$key];
    }
    return NULL;
  }

  /**
   * Alias for {@see \Papaya\Session\Values::offsetGet()}.
   *
   * @param mixed $identifier
   *
   * @param null $defaultValue
   * @param Filter|null $filter
   * @return mixed
   */
  public function get($identifier, $defaultValue = NULL, Filter $filter = NULL) {
    $value = $this->offsetGet($identifier);
    if (NULL !== $value && $filter instanceof Filter) {
      $value = $filter->filter($value);
    }
    if (NULL === $value) {
      return $defaultValue;
    }
    if (NULL === $defaultValue) {
      return $value;
    }
    if (\is_array($defaultValue)) {
      return \is_array($value) ? $value : $defaultValue;
    }
    if (\is_object($defaultValue) && \method_exists($defaultValue, '__toString')) {
      return \is_string($value) ? $value : (string)$defaultValue;
    }
    if (\is_scalar($defaultValue)) {
      $type = \gettype($defaultValue);
      \settype($value, $type);
      return $value;
    }
    return $defaultValue;
  }

  /**
   * Set a session value, if the session is inactive the value will not be stored.
   *
   * @param mixed $identifier
   * @param mixed $value
   */
  public function offsetSet($identifier, $value): void {
    $key = $this->_compileKey($identifier);
    if ($this->_session->isActive()) {
      $this->_session->wrapper()->storeValue($key, $value);
    }
    $this->_fallback[$key] = $value;
  }

  /**
   * Alias for {@see \Papaya\Session\Values::offsetSet()}.
   *
   * @param mixed $identifier
   * @param mixed $value
   */
  public function set($identifier, $value) {
    $this->offsetSet($identifier, $value);
  }

  /**
   * Remove an existing session value if the session is active.
   *
   * @param mixed $identifier
   */
  public function offsetUnset($identifier): void {
    $key = $this->_compileKey($identifier);
    if (
      $this->_session->isActive() &&
      $this->_session->wrapper()->hasValue($key)
    ) {
      $this->_session->wrapper()->removeValue($key);
    }
    if (\array_key_exists($key, $this->_fallback)) {
      unset($this->_fallback[$key]);
    }
  }

  /**
   * Provide access to the used identifer string
   *
   * @param mixed $identifier
   *
   * @return string
   */
  public function getKey($identifier) {
    return $this->_compileKey($identifier);
  }

  /**
   * Compile session variable identifier data into an string.
   *
   * If the identifier data is an object the class of this object is used.
   *
   * If the identifier data is an array the elements are joined using underscores. For object
   * elements their classname will be used. Array elements will be serialized and hased with md5.
   * All other elements are casted to strings.
   *
   * All other identifier data is casted to a string.
   *
   * @param mixed $identifier
   *
   * @return string
   */
  private function _compileKey($identifier) {
    if (\is_array($identifier)) {
      $result = '';
      foreach ($identifier as $part) {
        if (\is_object($part)) {
          $result .= '_'.\get_class($part);
        } elseif (\is_array($part)) {
          $result .= '_'.\md5(\serialize($part));
        } else {
          $result .= '_'.((string)$part);
        }
      }
      return \substr($result, 1);
    }
    if (\is_object($identifier)) {
      return \get_class($identifier);
    }
    return (string)$identifier;
  }
}
