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
namespace Papaya;

use Papaya\BaseObject\Interfaces\StringCastable;

/**
 * A superclass for configurations. The actual configuration class needs to extend this class and
 * define option names and default values in the internal $_options array.
 *
 * Option names are always normalized to uppercase with underscores. It is possible to use
 * camel case property syntax.
 *
 * @package Papaya-Library
 * @subpackage Configuration
 */
class Configuration
  implements \IteratorAggregate, \ArrayAccess {
  /**
   * Internal options array
   *
   * @var array
   */
  protected $_options = [];

  /**
   * Storage object, used to load/save the options.
   *
   * @var Configuration\Storage
   */
  private $_storage;

  /**
   * An hash identifying the options status.
   *
   * @var string|null
   */
  private $_hash;

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
   *
   * @throws \UnexpectedValueException
   */
  protected function defineOptions(array $options) {
    foreach ($options as $name => $default) {
      if (!\is_scalar($default) && NULL !== $default) {
        $name = Utility\Text\Identifier::toUnderscoreUpper($name);
        throw new \UnexpectedValueException(
          \sprintf('Default value for option "%s" is not a scalar.', $name)
        );
      }
      $this->_options[$name] = $default;
    }
  }

  /**
   * @param string $identifier
   * @param string $path
   * @return StringCastable|string
   */
  public function getPath(string $identifier, string $path = '') {
    return $path;
  }

  /**
   * compile and return and hash from the currently defined option values.
   */
  public function getHash() {
    if (NULL === $this->_hash) {
      $this->_hash = \md5(\serialize($this->_options));
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
   * @param Filter $filter
   *
   * @return mixed
   */
  public function get($name, $default = NULL, Filter $filter = NULL) {
    $name = Utility\Text\Identifier::toUnderscoreUpper($name);
    if (\array_key_exists($name, $this->_options)) {
      return $this->filter($this->_options[$name], $default, $filter);
    }
    return $default;
  }

  /**
   * Filter the option value, to validate and transform it before use.
   *
   * @param mixed $value
   * @param mixed $default
   * @param Filter $filter
   *
   * @return mixed
   */
  protected function filter($value, $default = NULL, Filter $filter = NULL) {
    if (NULL !== $filter) {
      $value = $filter->filter($value);
    }
    if (NULL !== $value) {
      if (NULL !== $default && \is_scalar($default)) {
        \settype($value, \gettype($default));
      }
      return $value;
    }
    return $default;
  }

  /**
   * Get option is only here for compatiblity with the old base_options class. It
   * uses the get() method.
   *
   * @deprecated {@see \Papaya\Configuration::get()}
   *
   * @param string $name
   * @param mixed $default
   *
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
    $name = Utility\Text\Identifier::toUnderscoreUpper($name);
    if (
      \array_key_exists($name, $this->_options) &&
      $this->_options[$name] !== $value &&
      $this->has($name)
    ) {
      $this->_options[$name] = $this->filter($value, $this->_options[$name]);
      $this->_hash = NULL;
    }
  }

  /**
   * Check if an option value exists, the name is a key of the
   * $_options array.
   *
   * @param string $name
   *
   * @return bool
   */
  public function has($name) {
    $name = Utility\Text\Identifier::toUnderscoreUpper($name);
    return \array_key_exists($name, $this->_options);
  }

  /**
   * Assign the values of an array or traverseable object to the current configuration object.
   *
   * @param array|\Traversable $source
   *
   * @throws \InvalidArgumentException
   */
  public function assign($source) {
    Utility\Constraints::assertArrayOrTraversable($source);
    foreach ($source as $name => $value) {
      $this->set($name, $value);
    }
  }

  /**
   * Load options using a storage object. This will throw an exception if no storage is assigned.
   *
   * @param \Papaya\Configuration\Storage|null $storage
   * @return bool
   */
  public function load(Configuration\Storage $storage = NULL) {
    if (NULL !== $storage) {
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
   * @throws \LogicException
   *
   * @param Configuration\Storage $storage
   *
   * @return Configuration\Storage
   */
  public function storage(Configuration\Storage $storage = NULL) {
    if (NULL !== $storage) {
      $this->_storage = $storage;
    } elseif (NULL === $this->_storage) {
      throw new \LogicException('No storage assigned to configuration.');
    }
    return $this->_storage;
  }

  /**
   * Magic method, property syntax for options existence check
   *
   * @see self::has()
   *
   * @param string $name
   *
   * @return bool
   */
  public function __isset($name) {
    return $this->has($name);
  }

  /**
   * Magic method, property syntax for options read
   *
   * @see self::get()
   *
   * @param string $name
   *
   * @return mixed
   */
  public function __get($name) {
    return $this->get($name);
  }

  /**
   * Magic method, property syntax for options write
   *
   * @see self::set()
   *
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
   *
   * @param string $name
   *
   * @return bool
   */
  public function offsetExists($name): bool {
    return $this->has($name);
  }

  /**
   * ArrayAccess interface: read an option
   *
   * @see self::get()
   *
   * @param string $name
   *
   * @return mixed
   */
  public function offsetGet($name) {
    return $this->get($name);
  }

  /**
   * ArrayAccess interface: write an option
   *
   * @see self::set()
   *
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
   *
   * @throws \LogicException
   */
  public function offsetUnset($name) {
    throw new \LogicException(
      'LogicException: You can only read or write options, not remove them.'
    );
  }

  /**
   * IteratorAggregate Interface: return an iterator for the options.
   * This is used for storage handling.
   *
   * @return \Iterator
   */
  public function getIterator(): \Traversable {
    return new Configuration\Iterator(\array_keys($this->_options), $this);
  }
}
