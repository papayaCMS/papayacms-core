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
namespace Papaya\Content\Structure;

/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 *
 * @link http://www.papaya-cms.com/
 *
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
abstract class Node extends \Papaya\Application\BaseObject {
  /**
   * @var array
   */
  private $_properties;

  public function __construct($properties) {
    $this->_properties = $properties;
  }

  public function __isset($name) {
    try {
      $value = $this->$name;
      return isset($value);
    } catch (\UnexpectedValueException $e) {
      return FALSE;
    }
  }

  public function __get($name) {
    $getter = 'get'.\Papaya\Utility\Text\Identifier::toCamelCase($name, TRUE);
    if (\method_exists($this, $getter)) {
      return \call_user_func([$this, $getter]);
    } elseif (\array_key_exists($name, $this->_properties)) {
      return $this->_properties[$name];
    }
    throw new \UnexpectedValueException(
      \sprintf(
        'Can not read unknown property "%s::$%s".',
        \get_class($this),
        $name
      )
    );
  }

  public function __set($name, $value) {
    $setter = 'set'.\Papaya\Utility\Text\Identifier::toCamelCase($name, TRUE);
    if (\method_exists($this, $setter)) {
      \call_user_func([$this, $setter], $value);
    } else {
      $this->setValue($name, $value);
    }
  }

  protected function setValue($name, $value) {
    if (\array_key_exists($name, $this->_properties)) {
      $this->_properties[$name] = $value;
    } else {
      throw new \UnexpectedValueException(
        \sprintf(
          'Can not write unknown property "%s::$%s".',
          \get_class($this),
          $name
        )
      );
    }
  }

  public function setName($name) {
    \Papaya\Utility\Text\XML::isQName($name);
    $this->_properties['name'] = $name;
  }
}
