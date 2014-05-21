<?php
/**
* Confirmation dialog control
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
* @version $Id: Confirmation.php 39725 2014-04-07 17:19:34Z weinert $
*/

/**
* Confirmation dialog control
*
* A interface control displaying a confirmation dialog and handle it.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogConfirmation extends PapayaUiDialog {

  /**
  * Dialog form method - should always be post for confirmation dialogs
  * @var NULL|integer
  */
  protected $_method = self::METHOD_POST;

  /**
  * Dialog message
  *
  * @var string|PapayaUiString
  */
  protected $_message = 'Confirm action?';

  /**
  * Dialog button caption
  *
  * @var string|PapayaUiString
  */
  protected $_button = 'Yes';

  /**
  * Initialize object, set owner, field data and parameters group
  *
  * @param object $owner
  * @param PapayaRequestParameters|array $hiddenFields
  * @param string $parameterGroup
   */
  public function __construct($owner, $hiddenFields, $parameterGroup = NULL) {
    parent::__construct($owner);
    if (isset($parameterGroup)) {
      $this->parameterGroup($parameterGroup);
    }
    $this->hiddenFields()->merge($hiddenFields);
  }

  /**
  * Check if this dialog was submitted
  *
  * @return boolean
  */
  public function isSubmitted() {
    if ($this->isPostRequest()) {
      return $this->parameters()->get('confirmation') == $this->hiddenFields()->getChecksum();
    }
    return FALSE;
  }

  /**
  * Validate dialog (check the dialog token)
  *
  * @return boolean
  */
  public function execute() {
    if (is_null($this->_executionResult)) {
      if ($this->isSubmitted()) {
        $this->_executionResult = $this->tokens()->validate(
          $this->parameters()->get('token'), $this->_owner
        );
      } else {
        $this->_executionResult = FALSE;
      }
    }
    return $this->_executionResult;
  }

  /**
   * Append dialog elements to dom
   *
   * @param PapayaXmlElement $parent
   * @return NULL|\PapayaXmlElement|void
   */
  public function appendTo(PapayaXmlElement $parent) {
    $dialog = $parent->appendElement(
      'confirmation-dialog',
      array('action' => $this->action(), 'method' => 'post')
    );
    $this->appendHidden($dialog, $this->hiddenValues());
    $this->appendHidden($dialog, $this->hiddenFields(), $this->parameterGroup());
    $values = new PapayaRequestParameters(
      array(
        'confirmation' => $this->hiddenFields()->getCheckSum(),
        'token' => $this->tokens()->create($this->_owner)
      )
    );
    $this->appendHidden($dialog, $values, $this->parameterGroup());
    $dialog->appendElement('message', array(), (string)$this->_message);
    $dialog->appendElement(
      'dialog-button',
      array('type' => 'submit', 'caption' => (string)$this->_button)
    );
    $dialog->appendTo($parent);
    return $dialog;
  }

  /**
  * Set dialog message text
  *
  * @param string|PapayaUiString $text
  */
  public function setMessageText($text) {
    $this->_message = $text;
  }

  /**
  * Set dialog button caption
  *
  * @param string|PapayaUiString $caption
  */
  public function setButtonCaption($caption) {
    $this->_button = $caption;
  }
}