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

namespace Papaya\Ui\Control;
/**
 * Abstract superclass implementing basic features for user interface control parts.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
abstract class Part
  extends \Papaya\Application\BaseObject
  implements \Papaya\Xml\Appendable {

  /**
   * Allows to declare dynamic properties with optional getter/setter methods. The read and write
   * options can be methods or properties. If no write option is provided the property is read only.
   *
   * array(
   *   'propertyName' => array('read', 'write')
   * )
   *
   * @var array
   */
  protected $_declaredProperties = array();


  /**
   * Validate dynamic property against the declared properties array. Call getter method or read
   * protected property.
   *
   * @throws \UnexpectedValueException
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    if (
      isset($this->_declaredProperties[$name]) &&
      isset($this->_declaredProperties[$name][0])
    ) {
      $read = $this->_declaredProperties[$name][0];
      if (method_exists($this, $read)) {
        return $this->$read();
      } elseif (isset($this->$read) || property_exists(get_class($this), $read)) {
        return $this->$read;
      } else {
        throw new \UnexpectedValueException(
          sprintf(
            'Invalid declaration: Can not read property "%s::$%s".',
            get_class($this),
            $name
          )
        );
      }
    }
    throw new \UnexpectedValueException(
      sprintf(
        'Can not read unknown property "%s::$%s".',
        get_class($this),
        $name
      )
    );
  }

  /**
   * Validate dynamic property against the declared properties array. Call setter method or write
   * protected property.
   *
   * @throws \UnexpectedValueException
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value) {
    if (isset($this->_declaredProperties[$name]) &&
      isset($this->_declaredProperties[$name][1])) {
      $write = $this->_declaredProperties[$name][1];
      if (method_exists($this, $write)) {
        $this->$write($value);
        return;
      } elseif (isset($this->$write) || property_exists(get_class($this), $write)) {
        $this->$write = $value;
        return;
      } else {
        throw new \UnexpectedValueException(
          sprintf(
            'Invalid declaration: Can not write property "%s::$%s".',
            get_class($this),
            $name
          )
        );
      }
    } elseif (isset($this->_declaredProperties[$name]) &&
      isset($this->_declaredProperties[$name][0])) {
      throw new \UnexpectedValueException(
        sprintf(
          'Invalid declaration: Can not write readonly property "%s::$%s".',
          get_class($this),
          $name
        )
      );
    }
    throw new \UnexpectedValueException(
      sprintf(
        'Can not write unknown property "%s::$%s".',
        get_class($this),
        $name
      )
    );
  }
}
