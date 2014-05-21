<?php
/**
* Design forms
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Controls
* @version $Id: base_formdesigner.php 39818 2014-05-13 13:15:13Z weinert $
*/

/**
* Design forms
*
* @package Papaya-Library
* @subpackage Controls
*/
class base_formdesigner extends base_formdesigner_xml {
  /**
  * Field
  * @var array $field
  */
  var $field;

  /**
  * Input field size
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'medium';

  /**
  * Is modified?
  * @var boolean $_modified
  */
  var $_modified = FALSE;

  /**
  * Language id
  * @var integer $lngId
  */
  var $lngId = NULL;

  /**
   * @var base_dialog $groupEditDialog
   */
  var $groupEditDialog = NULL;

  /**
   * @var base_dialog $fieldEditDialog
   */
  var $fieldEditDialog = NULL;

  /**
  * the default hidden parameters provided by the parent object
  * @var array | NULL
  */
  var $hiddenParams = NULL;

  /**
  * the default base parameters provided by the parent object
  * @var array | NULL
  */
  var $baseParams = NULL;

  /**
   * @var PapayaTemplate
   */
  public $layout;

  /**
   * Initialize parameters
   *
   * @param string $paramName
   * @param string $fieldXML
   * @param integer $lngId
   * @param null|array $hiddenParams
   * @param null|array $baseParams
   */
  public function initialize(
    $paramName, $fieldXML, $lngId, $hiddenParams = NULL, $baseParams = NULL
  ) {
    $this->paramName = $paramName;
    $this->initializeParams();
    $this->xmlToFields($fieldXML);
    $this->lngId = $lngId;
    $this->hiddenParams = $hiddenParams;
    $this->baseParams = $baseParams;
  }

