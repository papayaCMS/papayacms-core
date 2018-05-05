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
* A dialog superclass for dialogs that execute database actions on {@see PapayaDatabaseObjectRecord}
* instances
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiDialogDatabase extends \PapayaUiDialog {

  /**
  * Attached database callbacks object
  *
  * @var PapayaDatabaseObjectRecord
  */
  private $_callbacks = NULL;

  /**
  * Attached database record object
  *
  * @var PapayaDatabaseInterfaceRecord
  */
  private $_record = NULL;

  /**
  * Create dialog and attach a record to it.
  *
  * @param \PapayaDatabaseInterfaceRecord $record
  */
  public function __construct(\PapayaDatabaseInterfaceRecord $record) {
    $this->record($record);
  }

  /**
  * Getter/Setter for the database record object. The record is set in the constructor always.
  * But it can be changed or accessed using this method.
  *
  * The values of the record are merged into the data property.
  *
  * @param \PapayaDatabaseInterfaceRecord $record
  * @return \PapayaDatabaseInterfaceRecord
  */
  public function record(\PapayaDatabaseInterfaceRecord $record = NULL) {
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
  * @param \PapayaUiDialogDatabaseCallbacks $callbacks
  * @return \PapayaUiDialogDatabaseCallbacks
  */
  public function callbacks(\PapayaUiDialogDatabaseCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    }
    if (is_null($this->_callbacks)) {
      $this->_callbacks = new \PapayaUiDialogDatabaseCallbacks();
    }
    return $this->_callbacks;
  }
}
