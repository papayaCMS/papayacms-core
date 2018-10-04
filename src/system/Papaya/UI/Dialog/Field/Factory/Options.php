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
namespace Papaya\UI\Dialog\Field\Factory;

use Papaya\Filter;
use Papaya\Utility;

/**
 * Field factory option for profiles.
 *
 * This object store and provide data for the individual profiles needed to create the field.
 * Not each profile needs to each all data and may use it in complete different ways.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string $name field name
 * @property string $caption field caption
 * @property string $hint field info/hint
 * @property mixed $default field default value
 * @property bool $mandatory mandatory field status
 * @property bool $disabled disabled field status
 * @property Filter $validation the validation filter, can be set from string|array as well
 * @property mixed $parameters an individual parameters value
 * @property \Papaya\Application\BaseObject $context used for callbacks or access to the application registry
 */
class Options implements \ArrayAccess {
  /**
   * Definition of options and default values
   *
   * @var array
   */
  private $_definition = [
    'name' => '',
    'caption' => '',
    'hint' => '',
    'default' => NULL,
    'mandatory' => FALSE,
    'disabled' => FALSE,
    'validation' => NULL,
    'parameters' => NULL,
    'context' => NULL
  ];

  /**
   * Buffer for the current option values, if the value here is NULL or not set, use the
   * default value from the definition.
   *
   * @var array
   */
  private $_values = [];

  /**
   * @var Filter\Factory
   */
  private $_filterFactory;

  /** @noinspection ArrayTypeOfParameterByDefaultValueInspection */

  /**
   * Create object and assign values from the provided Traversable or array.
   *
   * @param array|\Traversable $values
   *
   * @throws \UnexpectedValueException
   * @throws Exception\InvalidOption
   */
  public function __construct(
    $values = []
  ) {
    $this->assign($values);
  }

  /**
   * Assign values from a traversable as option values, unknown option names will be ignored
   *
   * @param array|\Traversable $values
   *
   * @throws \UnexpectedValueException
   * @throws Exception\InvalidOption
   */
  public function assign($values) {
    Utility\Constraints::assertArrayOrTraversable($values);
    foreach ($values as $name => $value) {
      $this->set($name, $value, TRUE);
    }
  }

  /**
   * Magic Method, return true if the property exists and isset.
   *
   * @param string $name
   *
   * @return bool
   *
   * @throws Exception\InvalidOption
   * @throws Filter\Factory\Exception\InvalidProfile
   */
  public function __isset($name) {
    return $this->exists($name, TRUE) && (NULL !== $this->get($name));
  }

  /**
   * Magic Method, return the option value
   *
   * @param string $name
   *
   * @return mixed
   * @throws Filter\Factory\Exception\InvalidProfile
   * @throws Exception\InvalidOption
   */
  public function __get($name) {
    return $this->get($name);
  }

  /**
   * Magic Method, set the option value
   *
   * @param string $name
   * @param mixed $value
   *
   * @throws Exception\InvalidOption
   */
  public function __set($name, $value) {
    $this->set($name, $value);
  }

  /**
   * Magic Method, reset the option value to its default value from the definition
   *
   * @param string $name
   *
   * @internal param mixed $value
   *
   * @throws Exception\InvalidOption
   */
  public function __unset($name) {
    if ($this->exists($name)) {
      $this->set($name, $this->_definition[$name]);
    }
  }

  /**
   * ArrayAccess interface, check if the option name is valid
   *
   * @see \ArrayAccess::offsetExists()
   *
   * @param mixed $offset
   *
   * @return bool
   *
   * @throws Exception\InvalidOption
   */
  public function offsetExists($offset) {
    return $this->exists($offset, TRUE);
  }

  /**
   * ArrayAccess interface, get the option value
   *
   * @see \ArrayAccess::offsetGet()
   *
   * @param mixed $offset
   *
   * @return mixed
   *
   * @throws Exception\InvalidOption
   * @throws \Papaya\Filter\Factory\Exception\InvalidProfile
   */
  public function offsetGet($offset) {
    return $this->__get($offset);
  }

