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

namespace Papaya\BaseObject;
/**
 * The item class allows to define objects that have a set of properties, the properties are
 * accessible through property and array syntax and it is possbile to iterate over them.
 **
 *
 * @package Papaya-Library
 * @subpackage Objects
 */
class Item
  extends \Papaya\Application\BaseObject
  implements \ArrayAccess, \IteratorAggregate {

  /**
   * Internal value store
   *
   * @var array
   */
  private $_values = array();

  /**
   * Create object and define properties provided as an list.
   *
   * @param array(string) $properties
   */
  public function __construct(array $properties) {
    foreach ($properties as $name) {
      $this->_values[\PapayaUtilStringIdentifier::toUnderscoreLower($name)] = NULL;
    }
  }

  /**
   * Assign the data from another object (like an array)
   *
   * @param array|\Traversable $data
   * @throws \InvalidArgumentException
   */
  public function assign($data) {
    if (!(is_array($data) || $data instanceof \Traversable)) {
      throw new \InvalidArgumentException(
        sprintf(
          'Argument $data must be an array or instance of Traversable.'
        )
      );
    }
    foreach ($data as $name => $value) {
      $name = \PapayaUtilStringIdentifier::toUnderscoreLower($name);
      if (array_key_exists($name, $this->_values)) {
        $this->_values[$name] = $value;
      }
    }
  }

  /**
   * Empty object, reset all values to NULL
   */
  public function clear() {
    foreach ($this->_values as $name => $value) {
      $this->_values[$name] = NULL;
    }
  }

  /**
   * Get the values as an array
   *
   * @return array
   */
  public function toArray() {
    return $this->_values;
  }

  /**
   * Get an iterator for the defined values.
   *
   * @return \ArrayIterator
   */
  public function getIterator() {
    return new \ArrayIterator($this->toArray());
  }

  /**
   * Validate if the defined value is set.
   *
   * @param string $name
   * @return boolean
   */
  public function __isset($name) {
    return isset($this->_values[$this->_prepareName($name)]);
  }

  /**
   * Return the defined value
   *
   * @throws \OutOfBoundsException
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    return $this->_values[$this->_prepareName($name)];
  }

  /**
   * Change a defined value
   *
   * @throws \OutOfBoundsException
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value) {
    $this->_values[$this->_prepareName($name)] = $value;
  }

  /**
   * Set the deifned value to NULL.
   *
   * @throws \OutOfBoundsException
   * @param string $name
   */
  public function __unset($name) {
    $this->_values[$this->_prepareName($name)] = NULL;
  }

  /**
   * ArrayAccess: Validate if a index/property exists at all
   *
   * @param string $name
   * @return boolean
   */
  public function offsetExists($name) {
    $name = \PapayaUtilStringIdentifier::toUnderscoreLower($name);
    return array_key_exists($name, $this->_values);
  }

  /**
   * ArrayAccess: Return the defined vbalue
   *
   * @throws \OutOfBoundsException
   * @param string $name
   * @return mixed
   */
  public function offsetGet($name) {
    return $this->__get($name);
  }

  /**
   * ArrayAccess: Change the defined value.
   *
   * @throws \OutOfBoundsException
   * @param string $name
   * @param mixed $value
   */
  public function offsetSet($name, $value) {
    $this->__set($name, $value);
  }

  /**
   * ArrayAccess: Set the deifned value to NULL
   *
   *
   * @param string $name
   * @internal param mixed $value
   */
  public function offsetUnset($name) {
    $this->__unset($name);
  }

  /**
   * Covert property/Index identifier to lowercase with underscore. Throws an exception
   * if the property/index is not defined.
   *
   * @param string $name
   * @throws \OutOfBoundsException
   * @return string
   */
  private function _prepareName($name) {
    $name = \PapayaUtilStringIdentifier::toUnderscoreLower($name);
    if (!array_key_exists($name, $this->_values)) {
      throw new \OutOfBoundsException(
        sprintf(
          'Property/Index "%s" is not defined for item class "%s".',
          $name,
          get_class($this)
        )
      );
    }
    return $name;
  }
}