  /**
  * Basic function for handling user request
  *
  * @access public
  */
  function execute() {
    if (isset($this->params['fielddsg_field']) &&
        isset($this->fields[$this->params['fielddsg_field']])) {
      $this->field = &$this->fields[$this->params['fielddsg_field']];
    }
    if (!isset($this->params['fielddsg_cmd'])) {
      $this->params['fielddsg_cmd'] = '';
    }
    switch ($this->params['fielddsg_cmd']) {
    case 'field_add':
      $this->initFieldEditDialog(TRUE);
      if ($this->fieldEditDialog->modified()) {
        if ($this->fieldEditDialog->checkDialogInput()) {
          if (isset($this->params['fielddsg_group']) &&
              trim($this->params['fielddsg_group']) != '') {
            $groupTitle = $this->params['fielddsg_group'];
          } elseif (isset($this->params['fielddsg_groupsel']) &&
                    trim($this->params['fielddsg_groupsel']) != '') {
            $groupTitle = $this->params['fielddsg_groupsel'];
          } else {
            $groupTitle = NULL;
          }

          if ((!isset($this->fields[$this->params['fielddsg_name']])) &&
              isset($groupTitle)) {
            $this->_modified = TRUE;
            $field = array(
              'name' => $this->params['fielddsg_name'],
              'title' => $this->params['fielddsg_title'],
              'type' => $this->params['fielddsg_type'],
              'validatefunc' => $this->params['fielddsg_valfunc'],
              'validateregex' => $this->params['fielddsg_valregex'],
              'needed' => $this->params['fielddsg_needed'],
              'group' => $groupTitle
            );
            if (isset($this->params['fielddsg_css']) &&
                trim($this->params['fielddsg_css']) != '') {
              $field['css'] = $this->params['fielddsg_css'];
            } elseif (isset($this->params['fielddsg_csssel']) &&
                      trim($this->params['fielddsg_csssel']) != '') {
              $field['css'] = $this->params['fielddsg_csssel'];
            } else {
              $field['css'] = '';
            }
            if (!isset($this->groups[$groupTitle])) {
              $this->groups[$groupTitle]['title'] = $groupTitle;
              if (!isset($this->groups[$groupTitle]['legend'])) {
                $this->groups[$groupTitle]['legend'] = $groupTitle;
              }
            }
            $this->groups[$groupTitle]['fields'][] = $field['name'];
            $this->fields[$field['name']] = $field;
            $this->field = &$this->fields[$field['name']];
            $this->params['fielddsg_field'] = $field['name'];
            $this->params['fielddsg_cmd'] = 'field_edit';
            unset($this->fieldEditDialog);
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Field name already in use.'));
          }
        }
      }
      break;
    case 'field_copy' :
      if (isset($this->field)) {
        $groupTitle = $this->field['group'];
        $oldFieldName = $this->field['name'];
        if (preg_match('~^(.+)_(\d+)$~', $oldFieldName, $regs)) {
          $oldFieldName = $regs[1];
        }
        $copyNumber = 1;
        while (isset($this->fields[$oldFieldName.'_'.$copyNumber])) {
          ++$copyNumber;
        }
        $fieldName = $oldFieldName.'_'.$copyNumber;
        $field = array(
          'name' => $fieldName,
          'group' => $groupTitle,
          'title' => $this->field['title'],
          'type' => $this->field['type'],
          'validatefunc' => $this->field['validatefunc'],
          'validateregex' => $this->field['validateregex'],
          'needed' => $this->field['needed'],
          'css' => $this->field['css'],
          'typeparams' => $this->field['typeparams']
        );
        $this->groups[$groupTitle]['fields'][] = $field['name'];
        $this->fields[$field['name']] = $field;
        $this->field = &$this->fields[$field['name']];
        $this->params['fielddsg_field'] = $field['name'];
        $this->params['fielddsg_cmd'] = 'field_edit';
        $this->_modified = TRUE;
      }
      break;
    case 'field_edit':
      if (isset($this->field)) {
        $this->initFieldEditDialog();
        if ($this->fieldEditDialog->modified()) {
          if ($this->fieldEditDialog->checkDialogInput()) {
            $this->_modified = TRUE;
            if ($this->params['fielddsg_name'] != $this->field['name']) {
              if (!isset($this->fields[$this->params['fielddsg_name']])) {
                $oldName = $this->field['name'];
                $this->field['name'] = $this->params['fielddsg_name'];
                $this->fields[$this->params['fielddsg_name']] = $this->fields[$oldName];
                $this->field = &$this->fields[$this->params['fielddsg_name']];
                unset($this->fields[$oldName]);
                $group = &$this->groups[$this->field['group']];
                $i = array_search($oldName, $group['fields']);
                if ($i === 0 || $i > 0) {
                  $group['fields'][$i] = $this->field['name'];
                }
                $this->fieldEditDialog->hidden['fielddsg_field'] = $this->field['name'];
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Field name already in use.'));
              }
            }
            // modify group
            if (isset($this->params['fielddsg_group']) &&
              trim($this->params['fielddsg_group']) != '') {
              $groupTitle = $this->params['fielddsg_group'];
            } elseif (isset($this->params['fielddsg_groupsel']) &&
              trim($this->params['fielddsg_groupsel']) != '') {
              $groupTitle = $this->params['fielddsg_groupsel'];
            } else {
              $groupTitle = NULL;
            }
            if ($groupTitle != $this->field['group']) {
              // group was modified
              if (isset($this->groups[$groupTitle])) {
                // already exists
                $oldGroup = $this->field['group'];
                $this->groups[$groupTitle]['fields'][] = $this->field['name'];
                if (count($this->groups[$oldGroup]['fields']) <= 1) {
                  unset($this->groups[$oldGroup]);
                } else {
                  $i = array_search($this->field['name'], $this->groups[$oldGroup]['fields']);
                  if ($i === 0 || $i > 0) {
                    unset($this->groups[$oldGroup]['fields'][$i]);
                  }
                }
              } else {
                $newGroups = array();
                // remove from old group
                foreach ($this->groups as $key => $val) {
                  if ($key == $this->field['group']) {
                    if (count($val['fields']) > 1) {
                      $i = array_search($this->field['name'], $val['fields']);
                      if ($i === 0 || $i > 0) {
                        unset($val['fields'][$i]);
                      }
                      $newGroups[$key] = $val;
                    }
                    $newGroups[$groupTitle]['title'] = $groupTitle;
                    $newGroups[$groupTitle]['fields'][] = $this->field['name'];
                  } else {
                    $newGroups[$key] = $val;
                  }
                }
                $this->groups = $newGroups;
              }
              $this->field['group'] = $groupTitle;
            }

            if (isset($this->params['fielddsg_css']) &&
                trim($this->params['fielddsg_css']) != '') {
              $this->field['css'] = $this->params['fielddsg_css'];
            } elseif (isset($this->params['fielddsg_csssel']) &&
                      trim($this->params['fielddsg_csssel']) != '') {
              $this->field['css'] = $this->params['fielddsg_csssel'];
            } else {
              $this->field['css'] = '';
            }
            $this->field['title'] = $this->params['fielddsg_title'];
            $this->field['validatefunc'] = $this->params['fielddsg_valfunc'];
            $this->field['validateregex'] = $this->params['fielddsg_valregex'];
            $this->field['needed'] = $this->params['fielddsg_needed'];

            if ($this->field['type'] != $this->params['fielddsg_type']) {
              $this->field['type'] = $this->params['fielddsg_type'];
              unset($this->fieldEditDialog);
            } else {
              $params = $this->types[$this->field['type']];
              if (is_array($params)) {
                foreach ($params as $attributeName => $paramData) {
                  $this->field['typeparams'][$attributeName] =
                    $this->params['fielddsg_tp_'.$attributeName];
                }
              }
            }
          }
        }
      }
      break;
    case 'field_del':
      if (isset($this->field)) {
        if (isset($this->params['fielddsg_confirm']) && $this->params['fielddsg_confirm']) {
          $this->_modified = TRUE;
          $group = &$this->groups[$this->field['group']];
          $i = array_search($this->field['name'], $group['fields']);
          if ($i === 0 || $i > 0) {
            if (count($group['fields']) < 2) {
              unset($this->groups[$group['title']]);
            } else {
              unset($group['fields'][$i]);
            }
          }
          unset($this->fields[$this->field['name']]);
          unset($this->field);
          unset($this->params['fielddsg_cmd']);
        }
      }
      break;
    case 'move_up':
    case 'move_down':
      if (isset($this->field) && isset($this->groups[$this->field['group']])) {
        //Feld bewegen
        $groupTitle = $this->field['group'];
        $i = array_search($this->field['name'], $this->groups[$groupTitle]['fields']);
        if ($i === 0 || $i > 0) {
          if ($this->params['fielddsg_cmd'] == 'move_down' &&
              count($this->groups[$groupTitle]['fields']) > $i + 1) {
            $f1 = $this->groups[$groupTitle]['fields'][$i + 1];
            $f2 = $this->groups[$groupTitle]['fields'][$i];
            $this->groups[$groupTitle]['fields'][$i + 1] = $f2;
            $this->groups[$groupTitle]['fields'][$i] = $f1;
            $this->_modified = TRUE;
          } elseif ($this->params['fielddsg_cmd'] == 'move_up' && $i > 0) {
            $f1 = $this->groups[$groupTitle]['fields'][$i - 1];
            $f2 = $this->groups[$groupTitle]['fields'][$i];
            $this->groups[$groupTitle]['fields'][$i - 1] = $f2;
            $this->groups[$groupTitle]['fields'][$i] = $f1;
            $this->_modified = TRUE;
          }
        }
      } elseif (isset($this->params['fielddsg_name']) &&
                isset($this->groups[$this->params['fielddsg_name']])) {
        //Gruppe bewegen
        $groupTitles = array_keys($this->groups);
        $i = array_search($this->params['fielddsg_name'], $groupTitles);
        if ($i === 0 || $i > 0) {
          if ($this->params['fielddsg_cmd'] == 'move_down' &&
              count($groupTitles) > $i + 1) {
            $g1 = $groupTitles[$i + 1];
            $g2 = $groupTitles[$i];
            $groupTitles[$i + 1] = $g2;
            $groupTitles[$i] = $g1;
            $this->_modified = TRUE;
          } elseif ($this->params['fielddsg_cmd'] == 'move_up' && $i > 0) {
            $g1 = $groupTitles[$i - 1];
            $g2 = $groupTitles[$i];
            $groupTitles[$i - 1] = $g2;
            $groupTitles[$i] = $g1;
            $this->_modified = TRUE;
          }
          if ($this->_modified) {
            $groups = array();
            foreach ($groupTitles as $groupTitle) {
              $groups[$groupTitle] = $this->groups[$groupTitle];
            }
            $this->groups = $groups;
          }
        }
      }
      break;
    case 'group_edit':
      if (isset($this->params['fielddsg_name'])) {
        $this->initGroupEditDialog();
        if ($this->groupEditDialog->modified()) {
          if ($this->groupEditDialog->checkDialogInput() &&
              trim($this->params['fielddsg_title']) != '' &&
              isset($this->groups[$this->params['fielddsg_name']])
              ) {
            if ($this->params['fielddsg_name'] !== $this->params['fielddsg_title'] &&
                isset($this->groups[$this->params['fielddsg_title']])
                ) {
              $this->addMsg(MSG_ERROR, $this->_gt('Group name already in use.'));
              break;
            }
            if (isset($this->params['fielddsg_text'])) {
              // Set new Text
              $this->_modified = TRUE;
              $this->groups[$this->params['fielddsg_name']]['text'] =
                $this->params['fielddsg_text'];
            }
            if (isset($this->params['fielddsg_title'])) {
              // Set new Title
              $this->_modified = TRUE;
              $this->groups[$this->params['fielddsg_name']]['title'] =
                $this->params['fielddsg_title'];
              $groups = array();
              foreach ($this->groups as $groupName => $groupData) {
                if ($groupName == $this->params['fielddsg_name']) {
                  $groups[$this->params['fielddsg_title']] = $groupData;
                } else {
                  $groups[$groupName] = $groupData;
                }
              }
              $this->groups = $groups;
              $this->params['fielddsg_name'] = $this->params['fielddsg_title'];
              unset($this->groupEditDialog);
            }
          }
        }
      }
      break;
    case 'group_del':
      if (isset($this->params['fielddsg_group'])) {
        if (isset($this->params['fielddsg_confirm'])
            && $this->params['fielddsg_confirm']) {
          unset($this->groups[$this->params['fielddsg_group']]);
          unset($this->params['fielddsg_cmd']);
          $this->_modified = TRUE;
        }
      }
      break;
    case 'group_add':
      $this->initGroupEditDialog(TRUE);
      if (isset($this->params['save']) && $this->params['save'] == 1) {
        if ($this->groupEditDialog->checkDialogInput() &&
            trim($this->params['fielddsg_title']) != '' &&
            !isset($this->groups[$this->params['fielddsg_title']])) {
          $this->groups[$this->params['fielddsg_title']]['title'] =
            $this->params['fielddsg_title'];
          $this->groups[$this->params['fielddsg_title']]['text'] =
            $this->params['fielddsg_text'];
          $this->_modified = TRUE;
          $this->params['fielddsg_name'] = $this->params['fielddsg_title'];
          unset($this->groupEditDialog);
        }
      }
      break;
    }
  }

