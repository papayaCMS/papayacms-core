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

/**
 * An basic framework object including request parameters handling
 *
 * @package Papaya-Library
 * @subpackage Objects
 */
trait DeclaredProperties {

  /**
   * @var array|NULL
   */
  private $_cachedPropertyDeclaration;

  /**
   * Allows to declare dynamic properties with optional getter/setter methods. The read and write
   * options can be closures, methods or properties. If no write option is provided the property is read only.
   *
   * [
   *   'propertyName' => ['read', 'write']
   * ]
   *
   * @return array
   */
  abstract public function getPropertyDeclaration();

  private function _getResolvedPropertyDeclaration() {
    if (NULL === $this->_cachedPropertyDeclaration) {
      $this->_cachedPropertyDeclaration = [];
      foreach ($this->getPropertyDeclaration() as $propertyName => $declaration) {
        $definition = [
          'get' => static function ($object) use ($propertyName) {
            throw new \UnexpectedValueException(
              \sprintf(
                'Invalid declaration: Can not read property "%s::$%s".',
                \get_class($object),
                $propertyName
              )
            );
          },
          'set' => static function ($object) use ($propertyName) {
            throw new \UnexpectedValueException(
              \sprintf(
                'Invalid declaration: Can not write property "%s::$%s".',
                \get_class($object),
                $propertyName
              )
            );
          }
        ];
        if (isset($declaration[0])) {
          $getter = $declaration[0];
          if ($getter instanceof \Closure) {
            $definition['get'] = static function($object) use ($getter) {
              $closure = \Closure::bind($getter, $object);
              return $closure();
            };
          } elseif (method_exists($this, $getter)) {
            $definition['get'] = static function($object) use ($getter) {
              return $object->$getter();
            };
          } elseif (property_exists(get_class($this), $getter)) {
            $definition['get'] = static function($object) use ($getter) {
              return $object->$getter;
            };
          }
        }
        if (isset($declaration[1])) {
          $setter = $declaration[1];
          if ($setter instanceof \Closure) {
            $definition['set'] = static function($object, $value) use ($setter) {
              $closure = \Closure::bind($setter, $object);
              $closure($value);
            };
          } elseif (method_exists($this, $setter)) {
            $definition['set'] = static function ($object, $value) use ($setter) {
              $object->$setter($value);
            };
          } elseif (\property_exists(\get_class($this), $setter)) {
            $definition['set'] = static function ($object, $value) use ($setter) {
              $object->$setter = $value;
            };
          }
        } elseif (NULL !== $declaration[0]) {
          $definition['set'] = function () use ($propertyName) {
            throw new \UnexpectedValueException(
              \sprintf(
                'Invalid declaration: Can not write readonly property "%s::$%s".',
                \get_class($this),
                $propertyName
              )
            );
          };
        }
        $this->_cachedPropertyDeclaration[$propertyName] = $definition;
      }
    }
    return $this->_cachedPropertyDeclaration;
  }

  /**
   * @param string $name
   * @return bool
   */
  public function __isset($name) {
    $definition = $this->_getResolvedPropertyDeclaration();
    return isset($definition[$name]['get']) && NULL !== $this->__get($name);
  }

  /**
   * Validate dynamic property against the declared properties array. Call getter method or read
   * protected property.
   *
   * @param string $name
   *
   * @return mixed
   * @throws \UnexpectedValueException
   *
   */
  public function __get($name) {
    $definition = $this->_getResolvedPropertyDeclaration();
    if (isset($definition[$name]['get'])) {
      $read = $definition[$name]['get'];
      return $read($this);
    }
    throw new \UnexpectedValueException(
      \sprintf(
        'Can not read unknown property "%s::$%s".',
        \get_class($this),
        $name
      )
    );
  }

  /**
   * Validate dynamic property against the declared properties array. Call setter method or write
   * protected property.
   *
   * @param string $name
   * @param mixed $value
   * @throws \UnexpectedValueException
   *
   */
  public function __set($name, $value) {
    $definition = $this->_getResolvedPropertyDeclaration();
    if (isset($definition[$name]['set'])) {
      $write = $definition[$name]['set'];
      $write($this, $value);
      return;
    }
    throw new \UnexpectedValueException(
      \sprintf(
        'Can not write unknown property "%s::$%s".',
        \get_class($this),
        $name
      )
    );
  }

  /**
   * You can not remove declared properties, calling unset is the same as setting them to NULL.
   *
   * @param $name
   */
  public function __unset($name) {
    $this->__set($name, NULL);
  }
}
