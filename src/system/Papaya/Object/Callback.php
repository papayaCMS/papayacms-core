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
* Encapsulate a php callback, to allow a default returnvalue and a context.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property mixed $defaultReturn
* @property object $context
* @property callable $callback
*/
class PapayaObjectCallback {

  /**
  * Default return value, returned by execute if no php callback is set.
  *
  * @var mixed
  */
  private $_defaultReturn = NULL;

  /**
  * The wrapped php callback
  *
  * @var Callback|Closure
  */
  private $_callback = NULL;

  /**
  * Context object, an instance of stdClass by default.
  *
  * @var object
  */
  private $_context = NULL;

  /**
   * @var bool
   */
  private $_addContext = TRUE;

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
  * @return mixed
  */
  public function execute() {
    if (isset($this->_callback)) {
      $arguments = func_get_args();
      if ($this->_addContext) {
        array_unshift($arguments, $this->_context);
      }
      return call_user_func_array($this->_callback, $arguments);
    } else {
      return $this->_defaultReturn;
    }
  }

  /**
  * Check status of $defaultReturn, $callback and $context properties.
  *
  * @param $name
  * @return boolean
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
      PapayaUtilConstraints::assertObject($value);
      $this->_context = $value;
      break;
    case 'defaultReturn' :
      $this->_defaultReturn = $value;
      break;
    case 'callback' :
      if (is_null($value) || is_callable($value)) {
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
    if ($name == 'context') {
      $this->_context = new \stdClass;
      return;
    }
    $property = $this->getPropertyName($name);
    $this->$property = NULL;
  }

  /**
  * Validate the property name and return the private object variable name.
  *
  * @throws UnexpectedValueException
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
      sprintf('Unknown property %s::$%s', __CLASS__, $name)
    );
  }
}
