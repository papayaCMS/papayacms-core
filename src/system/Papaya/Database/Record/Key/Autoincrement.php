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
* An single field autoincrement key
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Autoincrement.php 39197 2014-02-11 13:36:56Z weinert $
*/
class PapayaDatabaseRecordKeyAutoincrement implements \PapayaDatabaseInterfaceKey {

  /**
  * the property name
  *
  * @var string
  */
  private $_property = 'id';

  /**
  * the current field value
  *
  * @var NULL|integer
  */
  private $_value = NULL;

  /**
  * Create object and set the identifier property, the default
  *
  * @var NULL|integer
  */
  public function __construct($property = 'id') {
    $this->_property = $property;
  }

  /**
  * Provide information if the key is autoincrement
  *
  * @return integer
  */
  public function getQualities() {
    return \PapayaDatabaseInterfaceKey::DATABASE_PROVIDED;
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
  * The key is provided by the database so it should always exists if it is set.
  *
  * @return boolean
  */
  public function exists() {
    return isset($this->_value);
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
  * Get the property names of the key. This will always be on property for an autoincrement key.
  *
  * @return array(string)
  */
  public function getProperties() {
    return array($this->_property);
  }

  /**
  * Get the a property=>value array to use it. A mapping is used to convert it into acutal database
  * fields
  *
  * @param integer $for the action the filter ist fetched for
  * @return array(string)
  */
  public function getFilter($for = self::ACTION_FILTER) {
    return array($this->_property => $this->_value);
  }
}
