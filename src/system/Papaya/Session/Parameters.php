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

use Papaya\Application;
use Papaya\Request;
use Papaya\Utility;

/**
 * Session persistence for request parameters
 *
 * @package Papaya-Library
 * @subpackage Session
 */
class Parameters implements Application\Access {
  use Application\Access\Aggregation;

  /**
   * A group identifier for the session data, if an object is provided, it's classname will be used.
   *
   * @var object|string
   */
  private $_group;

  /**
   * @var Request\Parameters
   */
  private $_parameters;

  /**
   * Session values
   *
   * @var Values
   */
  private $_values;

  /**
   * Initialize object, set a group and the parameters object. The group can be an string or
   * an object. If it is an object, the class name is used.
   *
   * @param object|string $group
   * @param Request\Parameters $parameters
   */
  public function __construct($group, Request\Parameters $parameters) {
    $this->_group = $group;
    $this->parameters($parameters);
  }

  public function getGroup() {
    return $this->_group;
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
      /** @noinspection TypeUnsafeComparisonInspection */
      if ($sessionValue != $value && !empty($dependencies)) {
        foreach (Utility\Arrays::ensure($dependencies) as $dependency) {
          $this->remove($dependency);
        }
      }
      return $value;
    }
    if (NULL !== $sessionValue) {
      $this->parameters()->set($name, $sessionValue);
      return $this->parameters()->get($name, $default, $filter);
    }
    return $default;
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
   * @param Request\Parameters $parameters
   *
   * @return Request\Parameters
   */
  public function parameters(Request\Parameters $parameters = NULL) {
    if (NULL !== $parameters) {
      $this->_parameters = $parameters;
    }
    return $this->_parameters;
  }

  /**
   * Getter/Setter for the associated session values
   *
   * @param Values $values
   *
   * @return Values
   */
  public function values(Values $values = NULL) {
    if (NULL !== $values) {
      $this->_values = $values;
    } elseif (NULL === $this->_values) {
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
    $name = new Request\Parameters\Name($parameterName);
    return [$this->_group, $name->getString()];
  }
}
