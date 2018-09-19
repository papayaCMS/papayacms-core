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

use \Papaya\BaseObject\Interfaces\Properties;

/**
 * Encapsulate a php callback, to allow a default return value and a context.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property mixed $defaultReturn
 * @property object $context
 * @property callable $callback
 */
class Callback implements Properties {
  /**
   * Default return value, returned by execute if no php callback is set.
   *
   * @var mixed
   */
  private $_defaultReturn;

  /**
   * The wrapped php callback
   *
   * @var callable
   */
  private $_callback;

  /**
   * Context object, an instance of stdClass by default.
   *
   * @var object
   */
  private $_context;

  /**
   * @var bool
   */
  private $_addContext;

  /**
   * Create callback object, set default return value and create context object.
   *
   * @param mixed $defaultReturn
   * @param bool $addContext
   */
  public function __construct($defaultReturn = NULL, $addContext = TRUE) {
    $this->_defaultReturn = $defaultReturn;
    $this->_context = new \stdClass();
    $this->_addContext = (bool)$addContext;
  }

  /**
   * Execute the callback if defined, just return the default return value otherwise.
   *
   * @param array $arguments
   * @return mixed
   */
  public function execute(...$arguments) {
    if (NULL !== $this->_callback) {
      if ($this->_addContext) {
        \array_unshift($arguments, $this->_context);
      }
      $callback = $this->_callback;
      return $callback(...$arguments);
    }
    return $this->_defaultReturn;
  }

  /**
   * Check status of $defaultReturn, $callback and $context properties.
   *
   * @param $name
   * @return bool
   */
  public function __isset($name) {
    $property = $this->getPropertyName($name);
    return isset($this->$property);
  }

  /**
   * Get value of $defaultReturn, $callback and $context properties.
   *
   * @param $name
   * @return mixed
   */
  public function __get($name) {
    $property = $this->getPropertyName($name);
    return $this->$property;
  }

  /**
   * Change value of $defaultReturn, $callback and $context properties.
   *
   * @param $name
   * @param mixed
   */
  public function __set($name, $value) {
    $this->getPropertyName($name);
    switch ($name) {
      case 'context' :
        \Papaya\Utility\Constraints::assertObject($value);
        $this->_context = $value;
      break;
      case 'defaultReturn' :
        $this->_defaultReturn = $value;
      break;
      case 'callback' :
        if (NULL === $value || \is_callable($value)) {
          $this->_callback = $value;
        }
      break;
    }
  }

  /**
   * Set $defaultReturn, $callback or $context property to NULL.
   *
   * @param $name
   */
  public function __unset($name) {
    if ('context' === $name) {
      $this->_context = new \stdClass();
      return;
    }
    $property = $this->getPropertyName($name);
    $this->$property = NULL;
  }

  /**
   * Validate the property name and return the private object variable name.
   *
   * @throws \UnexpectedValueException
   * @param string $name
   * @return string
   */
  private function getPropertyName($name) {
    switch ($name) {
      case 'defaultReturn' :
      case 'callback' :
      case 'context' :
        return '_'.$name;
    }
    throw new \UnexpectedValueException(
      \sprintf('Unknown property %s::$%s', __CLASS__, $name)
    );
  }
}
