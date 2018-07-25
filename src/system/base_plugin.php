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
* Superclass for all plugin superclasses
*
* @package Papaya
* @subpackage Modules
*/
class base_plugin extends base_object {

  /**
  * GUID for this module, set be plugin loader
  *
  * @var NULL|string
  */
  public $guid = NULL;

  /**
  * Buffer for content/configurations data
  * @var array $data
  */
  var $data = NULL;

  /**
  * Parameter name for html forms
  * @var string $paramName
  */
  var $paramName = '';

  /**
  * Parameter for HTML-forms
  * @var array $params
  */
  var $params;

  /**
  * Default-link
  * @var string $baseLink
  */
  var $baseLink;

  /**
  * XML-tag-base
  * @var string $tagName
  */
  var $tagName = 'data';


  /**
  * Create XML for debug messages
  * @var boolean $usesXML
  */
  var $usesXML = FALSE;

  /**
  * edit fields - configuration for one instance of a module
  *
  * name => [
  *    title,
  *    check function,
  *    mandatory field (TRUE/FALSE),
  *    Type (input, combo, textarea)
  *    Type paramter(maximum length, value-hash, row)
  *    fast help
  * ]
  *
  * @var array $editFields
  */
  var $editFields;

  /**
  * edit groups are an alternative for edit fields - it is an array of groups with edit fields
  * If $editGroups is used $editFields will get ignored.
  * [
  *   title,
  *   image,
  *   [
  *     name => [
  *       title,
  *       check function,
  *       mandatory field (TRUE/FALSE),
  *       Type (input, combo, textarea)
  *       Type paramter(maximum length, value-hash, row)
  *       fast help)
  *     ]
  *   ]
  * ]
  * @var array $editGroups
  */
  var $editGroups;

  /**
  * plugin option fields - these are "gobal" options for all instances of a module
  *
  * name => [
  *    inscription,
  *    check function,
  *    mandatory field (TRUE/FALSE),
  *    Type (input, combo, textarea)
  *    Type paramter(maximum length, value-hash, row)
  *    fast help
  * ]
  *
  * @var array $pluginOptionFields
  */
  var $pluginOptionFields;

  /**
  * Output of module cacheable?
  * @var boolean $cachable
  */
  var $cacheable = TRUE;

  /**
  * Is preview possible?
  * @var integer $preview
  */
  var $preview = FALSE;

  /**
  * Field error
  * @var string $fieldError
  */
  var $fieldError;
  /**
  * Input error
  * @var string $inputError
  */
  var $inputError = 'The Input in this field contains errors.';
  /**
  * Empty error
  * @var string $emptyError
  */
  var $emptyError = 'This field needs a value.';

  /**
  * dialog field size
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'medium';

  /**
  * Papaya tag pattern
  * @var string $papayaTagPattern
  */
  var $papayaTagPattern = '/<(papaya|ndim):([a-z]\w+)\s?([^>]*)\/?>(<\/(\1):(\2)>)?/ims';

  /**
  * parent object / owner
  *
  * @var object|base_topic|\Papaya\Content\Page|NULL
  */
  var $parentObj = NULL;

  /**
   * @var base_dialog plugin properties dialog
   */
  public $dialog;

  /**
   * @var array
   */
  public $fieldErrors;

  /**
   * Constructor PHP 5
   *
   * @param object $aOwner owner object
   * @param null|string $paramName
   */
  public function __construct($aOwner, $paramName = NULL) {
    $this->parentObj = $aOwner;
    if (isset($paramName)) {
      $this->paramName = $this->getParamname($paramName);
    } elseif (isset($this->paramName) && trim($this->paramName) != '') {
      $this->paramName = $this->getParamname($this->paramName);
    } else {
      $this->paramName = $this->getParamname('bab');
    }
    $this->initializeParams();
    $this->baseLink = $this->getBaseLink();
  }

