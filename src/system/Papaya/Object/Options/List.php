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
* A options list if a list of name => value pairs. The names consists of letters and
* underscores (the first char can not be an underscore). Lowercase letters, will be converted
* to uppercase, so the names are case insensitive.
*
* If the option name is camel case (e.g. sampleOptionName) it will be splittet at the uppercase
* chars and joined again with underscores (e.g. SAMPLE_OPTION_NAME).
*
* The values have to be scalars, complex types are not allowed.
*
* @package Papaya-Library
* @subpackage Objects
*/
class PapayaObjectOptionsList
  implements ArrayAccess, Countable, IteratorAggregate {

  /**
  * Options storage
  * @var array
  */
  protected $_options = array();

  /**
  * Constrcutor: create object with optional default data
  */
  public function __construct(array $options = NULL) {
    if (is_array($options)) {
      foreach ($options as $name => $value) {
        $this->offsetSet($name, $value);
      }
    }
  }

  /**
  * Convert to uppercase letters and check name
  *
  * @throws \InvalidArgumentException
  * @param string $name
  * @return string
  */
  protected function _prepareName($name) {
    if (preg_match('(^[a-z][a-z\d]*([A-Z]+[a-z\d]*)+$)DS', $name)) {
      $camelCasePattern = '((?:[a-z][a-z\d]+)|(?:[A-Z][a-z\d]+)|(?:[A-Z]+(?![a-z\d])))S';
      if (preg_match_all($camelCasePattern, $name, $matches)) {
        $name = implode('_', $matches[0]);
      }
    }
    $name = strToUpper($name);
    if (preg_match('(^[A-Z]+[A-Z_]+$)DS', $name)) {
      return $name;
    }
    throw new \InvalidArgumentException(
      sprintf('Invalid option name "%s".', $name)
    );
  }

  /**
  * Read an option value
  *
  * @param $name
  * @return mixed
  */
  protected function _read($name) {
    return $this->_options[$name];
  }

  /**
  * Write an option value
  *
  * @param $name
  * @param $value
  */
  protected function _write($name, $value) {
    $this->_options[$name] = $value;
  }

  /**
   * Check if an option value exists
   *
   * @param $name
   * @return bool
   */
  protected function _exists($name) {
    return array_key_exists($name, $this->_options);
  }

  /**
   * ArrayAccess interface: return option
   *
   * @param string $name
   * @return mixed
   */
  public function offsetGet($name) {
    return $this->_read($this->_prepareName($name));
  }

  /**
  * ArrayAccess interface, set option value
  *
  * @throws \InvalidArgumentException
  * @param string $name
  * @param mixed $value
  * @return string
  */
  public function offsetSet($name, $value) {
    $name = $this->_prepareName($name);
    if (is_scalar($value)) {
      $this->_write($name, $value);
    } elseif (is_null($value)) {
      if (array_key_exists($name, $this->_options)) {
        unset($this->_options[$name]);
      }
    } else {
      throw new \InvalidArgumentException(
        sprintf(
          'Option value must be a skalar: "%s" given.', gettype($value)
        )
      );
    }
  }

  /**
  * ArrayAccess interface, check if option exists
  *
  * @param string $name
   * @return bool
   */
  public function offsetExists($name) {
    return $this->_exists($this->_prepareName($name));
  }

  /**
  * ArrayAccess interface: remove option
  * @param string $name
  */
  public function offsetUnset($name) {
    unset($this->_options[$this->_prepareName($name)]);
  }

  /**
  * Countable interface: return options count
  *
  * @return integer
  */
  public function count() {
    return count($this->_options);
  }

  /**
   * Magic Method: read access to options as properties
   *
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    return $this->offsetGet($name);
  }

  /**
   * Magic Method: write access to options as properties
   *
   * @param string $name
   * @param $value
   */
  public function __set($name, $value) {
    $this->offsetSet($name, $value);
  }

  /**
  * Magic Method: access to options as properties to check if they exists
  *
  * @param string $name
  * @return bool
  */
  public function __isset($name) {
    return $this->offsetExists($name);
  }

  /**
  * Magic Method: write to options as properties to remove them
  *
  * @param string $name
  */
  public function __unset($name) {
    $this->offsetUnset($name);
  }

  /**
  * Convert object to an array
  */
  public function toArray() {
    return $this->_options;
  }

  /**
  * IteratorAggrate Interface: return an iterator for the options in this object
  */
  public function getIterator() {
    return new \ArrayIterator($this->toArray());
  }

  /**
   * Assign a list of options
   */
  public function assign($values) {
    \PapayaUtilConstraints::assertArrayOrTraversable($values);
    foreach ($values as $name => $value) {
      $this->offsetSet($name, $value);
    }
  }

  /**
   * Set an option
   */
  public function set($name, $value) {
    $this->offsetSet($name, $value);
  }
}
