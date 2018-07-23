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

/**
* Papaya Application - object registry with profiles
* @package Papaya-Library
* @subpackage Application
*/
class Application implements \ArrayAccess {

  /**
  * Duplicate profiles trigger an error
  * @var int
  */
  const DUPLICATE_ERROR = 0;

  /**
  * Ignore duplicate profiles
  * @var int
  */
  const DUPLICATE_IGNORE = 1;

  /**
  * Overwrite duplicate profiles
  * @var int
  */
  const DUPLICATE_OVERWRITE = 2;

  /**
  * Class variable for singleton instance
  * @var PapayaApplication
  */
  private static $instance = NULL;


  /**
  * Profile objects
  * @var array
  */
  private $_profiles = array();

  /**
  * Objects
  * @var array(object)
  */
  private  $_objects = array();

  /**
  * Create a new instance of this class or return existing one (singleton)
  *
  * @param boolean $reset
  * @return \PapayaApplication Instance of Application Object
  */
  public static function getInstance($reset = FALSE) {
    if ($reset || is_null(self::$instance)) {
      self::$instance = new \PapayaApplication();
    }
    return self::$instance;
  }

  /**
  * Register a collection of profiles
  *
  * @param \Papaya\Application\Profiles $profiles
  * @param integer $duplicationMode
  * @return void
  */
  public function registerProfiles(
    Application\Profiles $profiles, $duplicationMode = self::DUPLICATE_ERROR
  ) {
    foreach ($profiles->getProfiles($this) as $identifier => $profile) {
      $this->registerProfile($identifier, $profile, $duplicationMode);
    }
  }

  /**
   * Register an object profile
   *
   * @param string $identifier
   * @param \Papaya\Application\Profile|callable $profile
   * @param integer $duplicationMode
   * @throws \InvalidArgumentException
   */
  public function registerProfile(
    $identifier, $profile, $duplicationMode = self::DUPLICATE_ERROR
  ) {
    if (!($profile instanceof Application\Profile || is_callable($profile))) {
      throw new \InvalidArgumentException(
        sprintf(
          'Invalid profile %s is %s.',
          $identifier,
          is_object($profile) ? get_class($profile) : gettype($profile)
        )
      );
    }
    $index = strtolower($identifier);
    if (isset($this->_profiles[$index])) {
      switch ($duplicationMode) {
      case self::DUPLICATE_OVERWRITE :
        break;
      case self::DUPLICATE_ERROR :
        throw new \InvalidArgumentException(
          sprintf(
            'Duplicate application object profile: "%s"',
            $identifier
          )
        );
      case self::DUPLICATE_IGNORE :
        return;
      }
    }
    if (!empty($index)) {
      $this->_profiles[$index] = $profile;
    }
  }

  /**
   * Get object instance, if the object does not exist and no profile is found, $className is
   * used to create a new object, if provided.
   *
   * @param string $identifier
   * @throws \InvalidArgumentException
   * @return object
   */
  public function getObject($identifier) {
    $index = strtolower($identifier);
    if (isset($this->_objects[$index]) &&
        is_object($this->_objects[$index])) {
      return $this->_objects[$index];
    }
    if (isset($this->_profiles[$index])) {
      $profile = $this->_profiles[$index];
      if ($profile instanceof Application\Profile) {
        return $this->_objects[$index] = $profile->createObject($this);
      } else {
        return $this->_objects[$index] = call_user_func($profile, $this);
      }
    }
    throw new \InvalidArgumentException(
      'Unknown profile identifier: '.$identifier
    );
  }

  /**
   * Store an object in the application registry.
   *
   * @param string $identifier
   * @param object $object
   * @param int $duplicationMode
   * @throws \LogicException
   */
  public function setObject($identifier, $object, $duplicationMode = self::DUPLICATE_ERROR) {
    \PapayaUtilConstraints::assertObject($object);
    $index = strtolower($identifier);
    if (isset($this->_objects[$index])) {
      switch ($duplicationMode) {
      case self::DUPLICATE_OVERWRITE :
        break;
      case self::DUPLICATE_ERROR :
        throw new \LogicException(
          sprintf(
            'Application object does already exists: "%s"',
            $identifier
          )
        );
      case self::DUPLICATE_IGNORE :
        return;
      }
    }
    $this->_objects[$index] = $object;
  }

  /**
  * Check if an object or an profile for an object exists
  *
  * @param string $identifier
  * @param boolean $checkProfiles
  * @return boolean
  */
  public function hasObject($identifier, $checkProfiles = TRUE) {
    $index = strtolower($identifier);
    if (isset($this->_objects[$index]) &&
        is_object($this->_objects[$index])) {
      return TRUE;
    } elseif (!$checkProfiles) {
      return FALSE;
    }
    return isset($this->_profiles[$index]);
  }

  /**
   * Check if an object or an profile for an object exists
   *
   * @param string $identifier
   * @throws \InvalidArgumentException
   * @return boolean
   */
  public function removeObject($identifier) {
    $index = strtolower($identifier);
    if (isset($this->_objects[$index]) &&
        is_object($this->_objects[$index])) {
      unset($this->_objects[$index]);
      return TRUE;
    } elseif (isset($this->_profiles[$index])) {
      return TRUE;
    }
    throw new \InvalidArgumentException(
      'Unknown profile identifier: '.$identifier
    );
  }

  /**
  * Allow property syntax to get objects from the registry.
  *
  * @see getObject
  * @param string $name
  * @return object
  */
  public function __get($name) {
    return $this->getObject($name);
  }

  /**
  * Allow property syntax to put objects into the registry.
  *
  * @see setObject
  * @param string $name
  * @param object $value
  */
  public function __set($name, $value) {
    $this->setObject($name, $value);
  }


  /**
   * Allow method syntax to get/set objects from/into the registry.
   *
   * @see __get
   * @see __set
   * @param string $name
   * @param $arguments
   * @return object
   */
  public function __call($name, $arguments) {
    if (isset($arguments[0])) {
      $this->__set($name, $arguments[0]);
    }
    return $this->__get($name);
  }

  /**
   * Allow property syntax to check object are availiable, this will return true even if only
   * a profile for the object exists.
   *
   * @see setObject
   * @param string $name
   * @return bool
   */
  public function __isset($name) {
    return $this->hasObject($name);
  }

  /**
   * @param string $offset
   * @return bool
   */
  public function offsetExists($offset) {
    return $this->hasObject($offset);
  }

  /**
   * @param string $offset
   * @return object
   */
  public function offsetGet($offset) {
    return $this->getObject($offset);
  }

  /**
   * @param string $offset
   * @param object $value
   */
  public function offsetSet($offset, $value) {
    $this->setObject($offset, $value);
  }

  /**
   * @param string $offset
   */
  public function offsetUnset($offset) {
    $this->removeObject($offset);
  }
}
