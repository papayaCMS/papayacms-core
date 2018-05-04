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
* callbacks are executed.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCommandDialog extends PapayaUiControlCommand {

  /**
  * Dialog object
  *
  * @var PapayaUiDialog
  */
  private $_dialog = NULL;

  /**
   * @var PapayaRequestParameters
   */
  private $_context = NULL;

  /**
  * Dialog event callbacks
  *
  * @var PapayaUiControlCommandDialogCallbacks
  */
  private $_callbacks = NULL;

  /**
   * Hide dialog after it was executed successfully.
   *
   * @var boolean
   */
  private $_hideAfterSuccess = FALSE;

  /**
   * Reset dialog after it was executed successfully. Ignored if hideAfterSuccess is TRUE.
   *
   * @var boolean
   */
  private $_resetAfterSuccess = FALSE;

  /**
  * Execute command and append result to output xml
  *
  * @param \PapayaXmlElement $parent
  * @return \PapayaXmlElement
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $showDialog = TRUE;
    $dialog = $this->dialog();
    if ($dialog) {
      if ($dialog->execute()) {
        $this->callbacks()->onExecuteSuccessful($dialog, $parent);
        if ($this->hideAfterSuccess()) {
          $showDialog = FALSE;
        } else {
          $showDialog = TRUE;
          if ($this->resetAfterSuccess()) {
            $this->reset();
          }
        }
      } elseif ($dialog->isSubmitted()) {
        $this->callbacks()->onExecuteFailed($dialog, $parent);
      }
    }
    if ($showDialog && $this->dialog()) {
      return $parent->append($this->dialog());
    }
    return $parent;
  }

  /**
   * A context for the dialog - to be set as hidden values or used in links
   *
   * @param \PapayaRequestParameters $context
   * @return \PapayaRequestParameters
   */
  public function context(\PapayaRequestParameters $context = NULL) {
    if (isset($context)) {
      $this->_context = $context;
    }
    return $this->_context;
  }

  /**
  * Getter/Setter for the dialog. If implizit create is used the createDialog method is called.
  *
  * @param \PapayaUiDialog $dialog
  * @return \PapayaUiDialog
  */
  public function dialog(\PapayaUiDialog $dialog = NULL) {
    if (isset($dialog)) {
      $this->_dialog = $dialog;
    } elseif (is_null($this->_dialog)) {
      $this->_dialog = $this->createDialog();
      if (isset($this->_context)) {
        $this->_dialog->hiddenValues()->merge($this->_context);
      }
      $this->callbacks()->onCreateDialog($this->_dialog);
    }
    return $this->_dialog;
  }

  /**
  * Getter/Setter for the callbacks object
  *
  * @param \PapayaUiControlCommandDialogCallbacks $callbacks
  * @return \PapayaUiControlCommandDialogCallbacks
  */
  public function callbacks(\PapayaUiControlCommandDialogCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (is_null($this->_callbacks)) {
      $this->_callbacks = new \PapayaUiControlCommandDialogCallbacks();
    }
    return $this->_callbacks;
  }

  /**
  * Create and return a dialog object, can be overloaded by child classes to create specific
  * dialogs.
  *
  * @return \PapayaUiDialog
  */
  protected function createDialog() {
    $dialog = new \PapayaUiDialog();
    $dialog->papaya($this->papaya());
    return $dialog;
  }

  /**
   * Getter/Setter for the hide dialog option. If it is set to TRUE the dialog will be hidden
   * (aka not added to the DOM) if it was executed successfully.
   *
   * @param NULL|boolean $hide
   * @return boolean
   */
  public function hideAfterSuccess($hide = NULL) {
    if (isset($hide)) {
      $this->_hideAfterSuccess = (bool)$hide;
    }
    return $this->_hideAfterSuccess;
  }

  /**
   * Getter/Setter for the reset dialog option. If it is set to TRUE the dialog will be unset
   * if it was executed successfully, triggering the implicit create.
   *
   * Ignored if the hide after success option is active.
   *
   * @param NULL|boolean $reset
   * @return boolean
   */
  public function resetAfterSuccess($reset = NULL) {
    if (isset($reset)) {
      $this->_resetAfterSuccess = (bool)$reset;
    }
    return $this->_resetAfterSuccess;
  }

  /**
   * Reset the action, (set the dialog to NULL again)
   */
  public function reset() {
    if (isset($this->_dialog)) {
      $this->_dialog = NULL;
    }
  }
}
