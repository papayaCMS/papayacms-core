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
* Papaya Request Parameters Handling
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParameters extends PapayaObjectParameters {

  /**
  * Get a subgroup of parameters
  * @param string $groupName
  * @return \PapayaRequestParameters
  */
  public function getGroup($groupName) {
    $result = new self();
    if (isset($this[$groupName])) {
      $value = $this[$groupName];
      if (is_array($value) || $value instanceof \Traversable) {
        $result->merge($this[$groupName]);
      }
    }
    return $result;
  }

  /**
   * Get the value, filter it, convert it to the type of the default value and
   * return the default value if no value is found.
   *
   * @see \PapayaObjectParameters::get()
   * @param array|int|string $offset
   * @param null $defaultValue
   * @param \PapayaFilter $filter
   * @return mixed
   */
  public function get($offset, $defaultValue = NULL, \PapayaFilter $filter = NULL) {
    return parent::get($this->_parseParameterName($offset), $defaultValue, $filter);
  }

  /**
   * Set a value. If $offsets is an array or Traversalbe each element in the array/Traversalbe
   * is set.
   *
   * @param int|string|array|\Traversable $offsets
   * @param mixed $value
   * @return $this
   */
  public function set($offsets, $value = NULL) {
    if (is_array($offsets) || $offsets instanceof \Traversable) {
      foreach ($offsets as $offset => $value) {
        $this[$offset] = $value;
      }
    } else {
      $this[$offsets] = $value;
    }
    return $this;
  }

  /**
   * Remove ohne or more keys. If $offsets is an array, each element is used as an separate
   * parameter name.
   *
   * @param int|string|array(string) $offsets
   * @return $this
   */
  public function remove($offsets) {
    if (!(is_array($offsets) || $offsets instanceof \Traversable)) {
      $offsets = array($offsets);
    }
    foreach ($offsets as $offset) {
      unset($this[$offset]);
    }
    return $this;
  }

  /**
   * Return the values as an array
   *
   * @return array
   */
  public function toArray() {
    return (array)$this;
  }

  /**
  * Parse request parameter name into parts
  *
  * @param string $name
  * @param string $groupSeparator
  * @return array|string
  */
  private function _parseParameterName($name, $groupSeparator = '') {
    $parts = new \PapayaRequestParametersName(str_replace('.', '_', $name), $groupSeparator);
    return $parts->getArray();
  }

  /**
  * Prepare parameters, make sure it is utf8 and strip slashes if needed
  *
  * @param string|array $parameter
  * @param boolean $stripSlashes
  * @param integer $recursion
  * @return array|string
  */
  public function prepareParameter($parameter, $stripSlashes = FALSE, $recursion = 42) {
    if (is_array($parameter) && $recursion > 0) {
      foreach ($parameter as $name => $value) {
        $parameter[$name] = $this->prepareParameter($value, $stripSlashes, $recursion - 1);
      }
      return $parameter;
    } elseif (is_bool($parameter)) {
      return $parameter;
    } else {
      if ($stripSlashes) {
        $parameter = stripslashes($parameter);
      }
      return \PapayaUtilStringUtf8::ensure($parameter);
    }
  }

  /**
   * Get encoded query string
   *
   * @param $groupSeparator
   * @return string
   */
  public function getQueryString($groupSeparator) {
    $query = new \PapayaRequestParametersQuery($groupSeparator);
    $query->values($this);
    return $query->getString();
  }

  /**
   * @param string $queryString
   * @return $this
   */
  public function setQueryString($queryString) {
    $query = new \PapayaRequestParametersQuery();
    $query->setString($queryString);
    $this->assign($query->values());
    return $this;
  }

  /**
   * Return the parameters as a flat array (name => value)
   *
   * @param string $groupSeparator
   * @return array
   */
  public function getList($groupSeparator = '[]') {
    $result = $this->flattenArray((array)$this, $groupSeparator);
    uksort($result, 'strnatcasecmp');
    return $result;
  }

  /**
  * Flatten the internal recursive array into a simple name => value list.
  *
  * @param array $parameters
  * @param string $groupSeparator
  * @param string $prefix
  * @param integer $maxRecursions
  * @return array
  */
  private function flattenArray($parameters, $groupSeparator, $prefix = '', $maxRecursions = 42) {
    $result = array();
    foreach ($parameters as $name => $value) {
      if (empty($prefix)) {
        $fullName = $name;
      } elseif ($groupSeparator == '[]' || empty($groupSeparator)) {
        $fullName = $prefix.'['.$name.']';
      } else {
        $fullName = $prefix.$groupSeparator.$name;
      }
      if (is_array($value)) {
        $result = \PapayaUtilArray::merge(
          $result, $this->flattenArray($value, $groupSeparator, $fullName, $maxRecursions - 1)
        );
      } else {
        $result[$fullName] = (string)$value;
      }
    }
    return $result;
  }

  /**
  * ArrayAccess Interface: set value
  *
  * @param string|integer $offset
  * @param mixed $value
  */
  public function offsetSet($offset, $value) {
    parent::offsetSet($this->_parseParameterName($offset), $value);
  }

  /**
   * ArrayAccess Interface: check if index exists
   *
   * @param string|integer $offset
   * @return bool
   */
  public function offsetExists($offset) {
    return parent::offsetExists($this->_parseParameterName($offset));
  }

  /**
  * ArrayAccess Interface: remove value
  *
  * @param string|integer $offset
  */
  public function offsetUnset($offset) {
    parent::offsetUnset($this->_parseParameterName($offset));
  }

  /**
   * ArrayAccess Interface: get value
   *
   * If the value is an array, it will return a new instance of itself containing the array.
   *
   * @param string|integer $offset
   * @internal param mixed $value
   * @return mixed|\PapayaRequestParameters
   */
  public function offsetGet($offset) {
    $result = parent::offsetGet($this->_parseParameterName($offset));
    if (is_array($result)) {
      return new self($result);
    } else {
      return $result;
    }
  }

  /**
  * Check if the object contains data at all.
  *
  * @return boolean
  */
  public function isEmpty() {
    return count($this) < 1;
  }
}
