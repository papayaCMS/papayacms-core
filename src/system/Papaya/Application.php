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
namespace Papaya {

  use Papaya\Application\Profile;
  use Papaya\Application\Profiles;
  use Papaya\BaseObject\Interfaces\Properties;

  /**
   * Papaya Application - object registry with profiles
   *
   * @package Papaya-Library
   * @subpackage Application
   */
  class Application implements \ArrayAccess, Properties, \IteratorAggregate {
    /**
     * Duplicate profiles trigger an error
     *
     * @var int
     */
    const DUPLICATE_ERROR = 0;

    /**
     * Ignore duplicate profiles
     *
     * @var int
     */
    const DUPLICATE_IGNORE = 1;

    /**
     * Overwrite duplicate profiles
     *
     * @var int
     */
    const DUPLICATE_OVERWRITE = 2;

    /**
     * Class variable for singleton instance
     *
     * @var Application
     */
    protected static $instance;

    /**
     * Profile objects
     *
     * @var array
     */
    private $_profiles = [];

    /**
     * Objects
     *
     * @var array(object)
     */
    private $_objects = [];

    /**
     * Create a new instance of this class or return existing one (singleton)
     *
     * @param bool $reset
     * @return self
     */
    public static function getInstance($reset = FALSE, callable $init = NULL) {
      if ($reset || NULL === self::$instance) {
        self::$instance = new self();
        if (is_callable($init)) {
          $init(self::$instance);
        }
      }
      return self::$instance;
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): \Traversable {
      return new \ArrayIterator($this->_objects);
    }

    /**
     * @return array
     */
    public function getProfiles() {
      return $this->_profiles;
    }

    /**
     * Register a collection of profiles
     *
     * @param Profiles $profiles
     * @param int $duplicationMode
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
     * @param Profile|callable $profile
     * @param int $duplicationMode
     *
     * @throws \InvalidArgumentException
     */
    public function registerProfile(
      $identifier, $profile, $duplicationMode = self::DUPLICATE_ERROR
    ) {
      if (!($profile instanceof Application\Profile || \is_callable($profile))) {
        throw new \InvalidArgumentException(
          \sprintf(
            'Invalid profile %s is %s.',
            $identifier,
            \is_object($profile) ? \get_class($profile) : \gettype($profile)
          )
        );
      }
      $index = \strtolower($identifier);
      if (isset($this->_profiles[$index])) {
        switch ($duplicationMode) {
        case self::DUPLICATE_OVERWRITE :
          break;
        case self::DUPLICATE_ERROR :
          throw new \InvalidArgumentException(
            \sprintf(
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
     * @param bool $silent
     * @return object
     */
    public function getObject($identifier, $silent = FALSE) {
      $index = \strtolower($identifier);
      if (
        isset($this->_objects[$index]) &&
        \is_object($this->_objects[$index])
      ) {
        return $this->_objects[$index];
      }
      if (isset($this->_profiles[$index])) {
        $profile = $this->_profiles[$index];
        if ($profile instanceof Application\Profile) {
          return $this->_objects[$index] = $profile->createObject($this);
        }
        return $this->_objects[$index] = $profile($this);
      }
      if ($silent) {
        return NULL;
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
     *
     * @throws \LogicException
     */
    public function setObject($identifier, $object, $duplicationMode = self::DUPLICATE_ERROR) {
      Utility\Constraints::assertObject($object);
      $index = \strtolower($identifier);
      if (isset($this->_objects[$index])) {
        switch ($duplicationMode) {
        case self::DUPLICATE_OVERWRITE :
          break;
        case self::DUPLICATE_ERROR :
          throw new \LogicException(
            \sprintf(
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
     * @param bool $checkProfiles
     *
     * @return bool
     */
    public function hasObject($identifier, $checkProfiles = TRUE) {
      $index = \strtolower($identifier);
      if (
        isset($this->_objects[$index]) &&
        \is_object($this->_objects[$index])
      ) {
        return TRUE;
      }
      if (!$checkProfiles) {
        return FALSE;
      }
      return isset($this->_profiles[$index]);
    }

    /**
     * Remove and existing object and optionally its profile
     *
     * @param string $identifier
     *
     * @param bool $removeProfile
     */
    public function removeObject($identifier, $removeProfile = FALSE) {
      if ($this->hasObject($identifier)) {
        $index = \strtolower($identifier);
        if (
        isset($this->_objects[$index])
        ) {
          unset($this->_objects[$index]);
        }
        if ($removeProfile && isset($this->_profiles[$index])) {
          unset($this->_profiles[$index]);
        }
      } else {
        throw new \InvalidArgumentException(
          'Unknown profile identifier: '.$identifier
        );
      }
    }

    /**
     * Allow property syntax to check object are available, this will return true even if only
     * a profile for the object exists.
     *
     * @param string $name
     *
     * @return bool
     * @see setObject
     *
     */
    public function __isset($name) {
      return $this->hasObject($name);
    }

    /**
     * Allow property syntax to get objects from the registry.
     *
     * @param string $name
     *
     * @return object
     * @see getObject
     *
     */
    public function __get($name) {
      return $this->getObject($name, TRUE);
    }

    /**
     * Allow property syntax to put objects into the registry.
     *
     * @param string $name
     * @param object $value
     * @see setObject
     *
     */
    public function __set($name, $value) {
      $this->setObject($name, $value);
    }

    /**
     * Allow property syntax to remove objects from the registry.
     *
     * @param string $name
     * @see setObject
     *
     */
    public function __unset($name) {
      $this->removeObject($name);
    }

    /**
     * Allow method syntax to get/set objects from/into the registry.
     *
     * @param string $name
     * @param $arguments
     *
     * @return object
     * @see __set
     *
     * @see __get
     */
    public function __call($name, $arguments) {
      if (isset($arguments[0])) {
        $this->__set($name, $arguments[0]);
      }
      return $this->__get($name);
    }

    /**
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool {
      return $this->hasObject($offset);
    }

    /**
     * @param string $offset
     *
     * @return object
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset) {
      return $this->getObject($offset);
    }

    /**
     * @param string $offset
     * @param object $value
     */
    public function offsetSet($offset, $value): void {
      $this->setObject($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void {
      $this->removeObject($offset);
    }
  }
}
