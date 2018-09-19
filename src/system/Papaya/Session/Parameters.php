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
namespace Papaya\Session;

/**
 * Session persistance for request parameters
 *
 * @package Papaya-Library
 * @subpackage Session
 */
class Parameters extends \Papaya\Application\BaseObject {
  /**
   * A group identifer for the session data, if an object is provided, it's classname will be used.
   *
   * @var object|string
   */
  private $_group;

  /**
   * @var \Papaya\Request\Parameters
   */
  private $_parameters;

  /**
   * Session values
   *
   * @var \Papaya\Session\Values
   */
  private $_values;

  /**
   * Initialize object, set a group and the parameters object. The group can be an string or
   * an object. If it is an object, the classname is used.
   *
   * @param object|string $group
   * @param \Papaya\Request\Parameters $parameters
   */
  public function __construct($group, \Papaya\Request\Parameters $parameters) {
    $this->_group = $group;
    $this->parameters($parameters);
  }

  /**
   * Load a given parameter. The parameter will be read from the session. If it exists in
   * $parameters it will be read from where and compared to the session value. If they are
   * different the session value will be changed. If dependencies are provided these parameters
   * will be removed in this case.
   *
   * If only the session value exists the $parameters object will be changed to restore the session
   * value into the parameters object.
   *
   * If a value was found in the $parameters object or the session it will be returned.
   *
   * @param string|array $name
   * @param mixed $default
   * @param \Papaya\Filter|null $filter
   * @param array|string $dependencies
   *
   * @return mixed
   */
  public function load($name, $default = NULL, $filter = NULL, $dependencies = NULL) {
    $sessionName = $this->getIdentifier($name);
    $sessionValue = $this->values()->get($sessionName);
    if ($this->parameters()->has($name)) {
      $value = $this->parameters()->get($name, $default, $filter);
      $this->values()->set($sessionName, $value);
      if ($sessionValue != $value && !empty($dependencies)) {
        foreach (\Papaya\Utility\Arrays::ensure($dependencies) as $dependency) {
          $this->remove($dependency);
        }
      }
      return $value;
    } elseif (isset($sessionValue)) {
      $this->parameters()->set($name, $sessionValue);
      return $this->parameters()->get($name, $default, $filter);
    } else {
      return $default;
    }
  }

  /**
   * Set a parameter and its session value
   *
   * @param string|array $name
   * @param mixed $value
   */
  public function store($name, $value) {
    $this->parameters()->set($name, $value);
    $this->values()->set($this->getIdentifier($name), $value);
  }

  /**
   * Remove a parameter and its session value
   *
   * @param string|array $name
   */
  public function remove($name) {
    $this->parameters()->remove($name);
    $this->values()->set($this->getIdentifier($name), NULL);
  }

  /**
   * Getter/Setter for the associated request parameters
   *
   * @param \Papaya\Request\Parameters $parameters
   *
   * @return \Papaya\Request\Parameters
   */
  public function parameters(\Papaya\Request\Parameters $parameters = NULL) {
    if (isset($parameters)) {
      $this->_parameters = $parameters;
    }
    return $this->_parameters;
  }

  /**
   * Getter/Setter for the associated session values
   *
   * @param \Papaya\Session\Values $values
   *
   * @return \Papaya\Session\Values
   */
  public function values(\Papaya\Session\Values $values = NULL) {
    if (isset($values)) {
      $this->_values = $values;
    } elseif (\is_null($this->_values)) {
      $this->_values = $this->papaya()->session->values;
    }
    return $this->_values;
  }

  /**
   * Get a clean session identifer
   *
   * @param string|array $parameterName
   *
   * @return array string
   */
  private function getIdentifier($parameterName) {
    $name = new \Papaya\Request\Parameters\Name($parameterName);
    return [$this->_group, $name->getString()];
  }
}
