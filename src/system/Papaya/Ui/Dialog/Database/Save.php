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

namespace Papaya\Ui\Dialog\Database;
/**
 * A dialog that can add/edit a record to a database table using a
 * {@see \Papaya\Database\BaseObject\PapayaDatabaseObjectRecord} object.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class Save extends \PapayaUiDialogDatabase {

  /**
   * If the dialog is successfully executed the records is saved.
   *
   * Before saving the record, the orignal object is cloned, gets the new data assigned and
   * a callback function is executed if defined.
   *
   * @return boolean
   */
  public function execute() {
    if (parent::execute()) {
      $record = clone $this->record();
      $record->assign($this->data());
      $record->assign($this->hiddenFields());
      if ($this->callbacks()->onBeforeSave($record) && $record->save()) {
        $this->record()->assign($record->toArray());
        return TRUE;
      }
    }
    return FALSE;
  }
}
