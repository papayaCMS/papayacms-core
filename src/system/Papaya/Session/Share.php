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
* This is the generic papaya session share object which can be used in projects to share related,
* session-persistent data conveniently between different modules.
*
* Usage:
* 1) Create a new class inheriting from PapayaSessionShare
* 2) Overload property $_definitions with a list of properties transparently made
*    persistent in the session, e.g. $_definitions = array('myProperty' => TRUE);
* 3) create an instance of your class
* 4) read/write the properties of this object like $shareObject->myProperty
*
* Constraints:
* Do not try to store application (request) state variables in this object. It's intended for
* session values only and doesn't allow to set custom properties.
*
* @package Papaya-Library
* @subpackage Session
*/
abstract class PapayaSessionShare extends \PapayaObject {

  /**
  * The list of those properties that are stored in the session.
  *
  * @var array key: propertyName, value: TRUE, e.g array('myProperty' => TRUE)
  */
  protected $_definitions = array();

  /**
  * Internal variable for an session values object used for dependency injection
  *
  * @var PapayaSessionValues
  */
  private $_sessionValues = NULL;

  /**
  * If set to true, the object will normalize the session variable names.
  *
  * @var string
  */
  protected $_normalizeNames = TRUE;


  /**
  * Getter for session values object
  *
  * @return \PapayaSessionValues $values
  */
  public function getSessionValues() {
    if (isset($this->_sessionValues)) {
      return $this->_sessionValues;
    }
    return $this->papaya()->session->values();
  }

  /**
  * Setter for session values object, allows dependency injection if needed.
  *
  * @param \PapayaSessionValues $values
  */
  public function setSessionValues(\PapayaSessionValues $values) {
    $this->_sessionValues = $values;
  }

  /**
  * Checks if a session property is available.
  *
  * @param string $name
  * @return boolean
  */
  public function __isset($name) {
    $name = $this->preparePropertyName($name);
    $values = $this->getSessionValues();
    /** @noinspection PhpIllegalArrayKeyTypeInspection */
    return isset($values[array($this->getGroupName(), $name)]);
  }

  /**
  * Retrieves a property from the session
  *
  * @param string $name
  * @return mixed
  */
  public function __get($name) {
    $name = $this->preparePropertyName($name);
    $values = $this->getSessionValues();
    /** @noinspection PhpIllegalArrayKeyTypeInspection */
    return $values[array($this->getGroupName(), $name)];
  }

  /**
  * Sets a session property defined in the list of persistent properties.
  *
  * @param string $name
  * @param mixed $value
  */
  public function __set($name, $value) {
    $name = $this->preparePropertyName($name);
    $values = $this->getSessionValues();
    /** @noinspection PhpIllegalArrayKeyTypeInspection */
    $values[array($this->getGroupName(), $name)] = $value;
  }

  /**
   * Sets a session property defined in the list of persistent properties.
   *
   * @param string $name
   * @internal param mixed $value
   */
  public function __unset($name) {
    $name = $this->preparePropertyName($name);
    $values = $this->getSessionValues();
    /** @noinspection PhpIllegalArrayKeyTypeInspection */
    unset($values[array($this->getGroupName(), $name)]);
  }

  /**
   * Allow getter/setter methods for defined session properties
   *
   * @param string $functionName
   * @param array $arguments
   * @throws \LogicException
   * @return mixed
   */
  public function __call($functionName, $arguments) {
    $mode = substr($functionName, 0, 3);
    switch ($mode) {
    case 'get' :
      $propertyName = substr($functionName, 3);
      return $this->$propertyName;
    case 'set' :
      $propertyName = substr($functionName, 3);
      $this->$propertyName = $arguments[0];
      return $this->$propertyName;
    }
    throw new \LogicException(
      sprintf(
        'LogicException: Unknown method "%s::%s".',
        get_class($this),
        $functionName
      )
    );
  }

  /**
  * Validate and prepare the property name
  *
  * @throws \InvalidArgumentException
  * @param string $name
  * @return string
  */
  protected function preparePropertyName($name) {
    if ($this->_normalizeNames) {
      if (preg_match('(^[a-zA-Z][a-z\d]*([A-Z]+[a-z\d]*)+$)DS', $name)) {
        $camelCasePattern = '((?:[a-z][a-z\d]+)|(?:[A-Z][a-z\d]+)|(?:[A-Z]+(?![a-z\d])))S';
        if (preg_match_all($camelCasePattern, $name, $matches)) {
          $name = implode('_', $matches[0]);
        }
        $name = strToLower($name);
      }
    }
    if (isset($this->_definitions[$name])) {
      return $name;
    }
    throw new \InvalidArgumentException(
      sprintf(
        'InvalidArgumentException: Invalid session share property name "%s".',
        $name
      )
    );
  }

  /**
   * Allow child classes to override the group name for the session values. By default this
   * ist the current class name of the object.
   *
   * @return string
   */
  protected function getGroupName() {
    return get_class($this);
  }
}
