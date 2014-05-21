<?php
/**
* A dialog that can delete a record from a database table using a {@see PapayaDatabaseObjectRecord}
* object.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Delete.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* A dialog that can delete a record from a database table using a {@see PapayaDatabaseObjectRecord}
* object.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogDatabaseDelete extends PapayaUiDialogDatabase {

  /**
  * If the dialog is successfully executed the records is delete.
  *
  * Before deleting the record the callback function is executed to validate the action. If the
  * delete was successful the second callback is executed.
  *
  * To delete the record the method delete() of the record is called.
  *
  * @return boolean
  */
  public function execute() {
    if (parent::execute()) {
      $record = $this->record();
      if ($this->callbacks()->onBeforeDelete($record) && $record->delete()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * If the dialog was executed, block the dialog output.
   *
   * @param PapayaXmlElement $parent
   * @return PapayaXmlElement|NULL
   */
  public function appendTo(PapayaXmlElement $parent) {
    if (!$this->_executionResult) {
      return parent::appendTo($parent);
    }
    return NULL;
  }
}