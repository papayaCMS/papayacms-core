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
* Field factory option for profiles.
*
* This object store and provide data for the individual profiles needed to create the field.
* Not each profile needs to each all data and may use it in complete different ways.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string $name field name
* @property string $caption field caption
* @property string $hint field info/hint
* @property mixed $default field default value
* @property boolean $mandatory mandatory field status
* @property boolean $disabled disabled field status
* @property PapayaFilter $validation the validation filter, can be set from string|array as well
* @property mixed $parameters an individual parameters value
* @property PapayaObject $context used for callbacks or access to the application registry
*/
class PapayaUiDialogFieldFactoryOptions implements \ArrayAccess {

  /**
   * Definition of options and default values
   *
   * @var array
   */
  private
    /** @noinspection PropertyCanBeStaticInspection */
    $_definition = array(
      'name' => '',
      'caption' => '',
      'hint' => '',
      'default' => NULL,
      'mandatory' => FALSE,
      'disabled' => FALSE,
      'validation' => NULL,
      'parameters' => NULL,
      'context' => NULL
    );

  /**
   * Buffer for the current option values, if the value here is NULL or not set, use the
   * default value from the definition.
   *
   * @var array
   */
  private $_values = array();

  /**
   * @var PapayaFilterFactory
   */
  private $_filterFactory;


  /** @noinspection ArrayTypeOfParameterByDefaultValueInspection */
  /**
   * Create object and assign values from the provided Traversable or array.
   *
   * @param array|\Traversable $values
   * @throws \UnexpectedValueException
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function __construct(
    $values = array()
  ) {
    $this->assign($values);
  }

  /**
   * Assign values from a traversable as option values, unknown option names will be ignored
   *
   * @param array|\Traversable $values
   * @throws \UnexpectedValueException
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function assign($values) {
    \PapayaUtilConstraints::assertArrayOrTraversable($values);
    foreach ($values as $name => $value) {
      $this->set($name, $value, TRUE);
    }
  }

  /**
   * Magic Method, return true if the property exists and isset.
   *
   * @param string $name
   * @return bool
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function __isset($name) {
    return $this->exists($name, TRUE) && (NULL !== $this->get($name));
  }

  /**
   * Magic Method, return the option value
   *
   * @param string $name
   * @return NULL
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function __get($name) {
    return $this->get($name);
  }

  /**
   * Magic Method, set the option value
   *
   * @param string $name
   * @param mixed $value
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function __set($name, $value) {
    $this->set($name, $value);
  }

  /**
   * Magic Method, reset the option value to its default value from the definition
   *
   * @param string $name
   * @internal param mixed $value
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
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
   * @param mixed $offset
   * @return bool
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function offsetExists($offset) {
    return $this->exists($offset, TRUE);
  }

  /**
   * ArrayAccess interface, get the option value
   *
   * @see \ArrayAccess::offsetGet()
   * @param mixed $offset
   * @return mixed
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function offsetGet($offset) {
    return $this->__get($offset);
  }

  /**
   * ArrayAccess interface, set the option value
   *
   * @see \ArrayAccess::offsetSet()
   * @param mixed $offset
   * @param mixed $value
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function offsetSet($offset, $value) {
    $this->__set($offset, $value);
  }

  /**
   * ArrayAccess interface, reset the option value to its default value from the definition
   *
   * @see \ArrayAccess::offsetGet()
   * @param mixed $offset
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function offsetUnset($offset) {
    $this->__unset($offset);
  }

  /**
   * Validate if the option name is valid, if $silent is FALSE and exception is thrown.
   *
   * @param string $name
   * @param boolean $silent
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   * @return boolean
   */
  private function exists($name, $silent = FALSE) {
    if (array_key_exists($name, $this->_definition)) {
      return TRUE;
    }
    if ($silent) {
      return FALSE;
    }
    throw new \PapayaUiDialogFieldFactoryExceptionInvalidOption($name);
  }

  /**
   * Fetch a value from the buffer or return the default value
   *
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   * @param string $name
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
   * @throws \PapayaUiDialogFieldFactoryExceptionInvalidOption
   * @param string $name
   * @param mixed $value
   * @param boolean $silent
   */
  private function set($name, $value, $silent = FALSE) {
    if ($this->exists($name, $silent)) {
      $this->_values[$name] = $value;
    }
  }

  /**
   * The validation value is converted into an PapayaFilter object
   *
   * If it is empty the filter depends only on the mandatory value.
   *
   * If it is an array, the first element is considered a PapayaFilter class and all others
   * arguments for the constructor.
   *
   * If it is an existing class, it is considered a PapayaFilter class.
   *
   * If it does start with an non word character it is considered a PCRE.
   *
   * In all other cases it is considered a filter profile name.
   *
   * @param mixed $validation
   * @return null|\PapayaFilter|\PapayaFilterNotEmpty
   */
  private function getValidation($validation) {
    if ($validation instanceof \PapayaFilter) {
      return $validation;
    }
    if (empty($validation)) {
      return $this->mandatory ? new \PapayaFilterNotEmpty() : NULL;
    }
    $factory = $this->filterFactory();
    if (is_array($validation) || $validation instanceof \Closure) {
      $result = $factory->getFilter('generator', $this->mandatory, $validation);
    } elseif (class_exists($validation)) {
      $result = $factory->getFilter('generator', $this->mandatory, array($validation));
    } elseif (preg_match('(^[^a-zA-Z])', $validation)) {
      $result = $factory->getFilter('regex', $this->mandatory, $validation);
    } else {
      $result = $factory->getFilter($validation, $this->mandatory);
    }
    return $result;
  }

  /**
   * Getter/Setter for the validation filter factory
   *
   * @param \PapayaFilterFactory $factory
   * @return \PapayaFilterFactory
   */
  public function filterFactory(\PapayaFilterFactory $factory = NULL) {
    if (NULL !== $factory) {
      $this->_filterFactory = $factory;
    } elseif (NULL === $this->_filterFactory) {
      $this->_filterFactory = new \PapayaFilterFactory();
    }
    return $this->_filterFactory;
  }
}