  /**
  * Get dialog XML
  *
  * @access public
  * @return string XML or ''
  */
  function getDialogXML() {
    $result = '';
    if (!isset($this->params['fielddsg_cmd'])) {
      $this->params['fielddsg_cmd'] = '';
    }
    switch ($this->params['fielddsg_cmd']) {
    case 'field_del':
      if (isset($this->field)) {
        $result .= $this->getDelFieldForm();
      }
      $result .= $this->getFieldEditDialog(TRUE);
      break;
    case 'field_add':
      $result .= $this->getFieldEditDialog(TRUE);
      break;
    case 'field_edit':
      if (isset($this->field)) {
        $result .= $this->getFieldEditDialog();
      }
      break;
    case 'group_edit':
      $result .= $this->getGroupEditDialog();
      break;
    case 'group_del':
      $result .= $this->getDelGroupForm();
      break;
    case 'group_add':
      $result .= $this->getGroupEditDialog(TRUE);
      break;
    }
    return $result;
  }

  /**
  * Check if input modifies
  *
  * @access public
  * @return boolean
  */
  function modified() {
    return (boolean)$this->_modified;
  }

  /**
  * Get list XML
  *
  * @access public
  * @return string XML
  */
  function getListXML() {
    return $this->getFieldsListView();
  }

