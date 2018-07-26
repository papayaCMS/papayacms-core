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

namespace Papaya\Configuration\Storage;

/**
 * Load options from database table
 *
 * @package Papaya-Library
 * @subpackage Configuration
 */
class Database extends \PapayaObject
  implements \Papaya\Configuration\Storage {

  /**
   * Options database records list
   *
   * @var \Papaya\Content\Configuration
   */
  private $_records;

  /**
   * Getter/Setter for database records object
   *
   * @param \Papaya\Content\Configuration $records
   * @return \Papaya\Content\Configuration
   */
  public function records(\Papaya\Content\Configuration $records = NULL) {
    if (NULL !== $records) {
      $this->_records = $records;
    } elseif (NULL === $this->_records) {
      $this->_records = new \Papaya\Content\Configuration();
    }
    return $this->_records;
  }

  /**
   * Dispatch the error message as http header and be silent otherwise.
   *
   * @param \Papaya\Database\Exception $exception
   */
  public function handleError(\Papaya\Database\Exception $exception) {
    if (
      isset($this->papaya()->response) &&
      $this->papaya()->options->get('PAPAYA_DBG_DEVMODE', FALSE)
    ) {
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
   * @return \Iterator
   */
  public function getIterator() {
    $options = array();
    foreach ($this->records() as $option) {
      $options[$option['name']] = $option['value'];
    }
    return new \ArrayIterator($options);
  }
}
