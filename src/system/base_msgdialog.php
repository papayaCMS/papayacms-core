<?php
/**
* Delegation-class for Message-Boxen
*
* <code>
* $hidden = array(
*   'cmd' => $this->params['cmd'],
*   'id' => $this->params['id'], // Of course you don't use 'id'. Name it!
*   'confirm' => 1
* );
* $msg = $this->_gtf('Do you really want to delete "%s" (#%d)?', array($name, $id));
* // $type may be 'question', 'hint', 'info' or 'error'
* $this->dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, $type);
* $this->dialog->buttonTitle = 'Delete'; // don't use _gt(), it will be applied automatically
* $this->layout->add($this->dialog->getMsgDialog());
* </code>
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Administration
* @version $Id: base_msgdialog.php 39360 2014-02-26 15:04:45Z weinert $
*/

/**
* Delegation-class for Message-Boxen
*
* @package Papaya
* @subpackage Administration
*/
class base_msgdialog extends base_object {

  /**
  * base link
  * @var string $baseLink
  */
  var $baseLink = '';

  /**
  * reference to owner
  * @var object $owner
  */
  var $owner = NULL;

  /**
  * paramter name array
  * @var array $paramName
  */
  var $paramName = '';

  /**
  * handle hidden form objects
  * @var array $hidden
  */
  var $hidden = NULL;

  /**
  * dialog title
  * @var string $dialogTitle
  */
  var $dialogTitle = 'Edit';

  /**
  * button title
  * @var string $buttonTitle
  */
  var $buttonTitle = 'Save';

  /**
  * maximum tokens per key
  * @var integer
  */
  var $maxTokensPerKey = 20;

  /**
  * (de)activate token use
  * @var boolean
  */
  var $useToken = TRUE;

  /**
  * CSRF tokens manager
  * @var PapayaUiTokens
  */
  protected $_tokens = NULL;

  /**
   * PHP 5 constructor
   *
   * @param object $aOwner
   * @param string $paramName
   * @param array $hidden
   * @param string $msg
   * @param int|string $type
   * @access public
   */
  function __construct($aOwner, $paramName, array $hidden, $msg = '', $type = 'question') {
    $this->owner = $aOwner;
    $this->paramName = $paramName;
    $this->hidden = $hidden;
    $this->messageString = $msg;
    $this->msgType = $type;
  }

  /**
  * Get dialog form action
  *
  * @return string
  */
  protected function getAction() {
    if (empty($this->baseLink)) {
      $this->baseLink = $this->getBaseLink();
    }
    return $this->baseLink;
  }

  /**
  * Get message dialog
  *
  * @access public
  * @return string $result XML
  */
  function getMsgDialog() {
    $name = (isset($this->dialogId) && $this->dialogId != '')
      ? sprintf(' name="%s"', $this->dialogId) : '';
    $result = sprintf(
      '<msgdialog title="%s" action="%s" type="%s"%s>',
      papaya_strings::escapeHTMLChars($this->getCaption()),
      papaya_strings::escapeHTMLChars($this->getAction()),
      papaya_strings::escapeHTMLChars($this->msgType),
      $name
    );
    $result .= $this->getHidden();
    $result .= '<message>';
    $result .= papaya_strings::escapeHTMLChars($this->messageString);
    $result .= '</message>';
    $result .= sprintf(
      '<dlgbutton value="%s" />'.LF,
      papaya_strings::escapeHTMLChars($this->_gt($this->buttonTitle))
    );
    $result .= '</msgdialog>';
    return $result;
  }

  /**
  * Get hidden
  *
  * @access public
  * @return string $result
  */
  public function getHidden() {
    $result = '';
    if ($this->useToken) {
      if ($token = $this->tokens()->create($this->owner)) {
        $result .= sprintf(
          '<input type="hidden" name="%s[token]" value="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($token)
        );
      }
    }
    if (isset($this->hidden) && is_array($this->hidden)) {
      foreach ($this->hidden as $name => $value) {
        if (is_array($value)) {
          foreach ($value as $idx => $subValue) {
            $result .= sprintf(
              '<input type="hidden" name="%s[%s][%s]" value="%s"/>'.LF,
              papaya_strings::escapeHTMLChars($this->paramName),
              papaya_strings::escapeHTMLChars($name),
              papaya_strings::escapeHTMLChars($idx),
              papaya_strings::escapeHTMLChars($subValue)
            );
          }
        } else {
          $result .= sprintf(
            '<input type="hidden" name="%s[%s]" value="%s"/>'.LF,
            papaya_strings::escapeHTMLChars($this->paramName),
            papaya_strings::escapeHTMLChars($name),
            papaya_strings::escapeHTMLChars($value)
          );
        }
      }
    }
    return $result;
  }

  /**
  * Get caption
  *
  * @access public
  * @return string
  */
  function getCaption() {
    switch($this->msgType) {
    case 'question' :
      return $this->_gt('Question');
    case 'hint' :
      return $this->_gt('Hint');
    case 'info' :
      return $this->_gt('Information');
    case 'error' :
      return $this->_gt('Error');
    default :
      return $this->dialogTitle;
    }
  }

  /**
  * Load parameters
  *
  * @access public
  */
  function loadParams() {
    $this->initializeParams();
  }

  /**
  * check dialog input (compare parameters with hidden fields)
  *
  * @access public
  * @return boolean
  */
  function checkDialogInput() {
    if ($this->useToken && !$this->checkDialogToken()) {
      return FALSE;
    }
    if (isset($this->hidden) && is_array($this->hidden)) {
      foreach ($this->hidden as $name => $expected) {
        if (!isset($this->params[$name])) {
          return FALSE;
        } elseif ($this->params[$name] != $expected) {
          return FALSE;
        }
      }
    }
    return TRUE;
  }

  /**
  * Getter/Setter for csrf token manager including implizit create
  *
  * @param PapayaUiTokens $tokens
  * @return PapayaUiTokens
  */
  protected function tokens(PapayaUiTokens $tokens = NULL) {
    if (isset($tokens)) {
      $this->_tokens = $tokens;
    } elseif (is_null($this->_tokens)) {
      $this->_tokens = new PapayaUiTokens();
    }
    return $this->_tokens;
  }

  /**
   * check dialog token
   *
   * @access public
   * @param string $token
   * @return boolean
   */
  function checkDialogToken($token = '') {
    if (empty($token) && isset($this->params['token'])) {
      $token = $this->params['token'];
    }
    return $this->tokens()->validate((string)$token, $this->owner);
  }

  /**
  * Get a dialog token for this dialog for external use
  * @return string
  */
  function getDialogToken() {
    return $this->tokens()->create($this->owner);
  }
}

