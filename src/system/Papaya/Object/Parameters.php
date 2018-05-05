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
* Extends the ArrayObject to allow lists as keys and a get with filtering and casting
**
* @package Papaya-Library
* @subpackage Objects
*/
class PapayaObjectParameters
  extends \ArrayObject {

  /**
   * Check if the value exists
   *
   * @param string|array $offset
   * @return boolean
   */
  public function has($offset) {
    return $this->offsetExists($offset);
  }

  /**
   * Get the value defined by the given offset. If the value does not exist or is NULL,
   * return the default value.
   *
   * If a filter is provided use it to filter the value before returing it.
   *
   * If the default value has a type, cast the value to this type. If the default value is an
   * object the result will be a string. Make sure the provided object implements __toString().
   *
   * @param mixed $offset
   * @param mixed $defaultValue
   * @param \PapayaFilter $filter
   * @return mixed
   */
  public function get($offset, $defaultValue = NULL, \PapayaFilter $filter = NULL) {
    $value = self::offsetGet($offset);
    if (isset($value) && isset($filter) && $filter instanceof \PapayaFilter) {
      $value = $filter->filter($value);
    }
    if (is_null($value)) {
      return $defaultValue;
    } elseif (is_null($defaultValue)) {
      return $value;
    } elseif (is_array($defaultValue)) {
      return is_array($value) ? $value : $defaultValue;
    } elseif (is_object($defaultValue) && method_exists($defaultValue, '__toString')) {
      return is_string($value) ? $value : (string)$defaultValue;
    } elseif (is_scalar($defaultValue)) {
      $type = gettype($defaultValue);
      settype($value, $type);
      return $value;
    } else {
      return $defaultValue;
    }
  }

  /**
   * Empty the internal array.
   */
  public function clear() {
    parent::exchangeArray(array());
  }

  /**
   * Merge the given array or Traversable into the internal array.
   *
   * @param array|\Traversable $value
   */
  public function merge($value) {
    \PapayaUtilConstraints::assertArrayOrTraversable($value);
    parent::exchangeArray(\PapayaUtilArray::merge($this, $value));
  }

  /**
   * Set each value from an array or traversable
   *
   * @param array|\Traversable $values
   * @internal param array|\Traversable $value
   */
  public function assign($values) {
    \PapayaUtilConstraints::assertArrayOrTraversable($values);
    foreach ($values as $key => $value) {
      $this[$key] = $value;
    }
  }

  /**
   * ArrayAccess interface, check if the offset exists
   *
   * @see \ArrayObject::offsetExists()
   * @param mixed $offset
   * @return boolean
   */
  public function offsetExists($offset) {
    if (empty($offset) && $offset !== 0) {
      return FALSE;
    }
    if (is_array($offset) && count($offset) > 0) {
      $first = array_shift($offset);
      if (!parent::offsetExists($first)) {
        return FALSE;
      }
      $data = parent::offsetGet($first);
      foreach ($offset as $key) {
        if (is_array($data) && array_key_exists($key, $data)) {
          $data =& $data[$key];
        } else {
          return FALSE;
        }
      }
      return TRUE;
    } else {
      return parent::offsetExists($offset);
    }
  }

  /**
   * ArrayAccess interface, return the value spocified by the key, return NULL if the value
   * does not exist.
   *
   * @see \ArrayObject::offsetGet()
   * @param mixed $offset
   * @return mixed
   */
  public function offsetGet($offset) {
    if (is_array($offset) && count($offset) > 0) {
      $first = array_shift($offset);
      $data = parent::offsetExists($first) ? parent::offsetGet($first) : NULL;
      foreach ($offset as $key) {
        if (is_array($data) && array_key_exists($key, $data)) {
          $data =& $data[$key];
        } else {
          $data = NULL;
          break;
        }
      }
      return $data;
    } elseif (!is_array($offset) && parent::offsetExists($offset)) {
      return parent::offsetGet($offset);
    } else {
      return NULL;
    }
  }

  /**
   * ArrayAccess interface, change a value
   *
   * @see \ArrayObject::offsetSet()
   * @param mixed $offset
   * @param mixed $value
   */
  public function offsetSet($offset, $value) {
    if ($value instanceof \Traversable) {
      $value = iterator_to_array($value);
    }
    if (is_array($offset) && count($offset) > 1) {
      $first = array_shift($offset);
      $last = array_pop($offset);
      $top = parent::offsetExists($first) ? parent::offsetGet($first) : array();
      $current =& $top;
      foreach ($offset as $key) {
        if (empty($key) && $key !== 0) {
          $current[] = array();
          end($current);
          $key = key($current);
        } elseif (!(isset($current[$key]) && is_array($current[$key]))) {
          $current[$key] = array();
        }
        $current =& $current[$key];
      }
      if (empty($last) && $last !== 0) {
        if (!is_array($current)) {
          $current = array();
        }
        $current[] = $value;
      } elseif (is_array($current)) {
        $current[$last] = $value;
      } else {
        $current = array($last => $value);
      }
      parent::offsetSet(empty($first) && $first !== 0 ? NULL : $first, $top);
    } elseif (is_array($offset)) {
      parent::offsetSet($offset[0], $value);
    } else {
      parent::offsetSet($offset, $value);
    }
  }

  /**
   * ArrayAccess interface, delete a value from the internal array
   *
   * @see \ArrayObject::offsetUnset()
   * @param mixed $offset
   */
  public function offsetUnset($offset) {
    if (empty($offset) && $offset !== 0) {
      return;
    }
    if (is_array($offset) && count($offset) > 1) {
      $first = array_shift($offset);
      $last = array_pop($offset);
      if (!parent::offsetExists($first)) {
        return;
      }
      $top = parent::offsetGet($first);
      $current =& $top;
      foreach ($offset as $key) {
        if (isset($current[$key]) && is_array($current[$key])) {
          $current =& $current[$key];
        } else {
          return;
        }
      }
      if (is_array($current) && isset($current[$last])) {
        unset($current[$last]);
        parent::offsetSet($first, $top);
      }
    } elseif (is_array($offset)) {
      $offset = $offset[0];
    }
    if (is_scalar($offset) && parent::offsetExists($offset)) {
      parent::offsetUnset($offset);
    }
  }

  /**
  * Compile a checksum hash for the parameter data
  *
  * @return string
  */
  public function getChecksum() {
    $data = (array)$this;
    ksort($data);
    \PapayaUtilArray::normalize($data);
    return md5(serialize($data));
  }
}
