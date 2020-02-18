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
* Delegation-class for dialogs
*
* @package Papaya
* @subpackage Controls
*/
class base_dialog extends base_object {

  /**
   * base link
   * @var string $baseLink
   */
  var $baseLink = '';

  /**
  * Input field width class
  * x-small, small, medium, large, x-large, xx-large
  *
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'medium';

  /**
  * reference to owner
  * @var object $owner
  */
  var $owner = NULL;

  /**
  * parameter name array
  * @var array $paramName
  */
  var $paramName = '';

  /**
  * fields
  *
  * Name => (
  *   [0] => label
  *   [1] => control function
  *   [2] => obligation field (TRUE/FALSE)
  *   [3] => type (input, combo, textarea)
  *   [4] => params for type (maximum length, value-hash, rows)
  *   [5] => quickinformation
  *   [6] => default value
  *   [7] => align
  * )
  *
  * @var array $fields
  */
  var $fields = NULL;

  /**
  * data
  * @var array $data
  */
  var $data = NULL;

  /**
  * handle hidden form objects
  * @var array $hidden
  */
  var $hidden = NULL;

  /**
  * buttons
  * @var array $buttons
  */
  var $buttons = array();

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
  * optional dialog icon
  * @var string
  */
  var $dialogIcon = '';

  /**
  * dialog hint
  * @var string $dialogHint
  */
  var $dialogHint = '';

  /**
  * dialog text for yes
  * @var string $textYes
  */
  var $textYes = 'Yes';

  /**
  * dialog text for no
  * @var string $textNo
  */
  var $textNo = 'No';

  /**
  * dialog id
  * @var string $dialogId
  */
  var $dialogId = '';

  /**
  * dialog http data transfer method (post or get)
  * @var string $dialogId
  */
  var $dialogMethod = 'post';

  /**
  * double dialog buttons
  * @var boolean $dialogDoubleButtons
  */
  var $dialogDoubleButtons = FALSE;

  /**
  * hide dialog buttons
  * @var boolean $dialogHideButtons
  */
  var $dialogHideButtons = FALSE;

  /**
  * Adds marker for change protection (javascript)
  * @var boolean
  */
  var $dialogProtectChanges = FALSE;

  /**
  * input errors
  * @var array $inputErrors
  */
  var $inputErrors = NULL;

  /**
  * use csrf tokens
  * @var boolean
  */
  var $useToken = TRUE;

  /**
   * @var string
   * @deprecated
   */
  public $tableTopics;

  /**
  * CSRF tokens manager
  *
  * @var \Papaya\UI\Tokens
  */
  protected $_tokens = NULL;

  /**
  * maximum tokens per key
  * @var integer
  */
  var $maxTokensPerKey = 20;

  /**
  * contains upload fields?
  * @var boolean
  */
  var $uploadFiles = FALSE;

  /**
  * Translation of captions active/inactive
  * To prevent captions from being translated automatically,
  * set this variable to FALSE. Because most callers rely on
  * automatic translation, it is turned on by default.
  * @var boolean
  */
  var $translate = TRUE;

  /**
  * ignore if the send value equals the caption of the field
  * @var boolean
  */
  var $ignoreCaptionValues = FALSE;

  /**
  * Dialog target
  * @var string
  */
  var $target = NULL;

