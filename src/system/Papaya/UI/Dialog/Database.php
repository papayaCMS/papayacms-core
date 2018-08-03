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

namespace Papaya\UI\Dialog;
/**
 * A dialog superclass for dialogs that execute database actions on
 * {@see \Papaya\Database\BaseObject\PapayaDatabaseObjectRecord} instances
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Database extends \Papaya\UI\Dialog {

  /**
   * Attached database callbacks object
   *
   * @var \Papaya\Database\BaseObject\Record
   */
  private $_callbacks = NULL;

  /**
   * Attached database record object
   *
   * @var \Papaya\Database\Interfaces\Record
   */
  private $_record = NULL;

  /**
   * Create dialog and attach a record to it.
   *
   * @param \Papaya\Database\Interfaces\Record $record
   */
  public function __construct(\Papaya\Database\Interfaces\Record $record) {
    $this->record($record);
  }

  /**
   * Getter/Setter for the database record object. The record is set in the constructor always.
   * But it can be changed or accessed using this method.
   *
   * The values of the record are merged into the data property.
   *
   * @param \Papaya\Database\Interfaces\Record $record
   * @return \Papaya\Database\Interfaces\Record
   */
  public function record(\Papaya\Database\Interfaces\Record $record = NULL) {
    if (isset($record)) {
      $this->_record = $record;
      $this->data()->merge((array)$record->toArray());
    }
    return $this->_record;
  }

  /**
   * Getter/Setter for the callbacks, if you set your own callback object, make sure it has the
   * needed definitions.
   *
   * @param \Papaya\UI\Dialog\Database\Callbacks $callbacks
   * @return \Papaya\UI\Dialog\Database\Callbacks
   */
  public function callbacks(\Papaya\UI\Dialog\Database\Callbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    }
    if (is_null($this->_callbacks)) {
      $this->_callbacks = new \Papaya\UI\Dialog\Database\Callbacks();
    }
    return $this->_callbacks;
  }
}
