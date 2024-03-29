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

use Papaya\Request;
use Papaya\UI;
use Papaya\XML;

/**
 * Confirmation dialog control
 *
 * A interface control displaying a confirmation dialog and handle it.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Confirmation extends UI\Dialog {
  /**
   * Dialog form method - should always be post for confirmation dialogs
   *
   * @var null|int
   */
  protected $_method = self::METHOD_POST;

  /**
   * Dialog message
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_message = 'Confirm action?';

  /**
   * Dialog button caption
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_button = 'Yes';

  /**
   * Initialize object, set owner, field data and parameters group
   *
   * @param object $owner
   * @param Request\Parameters|array $hiddenFields
   * @param string $parameterGroup
   */
  public function __construct($owner, $hiddenFields, $parameterGroup = NULL) {
    parent::__construct($owner);
    if (NULL !== $parameterGroup) {
      $this->parameterGroup($parameterGroup);
    }
    $this->hiddenFields()->merge($hiddenFields);
  }

  /**
   * Check if this dialog was submitted
   *
   * @return bool
   */
  public function isSubmitted() {
    if ($this->isPostRequest()) {
      return $this->parameters()->get('confirmation') === $this->hiddenFields()->getChecksum();
    }
    return FALSE;
  }

  /**
   * Validate dialog (check the dialog token)
   *
   * @return bool
   */
  public function execute() {
    if (NULL === $this->_executionResult) {
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
   * @param XML\Element $parent
   *
   * @return null|XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $dialog = $parent->appendElement(
      'confirmation-dialog',
      ['action' => $this->action(), 'method' => 'post']
    );
    $this->appendHidden($dialog, $this->hiddenValues());
    $this->appendHidden($dialog, $this->hiddenFields(), $this->parameterGroup());
    $values = new Request\Parameters(
      [
        'confirmation' => $this->hiddenFields()->getChecksum(),
        'token' => $this->tokens()->create($this->_owner)
      ]
    );
    $this->appendHidden($dialog, $values, $this->parameterGroup());
    $dialog->appendElement('message', [], (string)$this->_message);
    $dialog->appendElement(
      'dialog-button',
      ['type' => 'submit', 'caption' => (string)$this->_button]
    );
    $dialog->appendTo($parent);
    return $dialog;
  }

  /**
   * Set dialog message text
   *
   * @param string|\Papaya\UI\Text $text
   */
  public function setMessageText($text) {
    $this->_message = $text;
  }

  public function getMessageText() {
    return $this->_message;
  }

  /**
   * Set dialog button caption
   *
   * @param string|\Papaya\UI\Text $caption
   */
  public function setButtonCaption($caption) {
    $this->_button = $caption;
  }

  public function getButtonCaption(): string {
    return $this->_button;
  }
}
