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
* Load options from database table
*
* @package Papaya-Library
* @subpackage Configuration
*/
class PapayaConfigurationStorageDatabase extends PapayaObject
  implements PapayaConfigurationStorage {

   /**
   * Options database records list
   *
   * @var PapayaContentConfiguration
   */
   private $_records = NULL;

  /**
  * Getter/Setter for database records object
  *
  * @param PapayaContentConfiguration $records
  * @return PapayaContentConfiguration
  */
  public function records(PapayaContentConfiguration $records = NULL) {
    if (isset($records)) {
      $this->_records = $records;
    } elseif (is_null($this->_records)) {
      $this->_records = new \PapayaContentConfiguration();
    }
    return $this->_records;
  }

  /**
  * Dipatch the error message as http header and be silent otherwise.
  *
  * @param PapayaDatabaseException $exception
  */
  public function handleError(PapayaDatabaseException $exception) {
    if ($this->papaya()->options->get('PAPAYA_DBG_DEVMODE', FALSE) &&
        isset($this->papaya()->response)) {
      $message = str_replace(array('\r', '\n'), ' ', $exception->getMessage());
      $this->papaya()->response->sendHeader(
        'X-Papaya-Error: '.get_class($exception).': '.$message
      );
    }
  }

  /**
  * Load records from database
  *
  * @return boolean
  */
  public function load() {
    $this->records()->getDatabaseAccess()->errorHandler(array($this, 'handleError'));
    return $this->records()->load();
  }

  /**
  * Get iterator for options array(name => value)
  *
  * @return Iterator
  */
  public function getIterator() {
    $options = array();
    foreach ($this->records() as $option) {
      $options[$option['name']] = $option['value'];
    }
    return new \ArrayIterator($options);
  }
}