  /**
  * Initialization object data like session parameters for administration interface.
  *
  * @access public
  * @return boolean FALSE
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.get_class($this).'_'.$this->paramName;
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('contentmode', 'cmd');

    if (isset($this->params['contentmode']) &&
        $this->params['contentmode'] > 0 &&
        isset($this->editGroups[$this->params['contentmode']]) &&
        is_array($this->editGroups[$this->params['contentmode']][2])) {
      $this->editFields = $this->editGroups[$this->params['contentmode']][2];
    } elseif (isset($this->editGroups[0]) && is_array($this->editGroups[0][2])) {
      $this->editFields = $this->editGroups[0][2];
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
   * Returns given parameter
   *
   * @param string $paramName parameter name
   * @return string
   */
  public function getParamName($paramName) {
    return $paramName;
  }

  /**
  * Load configuration data in object
  *
  * @param string $xmlData XML-data string
  * @access public
  */
  function setData($xmlData) {
    $this->data = PapayaUtilStringXml::unserializeArray($xmlData);
    $this->onLoad();
  }

  /**
  * init some default data (from $this->editFields)
  *
  * @param array $fields name of fields with default - optional
  * @param boolean $override can the default value override the current value in $this->data
  * @param array | NULL $fieldData array with field data (default is $this->editFields)
  * @access public
  * @return void
  */
  function setDefaultData($fields = NULL, $override = FALSE, $fieldData = NULL) {
    if (!isset($fieldData)) {
      if (is_array($this->editGroups) && count($this->editGroups) > 0) {
        foreach ($this->editGroups as $group) {
          $this->setDefaultData(NULL, $override, $group[2]);
        }
      } else {
        $fieldData = $this->editFields;
      }
    }
    if (!empty($fieldData)) {
      if (!is_array($fields)) {
        $fields = array_keys($fieldData);
      }
      if (is_array($fields)) {
        foreach ($fields as $fieldName) {
          if (isset($fieldData[$fieldName]) &&
              is_array($fieldData[$fieldName]) &&
              isset($fieldData[$fieldName][6]) ) {
            if ($override || !isset($this->data[$fieldName])) {
              $this->data[$fieldName] = preg_replace(
                '~[\r\n]+\s*~', ' ', $fieldData[$fieldName][6]
              );
            }
          }
        }
      }
    }
  }

  /**
  * Write data array to xml string
  *
  * @access public
  * @return string $result XML-data string
  */
  function getData() {
    return PapayaUtilStringXml::serializeArray($this->data);
  }

  /**
   * data changed?
   *
   * @param string $marker
   * @return boolean
   */
  public function modified($marker = 'save') {
    if (isset($this->dialog) && is_object($this->dialog)) {
      return $this->dialog->modified($marker);
    }
    return FALSE;
  }

  /**
  * Change input
  *
  * @access public
  * @return boolean
  */
  function checkData() {
    $result = $this->checkDialogInput();
    return $result;
  }

  /**
  * XML for take-over in page
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    return $this->getData();
  }

  /**
  * Output of edit form
  *
  * @param string $dialogTitlePrefix optional, default value ''
  * @param string $dialogIcon optional, default value ''
  * @access public
  * @return string XML-string with Formular
  */
  function getForm($dialogTitlePrefix = '', $dialogIcon = '') {
    $result = $this->getEditGroupToolbar();
    $result .= $this->getDialog($dialogTitlePrefix, $dialogIcon);
    return $result;
  }