  /**
  * Initialize field edit dialog
  *
  * @param boolean $newField optional, default value FALSE
  * @access public
  */
  function initFieldEditDialog($newField = FALSE) {
    $data = array();
    if (!(isset($this->fieldEditDialog) && is_object($this->fieldEditDialog))) {
      if (isset($this->hiddenParams)) {
        $hidden = $this->mergeParamsWithBaseParams($this->hiddenParams);
      } else {
        $hidden = $this->mergeParamsWithBaseParams(array());
      }
      $hidden['save'] = 1;
      if ($newField) {
        $hidden['fielddsg_cmd'] = 'field_add';
      } else {
        $hidden['fielddsg_cmd'] = 'field_edit';
        $hidden['fielddsg_field'] = isset($this->params['fielddsg_field']) ?
          $this->params['fielddsg_field'] : '';
        $data = array(
          'fielddsg_name' => $this->field['name'],
          'fielddsg_title' => $this->field['title'],
          'fielddsg_type' => $this->field['type'],
          'fielddsg_valfunc' => $this->field['validatefunc'],
          'fielddsg_valregex' => $this->field['validateregex'],
          'fielddsg_needed' => $this->field['needed'],
          'fielddsg_csssel' => $this->field['css'],
          'fielddsg_groupsel' => $this->field['group']
        );
      }
      $types = array();
      if (isset($this->types) && is_array($this->types)) {
        foreach (array_keys($this->types) as $typeName) {
          $types[$typeName] = $typeName;
        }
      }

      $fields = array(
        'fielddsg_name' => array('Name', 'isAlphaNum', TRUE, 'input', 500, '', ''),
        'fielddsg_title' => array('Title', 'isSomeText', TRUE, 'input', 500, '', ''),
        'fielddsg_type' => array('Type', 'isAlpha', TRUE, 'combo', $types, '', ''),
        'Validation',
        'fielddsg_valfunc' => array('Function', 'isAlphaChar', TRUE, 'function',
          'getCheckFunctionsCombo', '', ''),
        'fielddsg_valregex' => array('PCRE', 'isNoHTML', FALSE, 'input', 40, '', ''),
        'fielddsg_needed' => array('Needed', 'isNum', TRUE, 'yesno',
          1, '', '', 'center'),
        'CSS',
        'fielddsg_css' => array('Input', 'isAlphaNum', FALSE, 'input', 80, '', ''),
        'fielddsg_csssel' => array('Select', 'isAlphaNum', FALSE, 'function',
          'getCSSCombo', '', ''),
        'Section',
        'fielddsg_group' => array('Input', 'isNoHTML', TRUE, 'input', 100, '', ''),
      );
      if (isset($this->groups) && is_array($this->groups) && count($this->groups) > 0) {
        $fields['fielddsg_group'][2] = FALSE;
        $fields['fielddsg_groupsel'] = array(
          'Select', 'isNoHTML', FALSE, 'function', 'getGroupsCombo', '', ''
        );
      }

      if (isset($this->field['type']) && isset($this->types[$this->field['type']]) &&
          is_array($this->types[$this->field['type']])) {
        $fields[] = 'Parameters';
        foreach ($this->types[$this->field['type']] as $attributeName => $field) {
          if (isset($this->field['typeparams'][$attributeName])) {
            $data['fielddsg_tp_'.$attributeName] =
              $this->field['typeparams'][$attributeName];
          }
          $fields['fielddsg_tp_'.$attributeName] = $field;
        }
      }

      $this->fieldEditDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->fieldEditDialog->dialogTitle = $this->_gt('Field');
      $this->fieldEditDialog->baseLink = $this->baseLink;
      $this->fieldEditDialog->inputFieldSize = $this->inputFieldSize;
      $this->fieldEditDialog->dialogDoubleButtons = TRUE;
      $this->fieldEditDialog->loadParams();
    }
  }

