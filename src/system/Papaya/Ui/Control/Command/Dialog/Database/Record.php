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

namespace Papaya\Ui\Control\Command\Dialog\Database;
/**
 * A command that executes a dialog. After dialog creation, and after successfull/failed execuution
 * callbacks are executed. This class adds record handling.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class Record extends \Papaya\Ui\Control\Command\Dialog {

  const ACTION_SAVE = 'save';
  const ACTION_DELETE = 'delete';

  private $_record;

  private $_action;

  /**
   * This dialog command uses database record objects
   *
   * @param \Papaya\Database\Interfaces\Record $record
   * @param string $action
   */
  public function __construct(\Papaya\Database\Interfaces\Record $record, $action = self::ACTION_SAVE) {
    $this->record($record);
    $this->_action = $action;
  }

  /**
   * Getter/Setter for the database record
   *
   * @param \Papaya\Database\Interfaces\Record $record
   * @return \Papaya\Database\Interfaces\Record
   */
  public function record(\Papaya\Database\Interfaces\Record $record = NULL) {
    if (NULL !== $record) {
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