  /**
  * Create the toolbar that produces a second tab row to navigate through the form fields.
  * This method returns the empty string iff the local toolbar object does not return
  * the button xml.
  *
  * @return string XML the xml definition of the toolbar object or the empty string
  */
  function getEditGroupToolbar() {
    if (isset($this->editGroups) && is_array($this->editGroups)) {
      $toolbar = new base_btnbuilder;
      $toolbar->images = $this->papaya()->images;

      foreach ($this->editGroups as $index => $group) {
        $image = empty($group[1]) ? 'categories-content' : $group[1];
        if (isset($this->params['contentmode']) && $this->params['contentmode'] == $index) {
          $down = TRUE;
        } elseif (empty($this->params['contentmode']) && $index == 0) {
          $down = TRUE;
        } else {
          $down = FALSE;
        }
        $toolbar->addButton(
          $group[0],
          $this->getLink(array('contentmode' => $index)),
          $image,
          '',
          $down
        );
      }

      $str = $toolbar->getXML();
      if ($str) {
        return '<toolbar>'.$str.'</toolbar>'.LF;
      }
    }
    return '';
  }

  /**
  * Initialize dialog
  *
  * @param mixed $hiddenValues optional, default value NULL
  * @access public
  */
  function initializeDialog($hiddenValues = NULL) {
    if (!(isset($this->dialog) && is_object($this->dialog))) {
      if (isset($hiddenValues) && is_array($hiddenValues)) {
        $hidden = $hiddenValues;
      }
      $hidden['save'] = 1;
      if (isset($this->params['contentmode'])) {
        $hidden['contentmode'] = $this->params['contentmode'];
      }

      $this->dialog = new base_dialog(
        $this, $this->paramName, $this->editFields, $this->data, $hidden
      );
      $this->dialog->loadParams();
      $this->dialog->inputFieldSize = $this->inputFieldSize;
      $this->dialog->dialogProtectChanges = TRUE;
      $this->dialog->baseLink = $this->baseLink;
    }
  }

  /**
  * Gibt den Dialog zurueck
  *
  * @param string $dialogTitlePrefix optional, default value ''
  * @param string $dialogIcon optional, default value ''
  * @access public
  * @return string $this->dialog->getDialogXML()
  */
  function getDialog($dialogTitlePrefix = '', $dialogIcon = '') {
    if (isset($this->dialog) && is_object($this->dialog)) {
      if (empty($dialogTitlePrefix)) {
        $this->dialog->dialogTitle =
          papaya_strings::escapeHTMLChars($this->_gt('Edit content'));
      } else {
        $this->dialog->dialogTitle =
          papaya_strings::escapeHTMLChars($dialogTitlePrefix).
          papaya_strings::escapeHTMLChars($this->_gt('Edit content'));
      }
      if (!empty($dialogIcon)) {
        $this->dialog->dialogIcon = papaya_strings::escapeHTMLChars($dialogIcon);
      }
      $this->dialog->dialogDoubleButtons = TRUE;
      if (isset($this->parentObj) && is_object($this->parentObj) &&
          isset($this->parentObj->tableTopics)) {
        $this->dialog->tableTopics = $this->parentObj->tableTopics;
      }
      return $this->dialog->getDialogXML();
    } else {
      return '';
    }
  }

  /**
  * Check user dialog input
  *
  * @access public
  * @return array $result
  */
  function checkDialogInput() {
    if ($result = $this->dialog->checkDialogInput()) {
      $this->data = PapayaUtilArray::merge($this->data, $this->dialog->data, 1);
    }
    if (isset($this->dialog->inputErrors)) {
      $this->fieldErrors = $this->dialog->inputErrors;
    }
    return $result;
  }

