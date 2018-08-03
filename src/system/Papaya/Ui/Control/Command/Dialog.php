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

namespace Papaya\Ui\Control\Command;
/**
 * A command that executes a dialog. After dialog creation, and after successfull/failed execuution
 * callbacks are executed.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class Dialog extends \Papaya\Ui\Control\Command {

  /**
   * Dialog object
   *
   * @var \Papaya\Ui\Dialog
   */
  private $_dialog;

  /**
   * @var \Papaya\Request\Parameters
   */
  private $_context;

  /**
   * Dialog event callbacks
   *
   * @var Dialog\Callbacks
   */
  private $_callbacks;

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
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
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
   * @param \Papaya\Request\Parameters $context
   * @return \Papaya\Request\Parameters
   */
  public function context(\Papaya\Request\Parameters $context = NULL) {
    if (NULL !== $context) {
      $this->_context = $context;
    }
    return $this->_context;
  }

  /**
   * Getter/Setter for the dialog. If implizit create is used the createDialog method is called.
   *
   * @param \Papaya\Ui\Dialog $dialog
   * @return \Papaya\Ui\Dialog
   */
  public function dialog(\Papaya\Ui\Dialog $dialog = NULL) {
    if (NULL !== $dialog) {
      $this->_dialog = $dialog;
    } elseif (NULL === $this->_dialog) {
      $this->_dialog = $this->createDialog();
      if (NULL !== $this->_context) {
        $this->_dialog->hiddenValues()->merge($this->_context);
      }
      $this->callbacks()->onCreateDialog($this->_dialog);
    }
    return $this->_dialog;
  }

  /**
   * Getter/Setter for the callbacks object
   *
   * @param Dialog\Callbacks $callbacks
   * @return Dialog\Callbacks
   */
  public function callbacks(Dialog\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new Dialog\Callbacks();
    }
    return $this->_callbacks;
  }

  /**
   * Create and return a dialog object, can be overloaded by child classes to create specific
   * dialogs.
   *
   * @return \Papaya\Ui\Dialog
   */
  protected function createDialog() {
    $dialog = new \Papaya\Ui\Dialog();
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
    if (NULL !== $hide) {
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
    if (NULL !== $reset) {
      $this->_resetAfterSuccess = (bool)$reset;
    }
    return $this->_resetAfterSuccess;
  }

  /**
   * Reset the action, (set the dialog to NULL again)
   */
  public function reset() {
    if (NULL !== $this->_dialog) {
      $this->_dialog = NULL;
    }
  }
}
