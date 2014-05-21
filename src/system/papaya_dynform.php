<?php
/**
* Dynamic forms consists of XML-data for admin section
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
* @subpackage Dialogs
* @version $Id: papaya_dynform.php 39260 2014-02-18 17:13:06Z weinert $
*/

/**
* Dynamic forms consists of XML-data for admin section
*
* @package Papaya
* @subpackage Dialogs
*/
class papaya_dynamic_form extends base_dynamic_form {

  /**
  * Get dialog XML
  *
  * @access public
  * @return string
  */
  function getDialogXML() {
    $result = sprintf(
      '<dialog action="%s" method="post" title="%s" width="100%%" enctype="multipart/form-data">',
      papaya_strings::escapeHTMLChars($this->baseLink),
      papaya_strings::escapeHTMLChars($this->dialogTitle)
    );
    $result .= $this->getHiddenFieldsXML();
    if (isset($this->groups) && is_array($this->groups)) {
      $result .= '<lines class="dialogLarge">';
      foreach ($this->groups as $group) {
        $result .= sprintf(
          '<subtitle caption="%s"/>',
          papaya_strings::escapeHTMLChars($group['title'])
        );
        if (isset($group['fields']) && is_array($group['fields'])) {
          foreach ($group['fields'] as $fieldId) {
            $field = $this->fields[$fieldId];
            $result .= sprintf(
              '<line fid="%s" caption="%s" %s>',
              papaya_strings::escapeHTMLChars($field['name']),
              papaya_strings::escapeHTMLChars($field['title']),
              $field['needed']
                ? 'needed="'.papaya_strings::escapeHTMLChars($this->_gt('Needed field')).'"' : ''
            );
            $result .= $this->getFieldXML($field);
            $result .= '</line>';
          }
        }
      }
      $result .= '</lines>';
      $result .= sprintf(
        '<dlgbutton value="%s"/>',
        papaya_strings::escapeHTMLChars($this->buttonTitle)
      );
    }
    $result .= '</dialog>';
    return $result;
  }

  /**
  * Get field name
  *
  * @param string $fieldName
  * @access public
  * @return string
  */
  function getFieldName($fieldName) {
    return (isset($this->paramName) && ($this->paramName != ''))
      ? $this->paramName.'['.$fieldName.']'
      : $fieldName;
  }

  /**
  * Get field CSS
  *
  * @param $field
  * @access public
  * @return string
  */
  function getFieldCSS($field) {
    switch($field['type']) {
    case 'combobox':
      return 'dialogSelect dialogScale';
      break;
    case 'checkbox':
      return 'dialogCheckbox dialogScale';
    case 'checkboxes':
      return 'dialogCheckboxes dialogScale';
    case 'imagefile':
    case 'file':
      return 'dialogFileSelect dialogScale';
    case 'image':
      return 'dialogImage dialogScale';
    case 'radio':
      return 'dialogRadio dialogScale';
    case 'textarea':
      return 'dialogTextarea dialogScale';
    case 'url':
    case 'input':
    default:
      return 'dialogInput dialogScale';
    }
  }

  /**
  * Load parameters
  *
  * @access public
  */
  function loadParams() {
    if (isset($this->paramName) && $this->paramName != '') {
      $this->initializeParams();
    }
  }

  /**
  * Get file upload data
  *
  * @param string $fieldName
  * @access public
  * @return mixed array or FALSE
  */
  function getFileUploadData($fieldName) {
    if (isset($_FILES) && is_array($_FILES) && isset($_FILES[$this->paramName]) &&
        isset($_FILES[$this->paramName]['name'][$fieldName]) &&
        isset($_FILES[$this->paramName]['tmp_name'][$fieldName]) &&
        is_uploaded_file($_FILES[$this->paramName]['tmp_name'][$fieldName])) {
      return array(
        'name' => $_FILES[$this->paramName]['name'][$fieldName],
        'tmp_name' => $_FILES[$this->paramName]['tmp_name'][$fieldName]
      );
    } else {
      return FALSE;
    }
  }
}