  /**
   * Get papaya image tag <papaya:media...
   *
   * @param string $str this is the string the dialog type image(?)
   *                    contains like "32242...,max,200,300"
   * @param integer $width optional, default value 0
   * @param integer $height optional, default value 0
   * @param string $alt optional, default value ''
   * @param string $resize optional, default value NULL
   * @param string $subTitle
   * @access public
   * @return string tag or ''
   */
  function getPapayaImageTag(
    $str, $width = 0, $height = 0, $alt = '', $resize = NULL, $subTitle = ''
  ) {
    if (preg_match($this->papayaTagPattern, $str, $regs)) {
      return $regs[0];
    } elseif (preg_match('~^([^.,]+(\.\w+)?)(,(\d+)(,(\d+)(,(\w+))?)?)?$~i', $str, $regs)) {
      $result = '<papaya:media src="'.papaya_strings::escapeHTMLChars($regs[1]).'"';
      if ($width > 0) {
        $result .= ' width="'.(int)$width.'"';
      } elseif (isset($regs[4])) {
        $result .= ' width="'.(int)$regs[4].'"';
      }
      if ($height > 0) {
        $result .= ' height="'.(int)$height.'"';
      } elseif (isset($regs[6])) {
        $result .= ' height="'.(int)$regs[6].'"';
      }
      if (isset($resize)) {
        $result .= ' resize="'.papaya_strings::escapeHTMLChars($resize).'"';
      } elseif (isset($regs[8])) {
        $result .= ' resize="'.papaya_strings::escapeHTMLChars($regs[8]).'"';
      }
      if (isset($alt) && trim($alt) != '') {
        $result .= ' alt="'.papaya_strings::escapeHTMLChars($alt).'"';
      }
      if (!empty($subTitle)) {
        $result .= ' subtitle="'.papaya_strings::escapeHTMLChars($subTitle).'"';
      }
      return $result.'/>';
    }
    return '';
  }

  /**
  * place holder - overload von ihertited classes
  *
  * @access public
  */
  function onLoad() {
  }

