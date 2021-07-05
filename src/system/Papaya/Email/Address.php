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
namespace Papaya\Email;

use InvalidArgumentException;

/**
 * A single Email address, including properties for the parts and string casting.
 *
 * @package Papaya-Library
 * @subpackage Email
 *
 * @property string $address
 * @property string $email
 * @property string $name
 */
class Address {
  /**
   * Recipient name
   *
   * @var string
   */
  private $_name = '';

  /**
   * Recipient email
   *
   * @var string
   */
  private $_email = '';

  /**
   * Initialize object with address if provided.
   *
   * @param string|NULL $address
   * @param string $name
   */
  public function __construct(string $address = NULL, string $name = '') {
    if (NULL !== $address) {
      $this->setAddress($address, $name);
    }
  }

  /**
   * Cast object to string. Returns "email" or "name <email>".
   *
   * @return string
   */
  public function __toString() {
    if (empty($this->_name)) {
      return $this->_email;
    }
    return $this->_name.' <'.$this->_email.'>';
  }

  /**
   * Set address from string (can include a name)
   *
   * @param string $address
   * @param string $name
   */
  protected function setAddress(string $address, string $name = ''): void {
    if (\preg_match('(^\s*(.*?)\s*<([^>]+)>)', $address, $matches)) {
      $this->_name = $matches[1];
      $this->_email = $matches[2];
    } else {
      $this->_email = $address;
      $this->_name = $name;
    }
  }

  /**
   * Set recipient name
   *
   * @param string $name
   */
  protected function setName(string $name): void {
    $this->_name = $name;
  }

  /**
   * @param $name
   *
   * @return bool
   */
  public function __isset($name) {
    switch ($name) {
      case 'name' :
      case 'email' :
      case 'address' :
        return TRUE;
    }
    return FALSE;
  }

  /**
   * Dynamic property setter
   *
   * @param string $name
   * @param string $value
   *
   * @throws InvalidArgumentException
   */
  public function __set(string $name, string $value) {
    switch ($name) {
      case 'name' :
        $this->setName($value);
        return;
      case 'email' :
        $this->setAddress($value, $this->_name);
        return;
      case 'address' :
        $this->setAddress($value);
        return;
    }
    throw new InvalidArgumentException(
      \sprintf('InvalidArgumentException: Unknown property "%s".', $name)
    );
  }

  /**
   * Dynamic property getter
   *
   * @param string $name
   *
   * @throws InvalidArgumentException
   *
   * @return string
   */
  public function __get(string $name) {
    switch ($name) {
      case 'name' :
        return $this->_name;
      case 'email' :
        return $this->_email;
      case 'address' :
        return $this->__toString();
    }
    throw new InvalidArgumentException(
      \sprintf('InvalidArgumentException: Unknown property "%s".', $name)
    );
  }
}
