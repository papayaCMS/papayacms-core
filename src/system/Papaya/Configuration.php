<?php
/**
* A class for configurations. The actual configuration class needs to extend this class and
* define option names and default values in the internal $_options array.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Configuration
* @version $Id: Configuration.php 39408 2014-02-27 16:00:49Z weinert $
*/

/**
* A superclass for configurations. The actual configuration class needs to extend this class and
* define option names and default values in the internal $_options array.
*
* Option names are alayways normalized to uppercase with underscores. It is possible to use
* camel case property syntax.
*
* @package Papaya-Library
* @subpackage Configuration
*/
class PapayaConfiguration
  extends PapayaObject
  implements IteratorAggregate, ArrayAccess {

  /**
  * Internal options array
  *
  * @var array
  */
  protected $_options = array();

  /**
  * Storage object, used to load/save the options.
  *
  * @var PapayaConfigurationStorage
  */
  private $_storage = NULL;

  /**
  * An hash identifieing the options status.
  *
  * @var string|NULL
  */
  private $_hash = NULL;

  /**
  * Create object and defined the given options.
  *
  * @param array $options
  */
  public function __construct(array $options) {
    $this->defineOptions($options);
  }

  /**
   * Validate and define the options.
   *
   * @param array $options
   * @throws UnexpectedValueException
   */
  protected function defineOptions(array $options) {
    foreach ($options as $name => $default) {
      if (!is_scalar($default) && !is_null($default)) {
        $name = PapayaUtilStringIdentifier::toUnderscoreUpper($name);
        throw new UnexpectedValueException(
          sprintf('Default value for option "%s" is not a scalar.', $name)
        );
      } else {
        $this->_options[$name] = $default;
      }
    }
  }

  /**
  * compile and return and hash from the currently defined option values.
  *
  *
  */
  public function getHash() {
    if (is_null($this->_hash)) {
      $this->_hash = md5(serialize($this->_options));
    }
    return $this->_hash;
  }

  /**
  * Read option. First it will try to find a constant with the option name. If no constant is found
  * it will use the value from the options array.
  *
  * If a filter ist provided the value will be processed by it.
  *
  * If a default value is provided, the system will cast the result to the type of this value.
  *
  * @param string $name
  * @param string $default
  * @param PapayaFilter $filter
  * @return mixed
  */
  public function get($name, $default = NULL, PapayaFilter $filter = NULL) {
    $name = PapayaUtilStringIdentifier::toUnderscoreUpper($name);
    if (array_key_exists($name, $this->_options)) {
      return $this->filter($this->_options[$name], $default, $filter);
    } else {
      return $default;
    }
  }

  /**
  * Filter the option value, to validate and transform it before use.
  *
  * @param mixed $value
  * @param mixed $default
  * @param PapayaFilter $filter
  * @return mixed
  */
  protected function filter($value, $default = NULL, PapayaFilter $filter = NULL) {
    if (isset($filter)) {
      $value = $filter->filter($value);
    }
    if (isset($value)) {
      if (isset($default) && is_scalar($default)) {
        settype($value, gettype($default));
      }
      return $value;
    } else {
      return $default;
    }
  }

  /**
  * Get option is only here for compatiblity with the old base_options class. It
  * uses the get() method.
  *
  * @deprecated {@see PapayaConfiguration::get()}
  * @param string $name
  * @param mixed $default
   * @return mixed
   */
  public function getOption($name, $default = NULL) {
    return $this->get($name, $default);
  }

  /**
  * Set an option value - the name must exists in the $_options array.
  *
  * @param string $name
  * @param mixed $value
  */
  public function set($name, $value) {
    $name = PapayaUtilStringIdentifier::toUnderscoreUpper($name);
    if ($this->has($name) &&
        array_key_exists($name, $this->_options) &&
        ($this->_options[$name] !== $value)) {
      $this->_options[$name] = $this->filter($value, $this->_options[$name]);
      $this->_hash = NULL;
    }
  }

  /**
   * Check if an option value exists, the name is a key of the
   * $_options array.
   *
   * @param string $name
   * @return bool
   */
  public function has($name) {
    $name = PapayaUtilStringIdentifier::toUnderscoreUpper($name);
    return array_key_exists($name, $this->_options);
  }

  /**
   * Assign the values of an array or traverseable object to the current configuration object.
   *
   * @param array|Traversable $source
   * @throws InvalidArgumentException
   */
  public function assign($source) {
    PapayaUtilConstraints::assertArrayOrTraversable($source);
    foreach ($source as $name => $value) {
      $this->set($name, $value);
    }
  }

  /**
  * Load options using a storage object. This will throw an exception if no storage is assigned.
  */
  public function load(PapayaConfigurationStorage $storage = NULL) {
    if (isset($storage)) {
      $this->storage($storage);
    }
    if ($this->storage()->load()) {
      foreach ($this->storage() as $option => $value) {
        $this->set($option, $value);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Getter/Setter for the storage object
  *
  * @throws LogicException
  * @param PapayaConfigurationStorage $storage
  * @return PapayaConfigurationStorage
  */
  public function storage(PapayaConfigurationStorage $storage = NULL) {
    if (isset($storage)) {
      $this->_storage = $storage;
    } elseif (is_null($this->_storage)) {
      throw new LogicException('No storage assigned to configuration.');
    }
    return $this->_storage;
  }

  /**
  * Magic method, property syntax for options existance check
  *
  * @see self::has()
  * @param string $name
  * @return boolean
  */
  public function __isset($name) {
    return $this->has($name);
  }

  /**
  * Magic method, property syntax for options read
  *
  * @see self::get()
  * @param string $name
  * @return mixed
  */
  public function __get($name) {
    return $this->get($name);
  }

  /**
  * Magic method, property syntax for options write
  *
  * @see self::set()
  * @param string $name
  * @param mixed $value
  */
  public function __set($name, $value) {
    $this->set($name, $value);
  }

  /**
  * ArrayAccess interface: check if an option exists
  *
  * @see self::has()
  * @param string $name
  * @return boolean
  */
  public function offsetExists($name) {
    return $this->has($name);
  }

  /**
  * ArrayAccess interface: read an option
  *
  * @see self::get()
  * @param string $name
  * @return mixed
  */
  public function offsetGet($name) {
    return $this->get($name);
  }

  /**
  * ArrayAccess interface: write an option
  *
  * @see self::set()
  * @param string $name
  * @param mixed $value
  */
  public function offsetSet($name, $value) {
    $this->set($name, $value);
  }

  /**
   * ArrayAccess interface: remove an option, this action throws an exception.
   * Options can only be changed, not removed.
   *
   *
   * @param string $name
   * @throws LogicException
   */
  public function offsetUnset($name) {
    throw new LogicException(
      'LogicException: You can only read or write options, not remove them.'
    );
  }

  /**
  * IteratorAggregate Interface: return an iterator for the options.
  * This is used for storage handling.
  *
  * @return Iterator
  */
  public function getIterator() {
    return new PapayaConfigurationIterator(array_keys($this->_options), $this);
  }
}