  /**
  * Callback for Select-Box with rights
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @access public
  * @return string
  */
  function getPermsCombo($name, $field, $data) {
    $result = "";
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $surfer = new base_surfer(FALSE);
    $surfer->loadPermissionList();

    $selected = ($data == -1) ? ' selected="selected"' : '';
    $result .= sprintf(
      '<option value="-1" %s>%s</option>'.LF,
      $selected,
      papaya_strings::escapeHTMLChars($this->_gt('none'))
    );
    if (isset($surfer->permissions) && is_array($surfer->permissions)) {
      foreach ($surfer->permissions as $id => $perm) {
        $selected = ($data == $id) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d" %s>%s</option>'.LF,
          (int)$id,
          $selected,
          papaya_strings::escapeHTMLChars($perm['surferperm_title'])
        );
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Callback for Select-Box with user groups
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  *
  * @return string
  */
  function getSurferGroupsCombo($name, $field, $data) {
    $result = "";
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );

    $surfer = new base_surfer(FALSE);
    $surfer->loadSurferGroupsList();

    $selected = ($data == -1) ? ' selected="selected"' : '';
    $result .= sprintf(
      '<option value="-1" %s>%s</option>'.LF,
      $selected,
      papaya_strings::escapeHTMLChars($this->_gt('all'))
    );
    if (isset($surfer->surferGroups) && is_array($surfer->surferGroups)) {
      foreach ($surfer->surferGroups as $id => $group) {
        $selected = ($data == $id) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d" %s>%s</option>'.LF,
          (int)$id,
          $selected,
          papaya_strings::escapeHTMLChars($group['surfergroup_title'])
        );
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Check sufer permissions
  *
  * @param integer $permId
  * @access public
  * @return boolean
  */
  function checkSurferPerm($permId) {
    if ($permId <= 0) {
      return TRUE;
    }
    $surfer = $this->papaya()->surfer;
    if ($surfer->isValid) {
      return $surfer->hasPerm($permId);
    }
    return FALSE;
  }

  /**
  * Returns the handle of the currently logged in user.
  * If the current user is not logged in, FALSE is returned.
  * @return string UserHandle or FALSE
  */
  function getCurrentSurferHandle() {
    $surfer = $this->papaya()->surfer;
    if ($surfer->isValid) {
      return $surfer->surfer['surfer_handle'];
    }
    return FALSE;
  }

  /**
   * parse data of image field
   *
   * @param $dataString
   * @internal param string $dateString
   * @access public
   * @return array $dataArr
   */
  function parseImageFieldData($dataString) {
    $dataArr = array(
      'src' => '',
      'width' => 0,
      'height' => 0
    );
    if (FALSE !== strpos($dataString, '<')) {
      $imageAttributesPattern = '~(src|width|height)=("|\')([^"]+)(\\2)~i';
      if (preg_match_all($imageAttributesPattern, $dataString, $regs, PREG_SET_ORDER)) {
        foreach ($regs as $reg) {
          $dataArr[strtolower($reg[1])] = $reg[3];
        }
      }
    } elseif (($arr = explode('/', $dataString)) &&
              is_array($arr) && count($arr) > 0) {
      $dataArr['src'] = $arr[0];
      $dataArr['width'] = empty($arr[1]) ? 0 : (int)$arr[1];
      $dataArr['height'] = empty($arr[2]) ? 0 : (int)$arr[2];
    } else {
      $dataArr['src'] = trim($dataString);
    }
    return $dataArr;
  }

  /**
  * get papaya media tag
  *
  * @param array $data
  * @param integer $width optional, default value 0
  * @param integer $height optional, default value 0
  * @param string $resize optional, default value 'max'
  * @access public
  * @return string
  */
  function getMediaTag($data, $width = 0, $height = 0, $resize = 'max') {
    if (is_array($data) && isset($data['src'])) {
      $src = $data['src'];
      if ($width <= 0 && isset($data['width']) && $data['width'] > 0) {
        $width = (int)$data['width'];
      }
      if ($height <= 0 && isset($data['height']) && $data['height'] > 0) {
        $height = (int)$data['height'];
      }
    } else {
      $src = $data;
    }
    if (trim($src) != '') {
      $result = '<papaya:media src="'.papaya_strings::escapeHTMLChars($src).'"';
      if ($width > 0) {
        $result .= ' width="'.(int)$width.'"';
      }
      if ($height > 0) {
        $result .= ' height="'.(int)$height.'"';
      }
      if (in_array($resize, array('min', 'mincrop', 'abs'))) {
        $result .= ' resize="'.$resize.'"';
      }
      $result .= '/>';
      return $result;
    }
    return '';
  }

  /**
   * get the plugin options dialog for the module manager
   *
   * @access public
   * @param $paramName
   * @param $hiddenValues
   * @return string | boolean
   */
  function getPluginOptionsDialog($paramName, $hiddenValues) {
    if (isset($this->pluginOptionFields) &&
        is_array($this->pluginOptionFields) &&
        count($this->pluginOptionFields) > 0) {

      //load current values from database
      $moduleOptions = new papaya_module_options();
      $data = $moduleOptions->getOptions($this->guid);
      //some hidden parameters from module manager
      if (isset($hiddenValues) && is_array($hiddenValues)) {
        $hidden = $hiddenValues;
      }
      $hidden['save'] = 1;
      //make sure that all option names are uppercase
      $fields = array();
      foreach ($this->pluginOptionFields as $fieldName => $field) {
        $fields[strtoupper($fieldName)] = $field;
      }
      //create dialog
      $pluginDialog = new base_dialog(
        $this, $paramName, $fields, $data, $hidden
      );
      $oldParamName = $this->paramName;
      $this->paramName = $paramName;
      $pluginDialog->loadParams();
      $pluginDialog->baseLink = $this->baseLink;
      $pluginDialog->dialogTitle = $this->_gt("Edit options");
      //save changes
      if (!empty($pluginDialog->params['save'])) {
        if ($pluginDialog->checkDialogInput()) {
          if ($moduleOptions->saveOptions($this->guid, $pluginDialog->data)) {
            $this->addMsg(MSG_INFO, $this->_gt('Options modified.'));
          }
        }
      }
      $result = $pluginDialog->getDialogXML();
      $this->paramName = $oldParamName;
      //return dialog xml string
      return $result;
    } else {
      return FALSE;
    }
  }
}