  /**
   * ArrayAccess interface, set the option value
   *
   * @see \ArrayAccess::offsetSet()
   *
   * @param mixed $offset
   * @param mixed $value
   *
   * @throws Exception\InvalidOption
   */
  public function offsetSet($offset, $value) {
    $this->__set($offset, $value);
  }

  /**
   * ArrayAccess interface, reset the option value to its default value from the definition
   *
   * @see \ArrayAccess::offsetGet()
   *
   * @param mixed $offset
   *
   * @throws Exception\InvalidOption
   */
  public function offsetUnset($offset) {
    $this->__unset($offset);
  }

  /**
   * Validate if the option name is valid, if $silent is FALSE and exception is thrown.
   *
   * @param string $name
   * @param bool $silent
   *
   * @throws Exception\InvalidOption
   *
   * @return bool
   */
  private function exists($name, $silent = FALSE) {
    if (\array_key_exists($name, $this->_definition)) {
      return TRUE;
    }
    if ($silent) {
      return FALSE;
    }
    throw new Exception\InvalidOption($name);
  }

  /**
   * Fetch a value from the buffer or return the default value
   *
   * @throws Exception\InvalidOption
   * @throws Filter\Factory\Exception\InvalidProfile
   *
   * @param string $name
   *
   * @return mixed
   */
  private function get($name) {
    $this->exists($name, FALSE);
    $result = isset($this->_values[$name]) ? $this->_values[$name] : $this->_definition[$name];
    if ('validation' === $name) {
      return $this->getValidation($result);
    }
    return $result;
  }

  /**
   * Set the option value, if silent is set to false invalid option names will trigger an exception.
   *
   * @throws Exception\InvalidOption
   *
   * @param string $name
   * @param mixed $value
   * @param bool $silent
   */
  private function set($name, $value, $silent = FALSE) {
    if ($this->exists($name, $silent)) {
      $this->_values[$name] = $value;
    }
  }

  /**
   * The validation value is converted into an \Papaya\Filter object
   *
   * If it is empty the filter depends only on the mandatory value.
   *
   * If it is an array, the first element is considered a \Papaya\Filter class and all others
   * arguments for the constructor.
   *
   * If it is an existing class, it is considered a \Papaya\Filter class.
   *
   * If it does start with an non word character it is considered a PCRE.
   *
   * In all other cases it is considered a filter profile name.
   *
   * @param mixed $validation
   *
   * @return null|Filter|Filter\NotEmpty
   *
   * @throws Filter\Factory\Exception\InvalidProfile
   */
  private function getValidation($validation) {
    if ($validation instanceof Filter) {
      return $validation;
    }
    if (empty($validation)) {
      return $this->mandatory ? new Filter\NotEmpty() : NULL;
    }
    $factory = $this->filterFactory();
    if (\is_array($validation) || $validation instanceof \Closure) {
      $result = $factory->getFilter('generator', $this->mandatory, $validation);
    } elseif (\class_exists($validation)) {
      $result = $factory->getFilter('generator', $this->mandatory, [$validation]);
    } elseif (\preg_match('(^[^a-zA-Z])', $validation)) {
      $result = $factory->getFilter('regex', $this->mandatory, $validation);
    } else {
      $result = $factory->getFilter($validation, $this->mandatory);
    }
    return $result;
  }

  /**
   * Getter/Setter for the validation filter factory
   *
   * @param Filter\Factory $factory
   *
   * @return Filter\Factory
   */
  public function filterFactory(Filter\Factory $factory = NULL) {
    if (NULL !== $factory) {
      $this->_filterFactory = $factory;
    } elseif (NULL === $this->_filterFactory) {
      $this->_filterFactory = new Filter\Factory();
    }
    return $this->_filterFactory;
  }
}