  /**
   * Initialize group edit dialog
   * @param boolean $newField optional, default value FALSE
   * @access public
   */
  function initGroupEditDialog($newField = FALSE) {
    $data = array();
    if (!(isset($this->groupEditDialog) && is_object($this->groupEditDialog))) {
      if (isset($this->hiddenParams)) {
        $hidden = $this->mergeParamsWithBaseParams($this->hiddenParams);
      } else {
        $hidden = $this->mergeParamsWithBaseParams(array());
      }
      $hidden['save'] = 1;
      if ($newField) {
        $hidden['fielddsg_cmd'] = 'group_add';
      } else {
        $hidden['fielddsg_cmd'] = 'group_edit';
        $hidden['fielddsg_name'] = $this->params['fielddsg_name'];
        if (isset($this->groups[$this->params['fielddsg_name']]['title'])) {
          $data['fielddsg_title'] = $this->groups[$this->params['fielddsg_name']]['title'];
        }
        if (isset($this->groups[$this->params['fielddsg_name']]['text'])) {
          $data['fielddsg_text'] = $this->groups[$this->params['fielddsg_name']]['text'];
        }
      }
      $fields = array(
        'fielddsg_title' => array('Title', 'isNoHTML', TRUE, 'input', 500),
        'fielddsg_text' => array('Text', 'isSomeText', FALSE, 'simplerichtext', 6)
      );

      $this->groupEditDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->groupEditDialog->dialogTitle = $this->_gt('Section');
      $this->groupEditDialog->baseLink = $this->baseLink;
      $this->groupEditDialog->inputFieldSize = $this->inputFieldSize;
      $this->groupEditDialog->dialogDoubleButtons = FALSE;
      $this->groupEditDialog->loadParams();
    }
  }

