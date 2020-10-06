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
namespace Papaya\Request;

use Papaya\BaseObject;
use Papaya\Filter;
use Papaya\Request\Parameters\GroupSeparator;

/**
 * Papaya Request Parameters Handling
 *
 * @package Papaya-Library
 * @subpackage Request
 */
class Parameters extends BaseObject\Parameters {
  public static function createFromString($queryString) {
    $queryParameters = new Parameters\QueryString();
    $queryParameters->setString($queryString);
    $parameters = new self();
    $parameters->assign($queryParameters->values());
    return $parameters;
  }

  /**
   * Get a subgroup of parameters
   *
   * @param string $groupName
   *
   * @return self
   */
  public function getGroup($groupName) {
    $result = new self();
    if (isset($this[$groupName])) {
      $value = $this[$groupName];
      if (\is_array($value) || $value instanceof \Traversable) {
        $result->merge($this[$groupName]);
      }
    }
    return $result;
  }

  /**
   * Get the value, filter it, convert it to the type of the default value and
   * return the default value if no value is found.
   *
   * @see \Papaya\BaseObject\Parameters::get()
   *
   * @param array|int|string $name
   * @param null $defaultValue
   * @param Filter $filter
   *
   * @return mixed
   */
  public function get($name, $defaultValue = NULL, Filter $filter = NULL) {
    return parent::get($this->_parseParameterName($name), $defaultValue, $filter);
  }

  /**
   * Set a value. If $offsets is an array or Traversable each element in the array/Traversalbe
   * is set.
   *
   * @param int|string|array|\Traversable $nameOrValues
   * @param mixed $value
   *
   * @return $this
   */
  public function set($nameOrValues, $value = NULL) {
    if (\is_array($nameOrValues) || $nameOrValues instanceof \Traversable) {
      foreach ($nameOrValues as $offset => $element) {
        $this[$offset] = $element;
      }
    } else {
      $this[$nameOrValues] = $value;
    }
    return $this;
  }

  /**
   * Remove one or more keys. If $offsets is an array, each element is used as an separate
   * parameter name.
   *
   * @param int|string|array(string) $offsets
   *
   * @return $this
   */
  public function remove($offsets) {
    if (!(\is_array($offsets) || $offsets instanceof \Traversable)) {
      $offsets = [$offsets];
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
   *
   * @return array|string
   */
  private function _parseParameterName($name, $groupSeparator = '') {
    $parts = new Parameters\Name(\str_replace('.', '_', $name), $groupSeparator);
    return $parts->getArray();
  }

  /**
   * Prepare parameters, make sure it is utf8 and strip slashes if needed
   *
   * @param string|array|bool $parameter
   * @param bool $stripSlashes
   * @param int $recursion
   *
   * @return array|string
   */
  public function prepareParameter($parameter, $stripSlashes = FALSE, $recursion = 42) {
    if (\is_array($parameter) && $recursion > 0) {
      foreach ($parameter as $name => $value) {
        $parameter[$name] = $this->prepareParameter($value, $stripSlashes, $recursion - 1);
      }
      return $parameter;
    }
    if (\is_bool($parameter)) {
      return $parameter;
    }
    if ($stripSlashes) {
      $parameter = \stripslashes($parameter);
    }
    return \Papaya\Utility\Text\UTF8::ensure($parameter);
  }

  /**
   * Get encoded query string
   *
   * @param $groupSeparator
   *
   * @return string
   */
  public function getQueryString($groupSeparator) {
    $query = new Parameters\QueryString($groupSeparator);
    $query->values($this);
    return $query->getString();
  }

  /**
   * @param string $queryString
   *
   * @return $this
   */
  public function setQueryString($queryString) {
    $query = new Parameters\QueryString();
    $query->setString($queryString);
    $this->assign($query->values());
    return $this;
  }

  /**
   * Return the parameters as a flat array (name => value)
   *
   * @param string $groupSeparator
   *
   * @return array
   */
  public function getList($groupSeparator = GroupSeparator::ARRAY_SYNTAX) {
    $result = $this->flattenArray((array)$this, $groupSeparator);
    \uksort($result, 'strnatcasecmp');
    return $result;
  }

  /**
   * Flatten the internal recursive array into a simple name => value list.
   *
   * @param array $parameters
   * @param string $groupSeparator
   * @param string $prefix
   * @param int $maxRecursions
   *
   * @return array
   */
  private function flattenArray($parameters, $groupSeparator, $prefix = '', $maxRecursions = 42) {
    $result = [];
    foreach ($parameters as $name => $value) {
      if (empty($prefix)) {
        $fullName = $name;
      } elseif (GroupSeparator::ARRAY_SYNTAX === $groupSeparator || empty($groupSeparator)) {
        $fullName = $prefix.'['.$name.']';
      } else {
        $fullName = $prefix.$groupSeparator.$name;
      }
      if (\is_array($value)) {
        $result = \Papaya\Utility\Arrays::merge(
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
   * @param string|int $offset
   * @param mixed $value
   */
  public function offsetSet($offset, $value) {
    parent::offsetSet($this->_parseParameterName($offset), $value);
  }

  /**
   * ArrayAccess Interface: check if index exists
   *
   * @param string|int $offset
   *
   * @return bool
   */
  public function offsetExists($offset) {
    return parent::offsetExists($this->_parseParameterName($offset));
  }

  /**
   * ArrayAccess Interface: remove value
   *
   * @param string|int $offset
   */
  public function offsetUnset($offset) {
    parent::offsetUnset($this->_parseParameterName($offset));
  }

  /**
   * ArrayAccess Interface: get value
   *
   * If the value is an array, it will return a new instance of itself containing the array.
   *
   * @param string|int $offset
   *
   * @internal param mixed $value
   *
   * @return mixed|self
   */
  public function offsetGet($offset) {
    $result = parent::offsetGet($this->_parseParameterName($offset));
    if (\is_array($result)) {
      return new self($result);
    }
    return $result;
  }

  /**
   * Check if the object contains data at all.
   *
   * @return bool
   */
  public function isEmpty() {
    return \count($this) < 1;
  }

  /**
   * @param array $names
   * @return self
   */
  public function getFiltered(array $names) {
    $filtered = new self();
    foreach ($names as $name) {
      if ($this->has($name)) {
        $filtered->set($name, $this->get($name));
      }
    }
    return $filtered;
  }
}
