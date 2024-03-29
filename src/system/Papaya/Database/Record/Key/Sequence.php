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

use Papaya\Database;

/**
 * An single field key, provided by a sequence object, the sequence is created on the
 * client side and the sequence object validates the existance in the database.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Sequence implements Database\Interfaces\Key {
  /**
   * Sequence object to create new identifiers
   *
   * @var Database\Sequence
   */
  private $_sequence;

  /**
   * the property name of the identifier field
   *
   * @var string
   */
  private $_property;

  /**
   * the current field value
   *
   * @var null|string|int
   */
  private $_value;

  /**
   * Create objecd and store sequence and property.
   *
   * @param Database\Sequence $sequence
   * @param string $property
   */
  public function __construct(Database\Sequence $sequence, $property = 'id') {
    $this->_sequence = $sequence;
    $this->_property = $property;
  }

  public function getSequence(): Database\Sequence {
    return $this->_sequence;
  }

  /**
   * Provide information about the key
   *
   * @var int
   *
   * @return int
   */
  public function getQualities() {
    return Database\Interfaces\Key::CLIENT_GENERATED;
  }

  /**
   * Assign data to the key. This is an array because others keys can consist of multiple fields
   *
   * @param array $data
   *
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
   * @return bool
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
    return [$this->_property];
  }

  /**
   * Get the a property=>value array to use it. A mapping is used to convert it into actual database
   * fields.
   *
   * If the filter for a create action (insert) is requested, a new id is created using the sequence
   * object.
   *
   * @param int $for the action the filter ist fetched for
   *
   * @return array(string)
   */
  public function getFilter($for = self::ACTION_FILTER) {
    if (self::ACTION_CREATE === $for) {
      return [$this->_property => $this->_sequence->next()];
    }
    return [$this->_property => $this->_value];
  }
}