  /**
  * Get combo check-functions
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @access public
  * @return string XML or ''
  */
  function getCheckFunctionsCombo($name, $field, $data) {
    $result = '';
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $methods = checkit::getList();
    asort($methods);
    if (is_array($methods) && count($methods) > 0) {
      $selected = ($data == -1) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d" %s>[%s]</option>'.LF,
        -1,
        $selected,
        papaya_strings::escapeHTMLChars('PCRE')
      );
      foreach ($methods as $method => $methodTitle) {
        if (substr($method, 0, 2) == 'is') {
          $selected = (strtolower($data) == strtolower($method))
            ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%s" %s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($method),
            $selected,
            papaya_strings::escapeHTMLChars($methodTitle)
          );
        }
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get groups combo
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @access public
  * @return string XML or ''
  */
  function getGroupsCombo($name, $field, $data) {
    $result = '';
    if (is_array($this->groups)) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      foreach ($this->groups as $title => $group) {
        $selected = ($data == $title) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($title),
          $selected,
          papaya_strings::escapeHTMLChars($title)
        );
      }
      $result .= '</select>'.LF;
    }
    return $result;
  }

  /**
  * Get CSS
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @access public
  * @return string XML or ''
  */
  function getCSSCombo($name, $field, $data) {
    $result = '';
    if (isset($this->fields) && is_array($this->fields)) {
      $cssClasses = array();
      foreach ($this->fields as $field) {
        if (isset($field['css']) && trim($field['css']) != '') {
          $cssClasses[] = $field['css'];
        }
      }
      $cssClasses = array_unique($cssClasses);
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      foreach ($cssClasses as $css) {
        $selected = ($data == $css) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($css),
          $selected,
          papaya_strings::escapeHTMLChars($css)
        );
      }
      $result .= '</select>'.LF;
    }
    return $result;
  }

  /**
  * Get field edit dialog
  *
  * @access public
  * @return string XML or ''
  */
  function getFieldEditDialog() {
    $this->initFieldEditDialog();
    return $this->fieldEditDialog->getDialogXML();
  }

  /**
   * Get field edit dialog
   *
   * @access public
   * @return string XML or ''
   */
  function getGroupEditDialog() {
    $this->initGroupEditDialog();
    return $this->groupEditDialog->getDialogXML();
  }

  /**
  * Get delete field form
  *
  * @access public
  * @return string XML from getDeleteForm
  */
  function getDelFieldForm() {
    $hidden = $this->mergeParamsWithBaseParams(
      array(
        'fielddsg_cmd' => 'field_del',
        'fielddsg_field' => $this->field['name'],
        'fielddsg_confirm' => 1
      )
    );
    $msg = sprintf(
      $this->_gt('Delete field "%s (%s)"?'),
      $this->field['name'],
      $this->field['title']
    );
    return $this->getDeleteForm($hidden, $msg);
  }

  /**
   * Delete group dialog
   * @return string XML from getDeleteForm
   */
  function getDelGroupForm() {
    $hidden = $this->mergeParamsWithBaseParams(
      array(
        'fielddsg_cmd' => 'group_del',
        'fielddsg_group' => $this->params['fielddsg_group'],
        'fielddsg_confirm' => 1
      )
    );
    $msg = sprintf(
      $this->_gt('Delete section "%s" and all fields in it?'),
      $this->groups[$this->params['fielddsg_group']]['title']
    );
    return $this->getDeleteForm($hidden, $msg);
  }

  /**
   * Returns delete from to confirm delete actions on fields and groups.
   * @param array $hidden Hidden fields
   * @param string $msg Question to display, sth. like "Really?"
   * @return string XML for base_dialog
   */
  function getDeleteForm($hidden, $msg) {
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->baseLink = $this->baseLink;
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get fields list view
  *
  * @access public
  * @return string XML or ''
  */
  function getFieldsListView() {
    $images = $this->papaya()->images;
    if (isset($this->groups) && is_array($this->groups) &&
        isset($this->fields) && is_array($this->fields)) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Fields'))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Name'))
      );
      $result .= sprintf(
        '<col span="2">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Type'))
      );
      $result .= '<col span="2"/>';
      $result .= '</cols>';
      $result .= '<items>';

      $groupMax = count($this->groups) - 1;
      $groupIdx = 0;
      foreach ($this->groups as $group) {
        $result .= sprintf(
          '<listitem title="%s" href="%s">',
          papaya_strings::escapeHTMLChars($group['title']),
          $this->getLink(
            $this->mergeParamsWithBaseParams(
              array(
                'fielddsg_cmd' => 'group_edit',
                'fielddsg_name' => $group['title']
              )
            )
          )
        );
        if ($groupMax > 0) {
          if ($groupIdx > 0) {
            $result .= '<subitem align="right">';
            $result .= sprintf(
              '<a href="%s"><glyph src="%s"/></a>',
              $this->getLink(
                $this->mergeParamsWithBaseParams(
                  array(
                    'fielddsg_cmd' => 'move_up',
                    'fielddsg_name' => $group['title']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($images['actions-go-up'])
            );
            $result .= '</subitem>';
          } else {
            $result .= '<subitem/>';
          }
          if ($groupIdx < $groupMax) {
            $result .= '<subitem align="left">';
            $result .= sprintf(
              '<a href="%s"><glyph src="%s"/></a>',
              $this->getLink(
                $this->mergeParamsWithBaseParams(
                  array(
                    'fielddsg_cmd' => 'move_down',
                    'fielddsg_name' => $group['title']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($images['actions-go-down'])
            );
            $result .= '</subitem>';
          } else {
            $result .= '<subitem/>';
          }
          $groupIdx++;
        } else {
          $result .= '<subitem/>';
          $result .= '<subitem/>';
        }
        $result .= '<subitem/>';
        $result .= '<subitem/>';
        $result .= '</listitem>';

        $fieldIdx = 0;
        if (isset($group['fields']) && is_array($group['fields'])) {
          $fieldMax = count($group['fields']) - 1;
          foreach ($group['fields'] as $fieldName) {
            if (isset($this->fields[$fieldName])) {
              $field = $this->fields[$fieldName];
              if (isset($this->params['fielddsg_field']) &&
                  $this->params['fielddsg_field'] == $field['name']) {
                $selected = ' selected="selected"';
              } else {
                $selected = '';
              }
              $result .= sprintf(
                '<listitem indent="1" title="%s" href="%s"%s>',
                papaya_strings::escapeHTMLChars($field['name']),
                papaya_strings::escapeHTMLChars(
                  $this->getLink(
                    $this->mergeParamsWithBaseParams(
                      array(
                        'fielddsg_cmd' => 'field_edit',
                        'fielddsg_field' => $field['name']
                      )
                    )
                  )
                ),
                $selected
              );
              $result .= sprintf(
                '<subitem span="2">%s</subitem>',
                papaya_strings::escapeHTMLChars($field['type'])
              );
              if ($fieldMax > 0) {
                if ($fieldIdx > 0) {
                  $result .= '<subitem align="right">';
                  $result .= sprintf(
                    '<a href="%s"><glyph src="%s"/></a>',
                    papaya_strings::escapeHTMLChars(
                      $this->getLink(
                        $this->mergeParamsWithBaseParams(
                          array(
                            'fielddsg_cmd' => 'move_up',
                            'fielddsg_field' => $field['name']
                          )
                        )
                      )
                    ),
                    papaya_strings::escapeHTMLChars($images['actions-go-up'])
                  );
                  $result .= '</subitem>';
                } else {
                  $result .= '<subitem/>';
                }
                if ($fieldIdx < $fieldMax) {
                  $result .= '<subitem align="left">';
                  $result .= sprintf(
                    '<a href="%s"><glyph src="%s"/></a>',
                    papaya_strings::escapeHTMLChars(
                      $this->getLink(
                        $this->mergeParamsWithBaseParams(
                          array(
                            'fielddsg_cmd' => 'move_down',
                            'fielddsg_field' => $field['name']
                          )
                        )
                      )
                    ),
                    papaya_strings::escapeHTMLChars($images['actions-go-down'])
                  );
                  $result .= '</subitem>';
                } else {
                  $result .= '<subitem/>';
                }
                $fieldIdx++;
              } else {
                $result .= '<subitem/>';
                $result .= '<subitem/>';
              }
              $result .= '</listitem>';
            }
          }
        }
      }
      $result .= '</items>';
      $result .= '</listview>';
      return $result;
    }
    return '';
  }

  /**
   * Get XML buttons
   *
   * @access public
   * @internal param array $params optional, default value empty array
   * @return string '' or XML
   */
  function getButtonsXML() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;

    $toolbar->addButton(
      'Add field',
      $this->getLink($this->mergeParamsWithBaseParams(array('fielddsg_cmd' => 'field_add'))),
      'actions-generic-add'
    );

    $toolbar->addButton(
      'Add section',
      $this->getLink($this->mergeParamsWithBaseParams(array('fielddsg_cmd' => 'group_add'))),
      'actions-generic-add'
    );
    if (isset($this->field) && is_array($this->field)) {
      $toolbar->addButton(
        'Copy field',
        $this->getLink(
          $this->mergeParamsWithBaseParams(
            array(
              'fielddsg_cmd' => 'field_copy',
              'fielddsg_field' => $this->field['name']
            )
          )
        ),
        'actions-edit-copy'
      );
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Delete field',
        $this->getLink(
          $this->mergeParamsWithBaseParams(
            array(
              'fielddsg_cmd' => 'field_del',
              'fielddsg_field' => $this->field['name']
            )
          )
        ),
        'actions-generic-delete'
      );
    }
    if (isset($this->params['fielddsg_cmd']) && $this->params['fielddsg_cmd'] == 'group_edit' ) {
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Delete section',
        $this->getLink(
          $this->mergeParamsWithBaseParams(
            array(
              'fielddsg_cmd' => 'group_del',
              'fielddsg_group' => $this->params['fielddsg_name']
            )
          )
        ),
        'actions-generic-delete'
      );
    }
    return $toolbar->getXML();
  }

  /**
  * generates a dropdown list of existing captcha images
   *
   * @param string $name
   * @param array $field
   * @param mixed $data
   * @return string
   */
  function getCaptchasCombo($name, $field, $data) {
    $result = '';
    $imageGenerator = new base_imagegenerator;
    $captchas = $imageGenerator->getIdentifiersByGUID(
      array(
        '103fecb7cc96c1a66633c7f464b15956',
        'fe3dd6359939c142781f70ae4b29c70c'
      )
    );
    if (is_array($captchas) && count($captchas) > 0) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      foreach ($captchas as $captcha) {
        if (!empty($data) && $data == $captcha['image_ident']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<option value="%s"%s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($captcha['image_ident']),
          $selected,
          papaya_strings::escapeHTMLChars($captcha['image_title'])
        );
      }
      $result .= '</select>'.LF;
    }
    $result = !empty($result)
      ? $result : 'You have to add a captcha generator in the images section!';
    return $result;
  }

  /**
   * Returns the continents from module Countries.
   * So you can filter country combo boxes geerated by base_formdesigner
   * by continent.
   *
   * @param string $name
   * @param array $field
   * @param mixed $data
   * @return string XML Continent list
   */
  function getContinents ($name, $field, $data) {
    $countriesObj = $this->papaya()->plugins->get('99db2c2898403880e1ddeeebf7ee726c', $this);

    // Check whether the module has successfully beein loaded
    if (!is_object($countriesObj)) {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('Module Countries could not be loaded from base_formdesigner!'),
        TRUE
      );
      return NULL;
    }

    $countriesObj->countryAdmin->paramName = $this->paramName;
    return $countriesObj->callbackContinent($name, $field, $data);
  }

  /**
   * Returns the countries from module Countries.
   * So you can define the default value, when a form were build
   *
   * @param string $name
   * @param array $field
   * @param mixed $data
   * @return string XML Continent list
   */
  function getCountries ($name, $field, $data) {
    $countriesObj = $this->papaya()->plugins->get('99db2c2898403880e1ddeeebf7ee726c', $this);

    // Check whether the module has successfully beein loaded
    if (!is_object($countriesObj)) {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('Module Countries could not be loaded from base_formdesigner!'),
        TRUE
      );
      return NULL;
    }

    $countriesObj->countryAdmin->paramName = $this->paramName;

    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $result .= $countriesObj->getCountryOptionsXHTML($data);
    $result .= '</select>';
    return $result;
  }


  /**
  * Get media directory combo
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @access public
  * @return string '' or XML
  */
  function getMediaDirectoryCombo($name, $field, $data) {
    $result = '';
    $mediaDB = base_mediadb::getInstance();
    $folders = $mediaDB->getFolderComboArray($this->lngId);

    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    foreach ($folders as $folderId => $folderName) {
      $selected = ($folderId == (int)$data) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d" %s>%s</option>'.LF,
        papaya_strings::escapeHTMLChars($folderId),
        $selected,
        papaya_strings::escapeHTMLChars($folderName)
      );
    }
    $result .= '</select>'.LF;

    return $result;
  }

  /**
  * Get merged array between params and base params
  *
  * @param array $params
  * @access public
  * @return array
  */
  function mergeParamsWithBaseParams($params) {
    $mergedParams = array();

    if (isset($this->baseParams) && is_array($this->baseParams)) {
      $mergedParams = array_merge($this->baseParams, $mergedParams);
    }
    if (isset($params) && is_array($params)) {
      $mergedParams = array_merge($params, $mergedParams);
    }
    return $mergedParams;
  }
}