  /**
  * Constructor
  *
  * @param object $aOwner
  * @param string $paramName
  * @param array $fields
  * @param array $data
  * @param array $hidden
  * @access public
  */
  public function __construct($aOwner, $paramName, $fields, $data = array(), $hidden = array()) {
    $this->owner = $aOwner;
    $this->paramName = $paramName;
    $this->fields = $fields;
    $this->data = $data;
    $this->hidden = $hidden;
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
  * Load parameters
  *
  * @access public
  */
  function loadParams() {
    $this->initializeParams();
  }

  /**
   * Local translation wrapper. Overloads _gt method of super class.
   * Translates given phrases only, when $this->translate has not been set to FALSE.
   *
   * @param string $phrase
   * @param string|NULL $module
   * @return string $phrase OR translation($phrase)
   */
  function _gt($phrase, $module = NULL) {
    if ($this->translate) {
      return parent::_gt($phrase, $module);
    } else {
      return $phrase;
    }
  }

  /**
   * add button
   *
   * @param string $buttonName
   * @param string $buttonTitle
   * @param bool $noTranslation
   * @param string $type
   * @param string $align
   * @access public
   */
  function addButton(
    $buttonName, $buttonTitle, $noTranslation = FALSE, $type = 'submit', $align = 'left'
  ) {
    if (!$noTranslation) {
      $buttonTitle = $this->_gt($buttonTitle);
    }
    if (!in_array($align, array('left', 'right', 'center'))) {
      $align = 'left';
    }
    $this->buttons[$buttonName] = array(
      'title' => $buttonTitle,
      'type' => $type,
      'align' => $align
    );
  }

  /**
  * Get dialog XML
  *
  * @access public
  * @return string '' or XML
  */
  function getDialogXML() {
    $result = '';
    if (isset($this->fields) && is_array($this->fields)) {
      $dlgHint = (trim($this->dialogHint) != '')
        ? sprintf(' hint="%s"', papaya_strings::escapeHTMLChars($this->dialogHint)) : '';
      $dlgIcon = (trim($this->dialogIcon) != '')
        ? sprintf(' icon="%s"', papaya_strings::escapeHTMLChars($this->dialogIcon)) : '';
      if (defined('PAPAYA_PROTECT_FORM_CHANGES') &&
          PAPAYA_PROTECT_FORM_CHANGES &&
          $this->dialogProtectChanges) {
        $protectChanges = ' protected="protected"';
      } else {
        $protectChanges = '';
      }
      $result = sprintf(
        '<dialog title="%s" action="%s" method="%s" width="100%%" %s%s%s%s%s%s%s>'.LF,
        papaya_strings::escapeHTMLChars($this->dialogTitle),
        papaya_strings::escapeHTMLChars($this->getAction()),
        papaya_strings::escapeHTMLChars($this->dialogMethod),
        ($this->dialogId)
          ? sprintf(' name="%s"', $this->dialogId) : '',
        $dlgHint,
        $dlgIcon,
        ($this->uploadFiles) ? ' enctype="multipart/form-data"' : '',
        !empty($this->dialogId)
          ? ' id="'.papaya_strings::escapeHTMLChars($this->dialogId).'"' : '',
        $protectChanges,
        !empty($this->target)
          ? ' target="'.papaya_strings::escapeHTMLChars($this->target).'"' : ''
      );
      $result .= $this->getHidden();
      switch($this->inputFieldSize) {
      case 'x-small':
        $fieldSizeClass = 'XSmall';
        break;
      case 'small':
        $fieldSizeClass = 'Small';
        break;
      case 'large':
        $fieldSizeClass = 'Large';
        break;
      case 'x-large':
        $fieldSizeClass = 'XLarge';
        break;
      case 'xx-large':
        $fieldSizeClass = 'XXLarge';
        break;
      default:
        $fieldSizeClass = 'Medium';
        break;
      }
      $inGroup = FALSE;
      $hasGroups = isset($this->fields[0]);
      $result .= sprintf(
        '<lines class="dialog%s">'.LF, papaya_strings::escapeHTMLChars($fieldSizeClass)
      );
      foreach ($this->fields as $key => $val) {
        if (isset($this->params[$key]) && $val[3] != 'info') {
          $data = $this->params[$key];
        } elseif (isset($this->data[$key])) {
          if (empty($this->data[$key]) && $val[3] == 'info') {
            $data = (isset($val[6])) ? $this->prepareCaptionString($val[6]) : '';
          } else {
            $data = $this->data[$key];
          }
        } else {
          $data = (isset($val[6])) ? $this->prepareCaptionString($val[6]) : '';
        }
        if (isset($val) && is_array($val)) {
          if ($hasGroups && !$inGroup) {
            $result .= '<linegroup>'.LF;
            $inGroup = TRUE;
          }
          if ($val[3] == 'yesno') {
            $align = ' align="left"';
          } else {
            $align = (isset($val[7]))
              ? sprintf(' align="%s"', papaya_strings::escapeHTMLChars($val[7])) : '';
          }
          $hint = (isset($val[5]) && trim($val[5]) != '')
            ? sprintf(
              ' hint="%s"',
              papaya_strings::escapeHTMLChars(
                $this->_gt($this->prepareCaptionString($val[5]))
              )
            )
            : '';
          if (isset($this->inputErrors[$key]) && $this->inputErrors[$key] == 1) {
            $error = ' error="error"';
          } else {
            $error = '';
          }
          if ($str = $this->getDlgElement($key, $val, $data)) {
            $result .= sprintf(
              '<line caption="%s" fid="%s" %s%s%s>%s</line>'.LF,
              papaya_strings::escapeHTMLChars($this->_gt($val[0])),
              papaya_strings::escapeHTMLChars($key),
              $hint,
              $error,
              $align,
              $str
            );
          }
        } elseif (is_string($val) && $hasGroups) {
          if ($inGroup) {
            $result .= '</linegroup>'.LF;
          }
          $result .= sprintf(
            '<linegroup caption="%s">',
            papaya_strings::escapeHTMLChars($this->_gt($val))
          );
          $inGroup = TRUE;
        }
      }
      if ($inGroup && $hasGroups) {
        $result .= '</linegroup>'.LF;
      }
      $result .= '</lines>'.LF;
      $result .= $this->getDialogButtonXML();
      $result .= '</dialog>'.LF;
    }
    return $result;
  }

  /**
  * Get XML for dialog buttons
  * @return string
  */
  function getDialogButtonXML() {
    $result = '';
    if (!$this->dialogHideButtons) {
      $result .= sprintf(
        '<dlgbutton value="%s" %s/>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt($this->buttonTitle)),
        (($this->dialogDoubleButtons) ? ' sandwich="sandwich"' : '')
      );
      if (isset($this->buttons) && is_array($this->buttons) &&
          count($this->buttons) > 0) {
        foreach ($this->buttons as $buttonName => $buttonData) {
          $result .= sprintf(
            '<dlgbutton type="%s" name="%s[%s]" value="%s" align="%s" %s/>'.LF,
            papaya_strings::escapeHTMLChars($buttonData['type']),
            papaya_strings::escapeHTMLChars($this->paramName),
            papaya_strings::escapeHTMLChars($buttonName),
            papaya_strings::escapeHTMLChars($buttonData['title']),
            papaya_strings::escapeHTMLChars($buttonData['align']),
            (($this->dialogDoubleButtons) ? ' sandwich="sandwich"' : '')
          );
        }
      }
    }
    return $result;
  }

  /**
  * Prepare stirng for use in captions (replace linebreaks)
  * @param string $str
  * @return string
  */
  function prepareCaptionString($str) {
    return preg_replace('~[\r\n]+\s*~', ' ', $str);
  }

  /**
  * Get hidden
  *
  * @access public
  * @return string '' or HTML
  */
  function getHidden() {
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
            if (is_array($subValue)) {
              foreach ($subValue as $idy => $subValueY) {
                $result .= sprintf(
                  '<input type="hidden" name="%s[%s][%s][%s]" value="%s"/>'.LF,
                  papaya_strings::escapeHTMLChars($this->paramName),
                  papaya_strings::escapeHTMLChars($name),
                  papaya_strings::escapeHTMLChars($idx),
                  papaya_strings::escapeHTMLChars($idy),
                  papaya_strings::escapeHTMLChars($subValueY)
                );
              }
            } else {
              $result .= sprintf(
                '<input type="hidden" name="%s[%s][%s]" value="%s"/>'.LF,
                papaya_strings::escapeHTMLChars($this->paramName),
                papaya_strings::escapeHTMLChars($name),
                papaya_strings::escapeHTMLChars($idx),
                papaya_strings::escapeHTMLChars($subValue)
              );
            }
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
  * get XML dialog element
  *
  * @param string $name
  * @param array $element
  * @param string $data
  * @access public
  * @return string
  */
  function getDlgElement($name, $element, $data) {
    $result = '';
    if (isset($element) && is_array($element)) {
      $elementId = (isset($this->dialogId) && $this->dialogId)
        ? sprintf(
          ' id="%s_%s"',
          papaya_strings::escapeHTMLChars($this->dialogId),
          papaya_strings::escapeHTMLChars($name)
        )
        : '';
      // Set disabled or readonly attribute
      if (strpos($element[3], 'disabled_') === 0) {
        $elementType = substr($element[3], strlen('disabled_'));
        $disabled = ' disabled="disabled"';
      } elseif (strpos($element[3], 'readonly_') === 0) {
        $elementType = substr($element[3], strlen('readonly_'));
        $disabled = ' readonly="readonly"';
      } else {
        $elementType = $element[3];
        $disabled = '';
      }
      $mandatory = ($element[2] == TRUE) ?
        ' mandatory="true"' : ' mandatory="false"';
      $inputSize = $this->getInputSize();
      switch ($elementType) {
      case 'info' :
        $result .=
          '<div class="dialogInfo dialogScale">'.
            papaya_strings::escapeHTMLChars($data).
            '</div>';
        break;
      case 'input' :
        $result .= sprintf(
          '<input type="text" name="%s[%s]" value="%s" maxlength="%d"'.
            ' class="dialogInput dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          papaya_strings::escapeHTMLChars($element[4]),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'input_counter' :
        $result .= sprintf(
          '<input type="text" name="%s[%s]" value="%s" maxlength="%d"'.
            ' class="dialogInput dialogScale dialogInputCounter" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          papaya_strings::escapeHTMLChars($element[4]),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'pageid' :
        $result .= sprintf(
          '<input type="text" name="%s[%s]" value="%s" maxlength="%d"'.
            ' class="dialogPageId dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          (isset($element[4]) ? papaya_strings::escapeHTMLChars($element[4]) : 10),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'geopos' :
        $result .= sprintf(
          '<input type="text" name="%s[%s]" value="%s" maxlength="%d"'.
            ' class="dialogGeoPos dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          200,
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'date' :
      case 'datetime' :
        $result .= sprintf(
          '<input type="text" name="%s[%s]" value="%s" maxlength="40"'.
            ' class="%s dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          ($elementType == 'datetime') ? 'dialogInputDateTime' : 'dialogInputDate',
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'color' :
        $result .= sprintf(
          '<input type="text" name="%s[%s]" value="%s" maxlength="40"'.
            ' class="dialogInputColor dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'imagefile' :
      case 'file' :
        $result .= sprintf(
          '<input type="file" name="%s[%s]" value="%s" maxlength="%d"'.
            ' class="dialogFileSelect dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          papaya_strings::escapeHTMLChars(isset($element[4]) ? $element[4] : 2000),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'checkbox' :
        $selected = ($data != '' && $data) ? ' checked="checked"' : '';
        $value = (isset($element[4]) && $element[4])
          ? papaya_strings::escapeHTMLChars($element[4]) : 'Yes';
        $result .= sprintf(
          '<input type="checkbox" name="%s[%s]" value="%s"'.
            ' class="dialogCheckbox" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($value),
          papaya_strings::escapeHTMLChars($name),
          $selected,
          $disabled,
          $mandatory
        );
        break;
      case 'password' :
        $result .= sprintf(
          '<input type="password" name="%s[%s]" value="%s" maxlength="%d"'.
            ' class="dialogPassword dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          papaya_strings::escapeHTMLChars(isset($element[4]) ? $element[4] : 1000),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'textarea' :
        $result .= sprintf(
          '<textarea name="%s[%s]" rows="%s" cols="%d"'.
            ' class="dialogTextarea dialogScale" wrap="virtual"'.
            ' fid="%s"%s%s%s>%s</textarea>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars(isset($element[4]) ? $element[4] : 10),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory,
          papaya_strings::escapeHTMLChars($data)
        );
        break;
      case 'image' :
        if (FALSE !== strpos($data, '<')) {
          $dataArr = array(
            'src' => '',
            'width' => 0,
            'height' => 0
          );
          $attributePattern = '~(src|width|height)=("|\')([^"]+)(\\2)~i';
          if (preg_match_all($attributePattern, $data, $regs, PREG_SET_ORDER)) {
            foreach ($regs as $reg) {
              $dataArr[strtolower($reg[1])] = $reg[3];
            }
          }
          $data = implode(',', $dataArr);
        }
        $result .= sprintf(
          '<input type="text" name="%s[%s]" value="%s" maxlength="200"'.
            ' class="dialogImage dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'mediafile' :
        if (FALSE !== strpos($data, '<')) {
          $dataArr = array(
            'src' => ''
          );
          if (preg_match_all('~(src)=("|\')([^"]+)(\\2)~i', $data, $regs, PREG_SET_ORDER)) {
            foreach ($regs as $reg) {
              $dataArr[strtolower($reg[1])] = $reg[3];
            }
          }
          $data = implode(',', $dataArr);
        }
        $result .= sprintf(
          '<input type="text" name="%s[%s]" value="%s" maxlength="200"'.
            ' class="dialogMediaFile dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'imagefixed' :
      case 'mediaimage' :
        if (FALSE !== strpos($data, '<')) {
          if (preg_match('~(src)=("|\')([^"]+)(\\2)~i', $data, $regs)) {
            $data = $regs[3];
          } else {
            $data = '';
          }
        }
        $result .= sprintf(
          '<input type="text" name="%s[%s]" value="%s" maxlength="200"'.
            ' class="dialogFixedImage dialogScale" size="%d" fid="%s"%s%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($data),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $disabled,
          $mandatory
        );
        break;
      case 'richtext' :
        $result .= sprintf(
          '<textarea name="%s[%s]" rows="%s" cols="70"'.
            ' class="dialogRichtext dialogScale" wrap="virtual"'.
            ' fid="%s"%s%s>%s</textarea>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          ($element[4] > 5) ? papaya_strings::escapeHTMLChars($element[4]) : 5,
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $mandatory,
          papaya_strings::escapeHTMLChars($data)
        );
        break;
      case 'simplerichtext' :
        $result .= sprintf(
          '<textarea name="%s[%s]" rows="%s" cols="%d"'.
            ' class="dialogSimpleRichtext dialogScale" wrap="virtual"'.
            ' fid="%s"%s%s>%s</textarea>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars(isset($element[4]) ? $element[4] : 4),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $mandatory,
          papaya_strings::escapeHTMLChars($data)
        );
        break;
      case 'individualrichtext' :
        $result .= sprintf(
          '<textarea name="%s[%s]" rows="%s" cols="%d"'.
            ' class="dialogIndividualRichtext dialogScale" wrap="virtual"'.
            ' fid="%s"%s%s>%s</textarea>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($element[4]),
          papaya_strings::escapeHTMLChars($inputSize),
          papaya_strings::escapeHTMLChars($name),
          $elementId,
          $mandatory,
          papaya_strings::escapeHTMLChars($data)
        );
        break;
      case 'combo' :
        $result .= $this->getComboBoxXML(
          $name, $element, $data, $elementId, $disabled, $mandatory
        );
        break;
      case 'translatedcombo':
        $result .= $this->getComboBoxXML(
          $name, $element, $data, $elementId, $disabled, $mandatory, TRUE
        );
        break;
      case 'radio' :
        if (isset($element[4]) && is_array($element[4])) {
          foreach ($element[4] as $key => $val) {
            if ($key == $data) {
              $selected = ' checked="checked"';
            } else {
              $selected = '';
            }
            $result .= sprintf(
              '<input class="dialogRadio" type="radio"'.
                ' name="%s[%s]" value="%s"%s%s>%s</input>'.LF,
              papaya_strings::escapeHTMLChars($this->paramName),
              papaya_strings::escapeHTMLChars($name),
              papaya_strings::escapeHTMLChars($key),
              $selected,
              $mandatory,
              papaya_strings::escapeHTMLChars($val)
            );
          }
        }
        break;
      case 'filecombo' :
        $result .= base_dialog::getFileCombo(
          $name,
          $element,
          $data,
          $element[4][0],
          $element[4][1],
          empty($element[4][2]) ? FALSE : (bool)$element[4][2],
          empty($element[4][3]) ? '' : (string)$element[4][3]
        );
        break;
      case 'dircombo' :
        $result .= base_dialog::getDirectoryCombo(
          $name,
          $element,
          $data,
          $element[4][0],
          empty($element[4][1]) ? '' : $element[4][1],
          empty($element[4][2]) ? '' : $element[4][2]
        );
        break;
      case 'function' :
        if (method_exists($this->owner, $element[4])) {
          if ($str = call_user_func(array($this->owner, $element[4]), $name, $element, $data)) {
            $result .= $str;
          }
        }
        break;
      case 'yesno' :
        if ($data == '0') {
          $yesChecked = '';
          $noChecked = ' checked="checked"';
        } else {
          $yesChecked = ' checked="checked"';
          $noChecked = '';
        }
        $result .= sprintf(
          '<input type="radio" value="1" name="%s[%s]" class="dialogRadio"'.
            ' fid="%s"%s>%s</input>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($name),
          $yesChecked,
          papaya_strings::escapeHTMLChars($this->_gt($this->textYes))
        );
        $result .= sprintf(
          '<input type="radio" value="0" name="%s[%s]"'.
            ' class="dialogRadio" fid="%s"%s>%s</input>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($name),
          $noChecked,
          papaya_strings::escapeHTMLChars($this->_gt($this->textNo))
        );
        break;
      case 'checkgroup' :
        if (isset($element[4]) && is_array($element[4])) {
          foreach ($element[4] as  $idx => $item) {
            if (is_array($data) && in_array($idx, $data)) {
              $selected = ' checked="checked"';
            } else {
              $selected = '';
            }
            $result .= sprintf(
              '<input type="checkbox" value="%s" name="%s[%s][]"'.
                ' class="dialogCheckbox" fid="%s"%s%s />%s'.LF,
              papaya_strings::escapeHTMLChars($idx),
              papaya_strings::escapeHTMLChars($this->paramName),
              papaya_strings::escapeHTMLChars($name),
              papaya_strings::escapeHTMLChars($name),
              $selected,
              $mandatory,
              papaya_strings::escapeHTMLChars($item)
            );
          }
        }
        break;
      case 'lookup_combo' :
        if ($str = $this->getFieldCombo($name, $element, $data)) {
          $result .= $str;
        }
        break;
      case 'lookup_radiogroup' :
        if ($str = $this->getFieldRadioGroup($name, $element, $data)) {
          $result .= $str;
        }
        break;
      case 'lookup_checkgroup' :
        if ($str = $this->getFieldCheckGroup($name, $element, $data)) {
          $result .= $str;
        }
        break;
      case 'captcha' :
        /* random id berechnen (session var identifier) */
        srand((double)microtime() * 1000000);
        $randId = md5(uniqid(rand()));
        /* hidden-feld mit id ausgeben */
        $result .= sprintf(
          '<input type="hidden" name="%s[%s][captchaident]"'.
            ' class="dialogCaptcha dialogScale" fid="%s_hidden" value="%s"></input>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($randId)
        );
        /* input-feld ausgeben */
        $result .= sprintf(
          '<input type="text" name="%s[%s][captchaanswer]"'.
            ' class="dialogCaptcha dialogScale" size="%d" fid="%s"%s></input>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          (int)$inputSize,
          papaya_strings::escapeHTMLChars($name),
          $mandatory
        );
        /* img-tag ausgeben */
        $result .= sprintf(
          '<img src="%s.image.jpg?img[identifier]=%s" />'.LF,
          papaya_strings::escapeHTMLChars($element[4]),
          papaya_strings::escapeHTMLChars($randId)
        );
        break;
      case 'mediafolder':
        $mediadb = new base_mediadb();
        $mediadb->paramName = $this->paramName;
        if ($str = $mediadb->callbackFolders($name, $element, $data)) {
          $result .= $str;
        }
        break;
      }
    }
    return $result;
  }

  /**
  * Returns the xml structure for a combo box dialog element. If the values need to be translated,
  * just call this method using TRUE as seventh argument.
  *
  * @param string $name value of the name attribute of the form field
  * @param array $element edit field object
  * @param string $data object containing values stored in the database
  * @param integer $elementId id of the field
  * @param string $disabled  xml attribute marking deactivated fields
  * @param string $mandatory xml attribute marking mandatory fields
  * @param boolean $translate TRUE iff the combo box values should be translated, otherwise FALSE
  * @access public
  * @return string
  */
  function getComboBoxXML(
    $name, $element, $data, $elementId, $disabled, $mandatory, $translate = FALSE
  ) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale" fid="%s"%s%s%s>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name),
      papaya_strings::escapeHTMLChars($name),
      $elementId,
      $disabled,
      $mandatory
    );
    if (isset($element[4]) && is_array($element[4])) {
      foreach ($element[4] as $key => $val) {
        if (is_array($val)) {
          $result .= sprintf(
            '<optgroup label="%s">'.LF,
            papaya_strings::escapeHTMLChars($key)
          );
          foreach ($val as $subKey => $subVal) {
            if ((string)$subKey == (string)$data) {
              $selected = ' selected="selected"';
            } else {
              $selected = '';
            }
            if ($translate) {
              $result .= sprintf(
                '<option value="%s"%s>%s</option>'.LF,
                papaya_strings::escapeHTMLChars($subKey),
                $selected,
                papaya_strings::escapeHTMLChars($this->_gt($subVal))
              );
            } else {
              $result .= sprintf(
                '<option value="%s"%s>%s</option>'.LF,
                papaya_strings::escapeHTMLChars($subKey),
                $selected,
                papaya_strings::escapeHTMLChars($subVal)
              );
            }
          }
          $result .= '</optgroup>';
        } else {
          if ((string)$key == (string)$data) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          if ($translate) {
            $result .= sprintf(
              '<option value="%s"%s>%s</option>'.LF,
              papaya_strings::escapeHTMLChars($key),
              $selected,
              papaya_strings::escapeHTMLChars($this->_gt($val))
            );
          } else {
            $result .= sprintf(
              '<option value="%s"%s>%s</option>'.LF,
              papaya_strings::escapeHTMLChars($key),
              $selected,
              papaya_strings::escapeHTMLChars($val)
            );
          }
        }
      }
    }
    return ($result.'</select>'.LF);
  }

  /**
  * the size attribute of the input tags depends on the dialog size
  *
  * @access public
  * @return integer
  */
  function getInputSize() {
    switch($this->inputFieldSize) {
    case 'x-small':
      return 10;
    case 'small':
      return 15;
    case 'large':
      return 50;
    case 'x-large':
    case 'xx-large':
      return 70;
    default:
      return 25;
    }
  }

  /**
   * Get file combo
   *
   * @param string $name
   * @param array $element
   * @param array $data
   * @param string $path
   * @param string $regEx
   * @param bool $emptyAllowed
   * @param string $baseDirectory
   * @access public
   * @return string
   */
  function getFileCombo(
    $name, $element, $data, $path, $regEx, $emptyAllowed = FALSE, $baseDirectory = ''
  ) {

    $result = '';
    switch ($baseDirectory) {
    case 'theme':
      $dir = $_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_WEB.'papaya-themes/'.$path;
      break;
    case 'current_theme':
      $themeHandler = new \Papaya\Theme\Handler();
      $dir = $themeHandler->getLocalThemePath().$path;
      break;
    case 'page':
      $dir = $_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_WEB.$path;
      break;
    case 'admin':
      $dir = $_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_WEB;
      if (substr($dir, -1) == '/') {
        $dir = substr($dir, 0, -1);
      }
      $dir .= PAPAYA_PATH_ADMIN.'/'.$path;
      break;
    case 'upload':
      $dir = PAPAYA_PATH_DATA.$path;
      break;
    default:
      if (strpos($path, 'callback:') === 0) {
        $callbackMethod = substr($path, 9);
        if (isset($this->owner) && method_exists($this->owner, $callbackMethod)) {
          $dir = call_user_func(array($this->owner, $callbackMethod));
        } else {
          $dir = '';
        }
      } else {
        $dir = $path;
      }
      break;
    }

    if (substr($dir, -1) != '/') {
      $dir .= '/';
    }

    try {
      $files = array_keys(iterator_to_array(new \FilesystemIterator($dir, \FilesystemIterator::KEY_AS_FILENAME)));
      $result = sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale" fid="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name),
        papaya_strings::escapeHTMLChars($name)
      );
      if ($emptyAllowed) {
        $result .= sprintf(
          '<option value="">%s</option>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('None'))
        );
      }
      sort($files);
      $fileGroups = array();
      foreach ($files as $file) {
        if (!preg_match($regEx, $file)) {
          continue;
        }
        if (preg_match('~^(.+)_([^_]+\\.[^.]+)$~', $file, $match)) {
          $fileGroups[$match[1]][] = $file;
        } else {
          $fileGroups['-'][] = $file;
        }
      }
      foreach ($fileGroups as $groupTitle => $fileGroup) {
        if (\count($fileGroup) > 0) {
          if (\count($fileGroups) > 1 ) {
            $result .= sprintf(
              '<optgroup label="%s">'.LF,
              papaya_strings::escapeHTMLChars($groupTitle)
            );
          }
          foreach ($fileGroup as $file) {
            $fileName = $file;
            $selected = ((string)$fileName == (string)$data)
              ? ' selected="selected"' : '';
            $result .= sprintf(
              '<option value="%s"%s>%s</option>'.LF,
              papaya_strings::escapeHTMLChars($fileName),
              $selected,
              papaya_strings::escapeHTMLChars($fileName)
            );
          }
          if (\count($fileGroups) > 1 ) {
            $result .= '</optgroup>';
          }
        } else {
          if (\is_array($fileGroup)) {
            $fileName = $fileGroup[0];
          } else {
            $fileName = $fileGroup;
          }
          $selected = ((string)$fileName == (string)$data)
            ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%s"%s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($fileName),
            $selected,
            papaya_strings::escapeHTMLChars($fileName)
          );
        }
      }
      $result .= '</select>'.LF;
    } catch (\UnexpectedValueException $e) {
      $result .= sprintf(
        '<div class="dialogInfo dialogScale">%s</div>',
        papaya_strings::escapeHTMLChars($this->_gt('Cannot open directory.'))
      );
    }
    return $result;
  }

  /**
  * Get directory combo
  *
  * @param string $name
  * @param array $element
  * @param array $data
  * @param string $path
  * @param string $baseDirectory optional, default value ''
  * @param string $pattern optional, PCRE pattern
  * @access public
  * @return string
  */
  function getDirectoryCombo(
    $name, $element, $data, $path, $baseDirectory = '', $pattern = ''
  ) {
    $result = '';
    switch ($baseDirectory) {
    case 'page':
      $dir = $_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_WEB.$path;
      break;
    case 'admin':
      $dir = $_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_WEB;
      if (substr($dir, -1) == '/') {
        $dir = substr($dir, 0, -1);
      }
      $dir .= PAPAYA_PATH_ADMIN.'/'.$path;
      break;
    case 'upload':
      $dir = PAPAYA_PATH_DATA.$path;
      break;
    case 'templates':
      $dir = $this->papaya()->options->get('PAPAYA_PATH_TEMPLATES', PAPAYA_PATH_DATA.'templates/').$path;
      break;
    default:
      $dir = $path;
      break;
    }
    if (substr($dir, -1) != '/') {
      $dir .= '/';
    }
    if (file_exists($dir) && $dh = @opendir($dir)) {
      $result = sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale" fid="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name),
        papaya_strings::escapeHTMLChars($name)
      );
      while ($file = readdir($dh)) {
        if (is_dir($dir.$file) && substr($file, 0, 1) != '.') {
          if (empty($pattern) || preg_match($pattern, $file)) {
            $files[] = $file;
          }
        }
      }
      closedir($dh);
      if (isset($files) && is_array($files)) {
        sort($files);
        foreach ($files as $file) {
          $selected = ((string)$file == (string)$data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%s"%s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($file),
            $selected,
            papaya_strings::escapeHTMLChars($file)
          );
        }
      }
      $result .= '</select>'.LF;
    } else {
      $result .= sprintf(
        '<div class="dialogInfo dialogScale">%s</div>',
        papaya_strings::escapeHTMLChars($this->_gt('Cannot open directory.'))
      );
    }
    return $result;
  }

  /**
  * Get field combo
  *
  * @param string $name
  * @param array $element
  * @param array $data
  * @access public
  * @return string
  */
  function getFieldCombo($name, $element, $data) {
    $result = '';
    $items = papaya_strings::splitLines($this->owner->data['lookup_'.$element[4]]);
    if (isset($items) && is_array($items)) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale" fid="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name),
        papaya_strings::escapeHTMLChars($name)
      );
      foreach ($items as $item) {
        $selected = ((string)$item == (string)$data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($item),
          $selected,
          papaya_strings::escapeHTMLChars($item)
        );
      }
      $result .= '</select>'.LF;
    }
    return $result;
  }

  /**
  * Get field radio group
  *
  * @param string $name
  * @param array $element
  * @param array $data
  * @access public
  * @return string
  */
  function getFieldRadioGroup($name, $element, $data) {
    $result = '';
    $items = papaya_strings::splitLines($this->owner->data['lookup_'.$element[4]]);
    if (isset($items) && is_array($items)) {
      foreach ($items as $idx => $item) {
        $selected = (($item == $data) || ($idx == 0 && $data == ''))
          ? ' checked="checked"' : '';
        $result .= sprintf(
          '<input type="dialogRadio" value="%s" name="%s[%s]"'.
            ' class="%s" fid="%s" %s>%s</input>'.LF,
          papaya_strings::escapeHTMLChars($item),
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($name),
          $selected,
          papaya_strings::escapeHTMLChars($item)
        );
      }
    }
    return $result;
  }

  /**
  * Get field check group
  *
  * @param string $name
  * @param array $element
  * @param array $data
  * @access public
  * @return string
  */
  function getFieldCheckGroup($name, $element, $data) {
    $result = '';
    $items = papaya_strings::splitLines($this->owner->data['lookup_'.$element[4]]);
    if (isset($items) && is_array($items)) {
      foreach ($items as $item) {
        if (is_array($data) && in_array($item, $data)) {
          $selected = ' checked="checked"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<input type="checkbox" value="%s" name="%s[%s][]"'.
          ' class="dialogCheckbox" fid="%s" %s>%s</input>'.LF,
          papaya_strings::escapeHTMLChars($item),
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($name),
          $selected,
          papaya_strings::escapeHTMLChars($item)
        );
      }
    }
    return $result;
  }

  /**
  * Check dialog input
  *
  * @access public
  * @return boolean
  */
  function checkDialogInput() {
    $result = TRUE;
    unset($this->inputErrors);
    if ($this->useToken && !$this->checkDialogToken()) {
      return FALSE;
    }
    foreach ($this->fields as $fieldName => $field) {
      if (is_string($fieldName) && is_array($field)) {
        $elementType = $field[3];
        if (strpos($field[3], 'disabled_') === 0) {
          //field does not allow user inputs - use default data if provided
          if (empty($field[6])) {
            $this->params[$fieldName] = NULL;
          } else {
            $this->params[$fieldName] = $field[6];
            $this->data[$fieldName] = $this->params[$fieldName];
          }
          continue;
        } elseif ($field[3] == 'file') {
          if (!empty($_FILES[$this->paramName]['tmp_name'][$fieldName])) {
            if (!is_uploaded_file($_FILES[$this->paramName]['tmp_name'][$fieldName])) {
              $this->inputErrors[$fieldName] = 1;
              $result = FALSE;
            }
          } elseif ($field[2]) {
            $this->inputErrors[$fieldName] = 1;
            $result = FALSE;
          }
        } elseif ($this->ignoreCaptionValues) {
          if (!empty($this->params[$fieldName]) && !empty($field[0]) &&
              $this->params[$fieldName] == $field[0]) {
            // current parameter value is caption value - remove and use like and empty string
            $this->params[$fieldName] = '';
          }
        }
        // check captcha result
        if ($elementType == 'captcha') {
          $identifier = empty($this->params[$fieldName]['captchaident'])
            ? '' : $this->params[$fieldName]['captchaident'];
          $answer = empty($this->params[$fieldName]['captchaanswer'])
            ? '' : $this->params[$fieldName]['captchaanswer'];
          if ($this->checkCaptchaAnswer($answer, $identifier)) {
            $this->inputErrors[$fieldName] = 0;
          } else {
            $result = $this->markFieldInvalid($fieldName, $field[0]);
          }
          continue;
        }

        if (!isset($this->params[$fieldName]) || $this->params[$fieldName] === '') {
          /*
          * The Field value is empty, check if it is a needed field, else set the default value
          * if available
          */
          if ($field[2]) {
            $result = $this->markFieldInvalid($fieldName, $field[0]);
          } else {
            $this->inputErrors[$fieldName] = 0;
            $this->data[$fieldName] = isset($field[6]) ? $field[6] : NULL;
          }
        } elseif ($field[1] === '') {
          /*
          * The field has no check defined, set the given value
          */
          $this->data[$fieldName] = isset($this->params[$fieldName])
            ? $this->params[$fieldName] : NULL;
        } elseif (is_array($this->params[$fieldName])) {
          /*
          * The given value is an array, check each value in the list
          */
          $this->data[$fieldName] = array();
          $filter = $this->getFilterObject($field[1]);
          if ($filter) {
            foreach ($this->params[$fieldName] as $subKey => $subValue) {
              if ($subValue === '' && !$field[2]) {
                $this->inputErrors[$fieldName] = 0;
                $this->data[$fieldName][$subKey] = '';
              } else {
                try {
                  $filter->validate($subValue);
                  $this->data[$fieldName][$subKey] = $filter->filter($subValue);
                  $this->inputErrors[$fieldName] = 0;
                } catch (\Papaya\Filter\Exception $e) {
                  $result = $this->markFieldInvalid($fieldName, $field[0]);
                  break;
                }
              }
            }
          }
        } else {
          /*
          * The given value is an array, check each value in the list
          */
          $filter = $this->getFilterObject($field[1]);
          if ($filter) {
            try {
              $filter->validate($this->params[$fieldName]);
              $this->data[$fieldName] = $filter->filter($this->params[$fieldName]);
              $this->inputErrors[$fieldName] = 0;
            } catch (\Papaya\Filter\Exception $e) {
              $result = $this->markFieldInvalid($fieldName, $field[0]);
            }
          }
        }
      }
    }
    return $result;
  }

  /**
  * Mark a field as invalid
  *
  * Add a message and set the error value in input errors array.
  *
  * @param string $fieldName
  * @param string $caption
  * @return string
  */
  function markFieldInvalid($fieldName, $caption) {
    $this->addMsg(
      MSG_ERROR,
      sprintf(
        $this->_gt('The input in field "%s" is not correct.'),
        $this->_gt($caption)
      )
    );
    $this->inputErrors[$fieldName] = 1;
    return FALSE;
  }

  /**
  * Return filter object to check and filter an single input value
  *
  * @param string|array $check
  * @return \Papaya\Filter
  */
  function getFilterObject($check) {
    if (is_object($check) &&
        $check instanceof \Papaya\Filter) {
      return $check;
    } elseif (is_string($check)) {
      if (checkit::has($check)) {
        return new \Papaya\Filter\Callback(array('checkit', $check), array(TRUE));
      } elseif (class_exists($check)) {
        return $this->createFilterObject($check);
      } else {
        return new \Papaya\Filter\RegEx($check);
      }
    } elseif (is_array($check) && class_exists($check[0])) {
      $filterClass = $check[0];
      array_shift($check);
      return $this->createFilterObject($filterClass, $check);
    }
    return NULL;
  }

  /**
  * Create a filter object from a definition
  * @param string $name
  * @param array $arguments
   * @return mixed|null
   */
  function createFilterObject($name, $arguments = array()) {
    $filterReflection = new ReflectionClass($name);
    if ($filterReflection->isSubclassOf(\Papaya\Filter::class)) {
      return call_user_func_array(
        array($filterReflection, 'newInstance'),
        $arguments
      );
    }
    return NULL;
  }

  /**
  * Checks whether the answer given by the user is identical to
  * the identifier generated by the captcha module.
  *
  * @param string $answer Answer given by the user
  * @param string $identifier session data array key for the identifier
  * @return TRUE iff $answer is identical to the identifier, otherwise FALSE
  */
  function checkCaptchaAnswer($answer, $identifier) {
    $sessionData = $this->getSessionValue('PAPAYA_SESS_CAPTCHA');
    if (isset($sessionData[$identifier]) &&
        trim($sessionData[$identifier]) != '' &&
        isset($answer)) {
      $this->setSessionValue('PAPAYA_SESS_CAPTCHA', array());
      return ($answer === $sessionData[$identifier]);
    }
    $this->setSessionValue('PAPAYA_SESS_CAPTCHA', array());
    return FALSE;
  }

  /**
  * clean input string
  *
  * @param string $elementType
  * @param string $value
  * @access public
  * @return string $value
  */
  function cleanInputString($elementType, $value) {
    if ($elementType == 'richtext' || $elementType == 'simplerichtext') {
      return papaya_strings::cleanInputString($value);
    }
    return $value;
  }


  /**
  * Did data in form change ? If TRUE save data
  *
  * @param string $marker optional, default value 'save'
  * @access public
  * @return boolean
  */
  function modified($marker = 'save') {
    if (isset($this->params[$marker]) && is_array($this->fields)) {
      foreach ($this->fields as $key => $val) {
        if (is_array($val)) {
          $paramValue = !isset($this->params[$key]) ? '' : $this->params[$key];
          $dataValue = !isset($this->data[$key]) ? '' : $this->data[$key];
          if ($val[3] == 'checkbox' || $val[3] == 'function') {
            if ($paramValue != $dataValue) {
              return TRUE;
            }
          } elseif (isset($this->params[$key]) && $paramValue != $dataValue) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Getter/Setter for csrf token manager including implizit create
  *
  * @param \Papaya\UI\Tokens $tokens
  * @return \Papaya\UI\Tokens
  */
  protected function tokens(\Papaya\UI\Tokens $tokens = NULL) {
    if (isset($tokens)) {
      $this->_tokens = $tokens;
    } elseif (is_null($this->_tokens)) {
      $this->_tokens = new \Papaya\UI\Tokens();
    }
    return $this->_tokens;
  }

  /**
   * check dialog token
   *
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


