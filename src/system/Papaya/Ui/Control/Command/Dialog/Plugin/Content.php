<?php
/**
* A command that executes a dialog. After dialog creation, and after successfull/failed execution
* callbacks are executed. This class adds read and write the data to an plugin content object
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
* @version $Id: Content.php 39132 2014-02-06 18:26:01Z weinert $
*/

/**
* A command that executes a dialog. After dialog creation, and after successfull/failed execution
* callbacks are executed. This class adds read and write the data to an plugin content object
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCommandDialogPluginContent extends PapayaUiControlCommandDialog {

  private $_content = NULL;

  /**
   * This dialog command uses database record objects
   *
   * @param PapayaPluginEditableContent $content
   */
  public function __construct(PapayaPluginEditableContent $content) {
    $this->_content = $content;
  }

  /**
   * Getter/Setter for the database record
   *
   * @return NULL|PapayaPluginEditableContent
   */
  public function getContent() {
    return $this->_content;
  }

  /**
  * Execute command and append result to output xml
  *
  * @param PapayaXmlElement
  * @return PapayaXmlElement
  */
  public function appendTo(PapayaXmlElement $parent) {
    $showDialog = TRUE;
    $dialog = $this->dialog();
    if ($dialog->execute()) {
      $this->getContent()->assign($dialog->data());
      $this->callbacks()->onExecuteSuccessful($dialog, $parent);
      $showDialog = !$this->hideAfterSuccess();
    } elseif ($dialog->isSubmitted()) {
      $this->callbacks()->onExecuteFailed($dialog, $parent);
    }
    if ($showDialog) {
      return $dialog->appendTo($parent);
    }
    return $parent;
  }

  /**
   * Create the dialog object and assign the content data to it.
   * @see PapayaUiControlCommandDialog::createDialog()
   * @return PapayaUiDialog
   */
  protected function createDialog() {
    $dialog = parent::createDialog();
    $dialog->data()->assign($this->getContent());
    return $dialog;
  }
}