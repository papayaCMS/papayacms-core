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
* A command that executes a dialog. After dialog creation, and after successfull/failed execuution
* callbacks are executed. This class adds record handling.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCommandDialogDatabaseRecord extends \PapayaUiControlCommandDialog {

  const ACTION_SAVE = 'save';
  const ACTION_DELETE = 'delete';

  private $_record = NULL;

  private $_action = self::ACTION_SAVE;

  /**
   * This dialog command uses database record objects
   *
   * @param \PapayaDatabaseInterfaceRecord $record
   * @param string $action
   */
  public function __construct(\PapayaDatabaseInterfaceRecord $record, $action = self::ACTION_SAVE) {
    $this->record($record);
    $this->_action = $action;
  }

  /**
   * Getter/Setter for the database record
   *
   * @param \PapayaDatabaseInterfaceRecord $record
   * @return \PapayaDatabaseInterfaceRecord
   */
  public function record(\PapayaDatabaseInterfaceRecord $record = NULL) {
    if (isset($record)) {
      $this->_record = $record;
    }
    return $this->_record;
  }

  /**
   * Create a database record aware dialog.
   *
   * @return \PapayaUiDialogDatabaseDelete|\PapayaUiDialogDatabaseSave
   */
  protected function createDialog() {
    switch ($this->_action) {
    case self::ACTION_DELETE :
      $dialog = new \PapayaUiDialogDatabaseDelete($this->record());
      break;
    default :
      $dialog = new \PapayaUiDialogDatabaseSave($this->record());
      break;
    }
    $dialog->papaya($this->papaya());
    return $dialog;
  }
}
