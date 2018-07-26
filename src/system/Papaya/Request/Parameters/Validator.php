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
* Papaya Request Parameter validation, allows to validate a group of parameters
* against an definition and access them in a filtered variant
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParametersValidator
  implements \ArrayAccess, \IteratorAggregate {

  /**
   * @var \PapayaRequestParameters
   */
  private $_parameters;

  /**
   * @var array
   */
  private $_definitions = array();

  /**
   * @var array
   */
  private $_values = array();

  /**
   * @var array
   */
  private $_errors = array();

  /**
   * @var null|boolean
   */
  private $_validationResult;

  /**
   * PapayaRequestParametersValidator constructor.
   *
   * @param array $definitions
   * @param array|\PapayaRequestParameters $parameters
   * @throws \UnexpectedValueException
   */
  public function __construct(array $definitions, $parameters) {
    $this->setDefinitions($definitions);
    if (is_array($parameters)) {
      $parameters = new \PapayaRequestParameters($parameters);
    }
    \PapayaUtilConstraints::assertInstanceOf(\PapayaRequestParameters::class, $parameters);
    $this->_parameters = $parameters;
  }

  /**
   * Validate and store the definitions, throw exceptions for invalid definitions
   *
   * @param array $definitions
   * @throws \UnexpectedValueException
   */
  private function setDefinitions(array $definitions) {
    foreach ($definitions as $definition) {
      $name = \PapayaUtilArray::get($definition, array('name', 0), NULL);
      \PapayaUtilConstraints::assertNotEmpty(
        $name, 'Empty parameter name not allowed.'
      );
      $default = \PapayaUtilArray::get($definition, array('default', 1), NULL);
      if ($default instanceof \Papaya\Filter) {
        $filter = $default;
        $default = NULL;
      } else {
        $filter = \PapayaUtilArray::get($definition, array('filter', 2), NULL);
      }
      if (NULL !== $filter) {
        \PapayaUtilConstraints::assertInstanceOf(\Papaya\Filter::class, $filter);
      }
      $this->_definitions[$name] = array(
        'default' => $default, 'filter' => $filter
      );
    }
  }

  /**
   * Validate and store the parameter values for later access, this is an lazy method
   * and will store the result, repeated calls to the method will always return the
   * stored result from the first call
   *
   * @return boolean
   */
  public function validate() {
    if (NULL === $this->_validationResult) {
      $this->_validationResult = TRUE;
      foreach ($this->_definitions as $name => $definition) {
        try {
          /** @var \Papaya\Filter $filter */
          $filter = isset($definition['filter']) ? $definition['filter'] : NULL;
          $value = $this->_parameters->get(
            $name, $definition['default'], $filter
          );
          $this->_values[$name] = $value;
          if (NULL !== $filter) {
            $filter->validate($value);
          }
        } catch (\PapayaFilterException $e) {
          $this->_errors[$name] = $e;
          $this->_validationResult = FALSE;
        }
      }
    }
    return $this->_validationResult;
  }

  /**
   * Trigger validation and return TRUE if a definition for the value exists.
   *
   * @param string $name
   * @return bool
   */
  public function offsetExists($name) {
    $this->validate();
    return array_key_exists($name, $this->_values);
  }

  /**
   * ArrayAccess alias for the simple get method
   *
   * @param string $name
   * @return mixed|null
   */
  public function offsetGet($name) {
    return $this->get($name);
  }

  /**
   * Trigger validation and return value, if no definition exists NULL is returned.
   *
   * @param string $name
   * @return mixed|null
   */
  public function get($name) {
    $this->validate();
    return isset($this->_values[$name]) ? $this->_values[$name] : NULL;
  }

  /**
   * @param mixed $name
   * @param mixed $value
   * @throws \InvalidArgumentException
   */
  public function offsetSet($name, $value) {
    $this->validate();
    if (isset($this->_definitions[$name])) {
      $definition = $this->_definitions[$name];
      if (isset($value, $definition['filter'])) {
        /** @noinspection PhpUndefinedMethodInspection */
        $value = $definition['filter']->filter($value);
      }
      if (NULL === $value) {
        $value = $definition['default'];
      } elseif (NULL !== $definition['default']) {
        if (is_array($definition['default'])) {
          $value = is_array($value) ? $value : $definition['default'];
        } elseif (is_object($definition['default'])) {
          $value = is_string($value) ? $value : (string)$definition['default'];
        } else {
          $type = gettype($definition['default']);
          settype($value, $type);
        }
      }
      if (isset($definition['filter'])) {
        /** @noinspection PhpUndefinedMethodInspection */
        $definition['filter']->validate($value);
      }
      $this->_values[$name] = $value;
    } else {
      throw new \InvalidArgumentException(
        sprintf(
          'Can not set undefined parameter name %s[%s]', get_class($this), $name
        )
      );
    }
  }

  /**
   * Reset a value to the provided default
   *
   * @param string $name
   * @throws \InvalidArgumentException
   */
  public function offsetUnset($name) {
    $this->validate();
    if (isset($this->_definitions[$name])) {
      $this->_values[$name] = $this->_definitions[$name]['default'];
    } else {
      throw new \InvalidArgumentException(
        sprintf(
          'Can not reset undefined parameter name %s[%s]', get_class($this), $name
        )
      );
    }
  }

  /**
   * Allow to access the values using iteration
   *
   * @return \Iterator
   */
  public function getIterator() {
    $this->validate();
    return new \ArrayIterator($this->_values);
  }

  /**
   * Public read access to the collected filter exceptions
   *
   * @return array
   */
  public function getErrors() {
    $this->validate();
    return $this->_errors;
  }

}
