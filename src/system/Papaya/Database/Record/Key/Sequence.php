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

namespace Papaya\Database\Record\Key;
/**
 * An single field key, provided by a sequence object, the sequence is created on the
 * client side and the sequence object validates the existance in the database.
 *
 * @package Papaya-Library
 * @subpackage Database
 * @version $Id: Sequence.php 39197 2014-02-11 13:36:56Z weinert $
 */
class Sequence implements \Papaya\Database\Interfaces\Key {

  /**
   * Sequence object to create new identifiers
   *
   * @var \Papaya\Database\Sequence
   */
  private $_sequence = NULL;

  /**
   * the property name of the identifier field
   *
   * @var string
   */
  private $_property = 'id';

  /**
   * the current field value
   *
   * @var NULL|string|integer
   */
  private $_value = NULL;

  /**
   * Create objecd and store sequence and property.
   *
   * @param \Papaya\Database\Sequence $sequence
   * @param string $property
   */
  public function __construct(\Papaya\Database\Sequence $sequence, $property = 'id') {
    $this->_sequence = $sequence;
    $this->_property = $property;
  }

  /**
   * Provide information about the key
   *
   * @var integer
   * @return int
   */
  public function getQualities() {
    return \Papaya\Database\Interfaces\Key::CLIENT_GENERATED;
  }

  /**
   * Assign data to the key. This is an array because others keys can consist of multiple fields
   *
   * @param array $data
   * @return bool
   */
  public function assign(array $data) {
    foreach ($data as $name => $value) {
      if ($name === $this->_property) {
        $this->_value = $value;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Validate if the record exists. In this case if the key value is not null it will
   * be considered as TRUE without asking the database.
   *
   * The key is provided by the key sequence object so it should always exists if it is set.
   *
   * @return boolean
   */
  public function exists() {
    return !empty($this->_value);
  }

  /**
   * Clear the key value
   */
  public function clear() {
    $this->_value = NULL;
  }

  /**
   * Convert the key values into an string, that can be used in array keys.
   *
   * @return string
   */
  public function __toString() {
    return (string)$this->_value;
  }

  /**
   * Get the property names of the key. This will always be on property for an sequence key.
   *
   * @return array(string)
   */
  public function getProperties() {
    return array($this->_property);
  }

  /**
   * Get the a property=>value array to use it. A mapping is used to convert it into actual database
   * fields.
   *
   * If the filter for a create action (insert) is requested, a new id is created using the sequence
   * object.
   *
   * @param integer $for the action the filter ist fetched for
   * @return array(string)
   */
  public function getFilter($for = self::ACTION_FILTER) {
    if ($for == self::ACTION_CREATE) {
      return array($this->_property => $this->_sequence->next());
    }
    return array($this->_property => $this->_value);
  }
}
