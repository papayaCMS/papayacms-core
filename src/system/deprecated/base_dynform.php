<?php
/**
* Dynamic form contains of XML-data
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
* @package Papaya
* @subpackage Dialogs
* @version $Id: base_dynform.php 39728 2014-04-07 19:51:21Z weinert $
*/

/**
* Dynamic form contains of XML-data
*
* @package Papaya
* @subpackage Dialogs
*/
class base_dynamic_form extends base_object {

  /**
  * Groups
  * @var array $groups
  */
  var $groups = NULL;
  /**
  * Fields
  * @var array $fields
  */
  var $fields = NULL;

  /**
  * Dialog title
  * @var string $dialogTitle
  */
  var $dialogTitle = '';
  /**
  * Button title
  * @var string $buttonTitle
  */
  var $buttonTitle = '';

  /**
  * Paramter name
  * @var string $paramName
  */
  var $paramName = NULL;

  /**
  * Data
  * @var array $data
  */
  var $data;

  /**
  * @var array
  */
  var $inputErrors = NULL;

  /**
   * @var array
   */
  public $validatedData = array();

  /**
   * @var array
   */
  public $uploadFiles = array();

  /**
  * Constructor
  *
  * @param string $xml
  * @param mixed $data optional, default value NULL
  * @param mixed $hidden optional, default value NULL
  * @access public
  */
  function __construct($xml, $data = NULL, $hidden = NULL) {
    $this->xmlToFields($xml);
    $this->data = $data;
    $this->hidden = $hidden;
  }

  /**
  * Get dialog XML
  *
  * @access public
  * @return string $result XML
  */
  function getDialogXML() {
    $result = sprintf(
      '<form action="%s" method="%s" enctype="multipart/form-data">',
      $this->baseLink,
      'post'
    );
    $result .= $this->getHiddenFieldsXML();
    if (isset($this->groups) && is_array($this->groups)) {
      foreach ($this->groups as $group) {
        $result .= '<fieldset>';
        $legend = (isset($group['legend']) && trim($group['legend']) != '')
          ? $group['legend'] : $group['title'];
        $text = (isset($group['legend-text']) && trim($group['legend-text']) != '')
          ? ' ' . $group['legend-text'] : '';
        $result .= '<legend><title>' . papaya_strings::escapeHTMLChars($legend)
          .'</title><text>' . $text . '</text></legend>';
        if (isset($group['fields']) && is_array($group['fields'])) {
          foreach ($group['fields'] as $fieldId) {
            $field = $this->fields[$fieldId];
            if (isset($this->inputErrors[$field['name']]) &&
                $this->inputErrors[$field['name']]) {
              $error = ' class="error"';
            } else {
              $error = '';
            }
            $result .= sprintf(
              '<label for="%s"%s>%s%s</label>',
              papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
              $error,
              papaya_strings::escapeHTMLChars($field['title']),
              ($field['needed'] ? '<needed/>' : '')
            );
            $result .= $this->getFieldXML($field);
          }
        }
        $result .= '<br clear="all" class="clear"/></fieldset>';
      }
      $result .= '<input type="submit" value="'.
        papaya_strings::escapeHTMLChars($this->buttonTitle).'" class="form_button"/>';
    }
    $result .= '</form>';
    return $result;
  }

  /**
  * Get field XML
  *
  * @param array &$field
  * @access public
  * @return string $result XML
  */
  function getFieldXML(&$field) {
    $result = '';
    $cssClassName = $this->getFieldCSS($field);
    if (isset($this->params[$field['name']])) {
      $value = $this->params[$field['name']];
    } elseif (isset($this->data[$field['name']])) {
      $value = $this->data[$field['name']];
    } elseif (isset($field['default'])) {
      $value = $field['default'];
    } else {
      $value = '';
    }
    switch ($field['type']) {
    case 'input':
      $result .= $this->getFieldXMLInput($field, $value, $cssClassName);
      break;
    case 'combobox':
      $result .= $this->getFieldXMLCombobox($field, $value, $cssClassName);
      break;
    case 'checkbox':
      $result .= $this->getFieldXMLCheckbox($field, $value, $cssClassName);
      break;
    case 'checkboxes':
      $result .= $this->getFieldXMLCheckboxes($field, $value, $cssClassName);
      break;
    case 'file':
      $result .= $this->getFieldXMLFile($field, $value, $cssClassName);
      break;
    case 'imagefile':
    case 'image':
      $result .= $this->getFieldXMLImage($field, $value, $cssClassName);
      break;
    case 'captcha':
      $result .= $this->getFieldXMLCaptcha($field, $value, $cssClassName);
      break;
    case 'radio':
      $result .= $this->getFieldXMLRadio($field, $value, $cssClassName);
      break;
    case 'textarea':
      $result .= $this->getFieldXMLTextarea($field, $value, $cssClassName);
      break;
    case 'url':
      $result .= $this->getFieldXMLInput($field, $value, $cssClassName);
      break;
    case 'country':
      $result .= $this->getFieldXMLCountry($field, $value, $cssClassName);
      break;
    }
    return $result;
  }

  /**
  * Get name of field
  *
  * @param string $fieldName
  * @access public
  * @return string
  */
  function getFieldName($fieldName) {
    return (isset($this->paramName) && ($this->paramName != '')) ?
      $this->paramName.'['.$fieldName.']' :
      $fieldName;
  }

  /**
  * Get css of field
  *
  * @param &$field
  * @access public
  * @return string $result
  */
  function getFieldCSS(&$field) {
    $result = (isset($field['css']) && ($field['css'] != '')) ?
      $field['css'] : 'form_'.$field['type'];
    if (isset($this->inputErrors[$field['name']]) &&
        $this->inputErrors[$field['name']]) {
      $result .= ' error';
    }
    return $result;
  }

  /**
  * Load parameters
  *
  * @access public
  */
  function loadParams() {
    if (isset($this->paramName) && $this->paramName != '') {
      if (isset($_REQUEST[$this->paramName]) &&
          is_array($_REQUEST[$this->paramName])) {
        $this->params = $_REQUEST[$this->paramName];
      }
    }
  }

  /**
  * Get hidden fields XML
  *
  * @access public
  * @return string $result
  */
  function getHiddenFieldsXML() {
    $result = '';
    if (isset($this->hidden) && is_array($this->hidden)) {
      foreach ($this->hidden as $key => $val) {
        if (isset($val) && is_array($val)) {
          foreach ($val as $subKey => $subValue) {
            $result .= sprintf(
              '<input type="hidden" name="%s[%s]" value="%s"/>',
              papaya_strings::escapeHTMLChars($key),
              papaya_strings::escapeHTMLChars($subKey),
              papaya_strings::escapeHTMLChars((string)$subValue)
            );
          }
        } elseif (isset($val)) {
          $result .= sprintf(
            '<input type="hidden" name="%s" value="%s"/>',
            papaya_strings::escapeHTMLChars($key),
            papaya_strings::escapeHTMLChars($val)
          );
        }
      }
    }
    return $result;
  }

  /**
  * Get query String
  *
  * @param array $params
  * @access public
  * @return mixed
  */
  function getQueryString($params) {
    $result = '';
    if (isset($params) && is_array($params)) {
      foreach ($params as $key => $val) {
        if (isset($val) && is_array($val)) {
          foreach ($val as $subKey => $subValue) {
            $result .= '&amp;'.urlencode($key).'['.urlencode($subKey).']='.
              urlencode((string)$subValue);
          }
        } elseif (isset($val)) {
          $result .= '&amp;'.urlencode($key).'='.urlencode((string)$val);
        }
      }
    }
    if (isset($this->hidden) && is_array($this->hidden)) {
      foreach ($this->hidden as $key => $val) {
        if (isset($val) && is_array($val)) {
          foreach ($val as $subKey => $subValue) {
            $result .= '&amp;'.urlencode($key).'['.urlencode($subKey).']='.
              urlencode((string)$subValue);
          }
        } elseif (isset($val)) {
          $result .= '&amp;'.urlencode($key).'='.urlencode((string)$val);
        }
      }
    }
    if ($result != '') {
      return substr($result, 5);
    } else {
      return '';
    }
  }

  /**
  * Check dialog inputs
  *
  * @param mixed $oldData optional, default value NULL
  * @access public
  * @return boolean
  */
  function checkDialogInputs($oldData = NULL) {
    unset($this->validatedData);
    unset($this->inputErrors);
    unset($this->uploadFiles);
    if (isset($this->fields) && is_array($this->fields)) {
      foreach ($this->fields as $field) {
        if ($field['type'] == 'file' || $field['type'] == 'imagefile') {
          if (isset($oldData[$field['name']])) {
            $this->validatedData[$field['name']] = $oldData[$field['name']];
          }
          if (($uploadData = $this->getFileUploadData($field['name'])) &&
              is_uploaded_file($uploadData['tmp_name'])) {
            // the upload is executed later - only if the data is requested
            if ($field['type'] == 'imagefile') {
              list(, , $imageType) = getimagesize($uploadData['tmp_name']);
              if ($imageType >= 1 && $imageType <= 3) {
                $uploadData['folder'] =
                  empty($field['typeparams']['folder']) ?
                    0 : (int)$field['typeparams']['folder'];
                $this->uploadFiles[$field['name']] = $uploadData;
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gtf('The input in field "%s" is not correct.', $field['title'])
                );
                $this->inputErrors[$field['name']] = $field['title'];
              }
            } else {
              $uploadData['folder'] = empty($field['typeparams']['folder']) ?
                0 : (int)$field['typeparams']['folder'];
              $this->uploadFiles[$field['name']] = $uploadData;
            }
          }
        } elseif ($field['type'] == 'checkbox') {
          $valueOn = (
            isset($field['typeparams']['value_on']) &&
                  trim($field['typeparams']['value_on']) != '')
            ? $field['typeparams']['value_on'] : 'X';
          $valueOff = (
            isset($field['typeparams']['value_off']) &&
                  trim($field['typeparams']['value_off']) != '')
            ? $field['typeparams']['value_off'] : 'O';
          if (isset($this->params[$field['name']]) &&
              $this->params[$field['name']] == $valueOn) {
            $this->validatedData[$field['name']] = $valueOn;
          } elseif ($field['needed']) {
            $this->inputErrors[$field['name']] = $field['title'];
          } else {
            $this->validatedData[$field['name']] = $valueOff;
          }
        } elseif ($field['type'] == 'checkboxes') {
          $items = papaya_strings::splitLines($field['typeparams']['items']);
          if (isset($this->params[$field['name']]) &&
              is_array($this->params[$field['name']])) {
            $inputs = array_flip($this->params[$field['name']]);
          } else {
            $inputs = array();
          }
          $data = FALSE;
          foreach ($items as $item) {
            if (strpos($item, '=') > 0) {
              list($itemKey) = explode('=', $item);
            } else {
              $itemKey = $item;
            }
            if (isset($inputs[$itemKey])) {
              $data[] = $itemKey;
            }
          }
          if (is_array($data)) {
            $this->validatedData[$field['name']] = implode("\n", $data);
          } elseif (!$field['needed']) {
            $this->validatedData[$field['name']] = '';
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gtf(
                'The input in field "%s" is not correct.', $field['title']
              )
            );
            $this->inputErrors[$field['name']] = $field['title'];
          }
        } elseif ($field['type'] == 'captcha') {
          $identifier = (string)$this->params[$field['name']]['captchaident'];
          $answer = (string)$this->params[$field['name']]['captchaanswer'];
          if ($this->checkCaptchaAnswer($answer, $identifier)) {
            $this->validatedData[$field['name']] = $this->params[$field['name']];
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gtf('The input in field "%s" is not correct.', $field['title'])
            );
            $this->inputErrors[$field['name']] = $field['title'];
          }
        } elseif (isset($this->params[$field['name']]) &&
                  (!is_array($this->params[$field['name']])) &&
                  strlen($this->params[$field['name']]) > 0) {
          if ($field['validatefunc'] == -1 && isset($field['validateregex'])) {
            if (@preg_match($field['validateregex'], $this->params[$field['name']])) {
              $this->validatedData[$field['name']] = $this->params[$field['name']];
            } else {
              $this->addMsg(
                MSG_ERROR,
                $this->_gtf('The input in field "%s" is not correct.', $field['title'])
              );
              $this->inputErrors[$field['name']] = $field['title'];
            }
          } elseif (checkit::has($field['validatefunc'])) {
            $checkFunction = $field['validatefunc'];
            if (
                checkit::validate(
                  $this->params[$field['name']], $checkFunction, $field['needed']
                )
               ) {
              $this->validatedData[$field['name']] = $this->params[$field['name']];
            } else {
              $this->addMsg(
                MSG_ERROR,
                $this->_gtf('The input in field "%s" is not correct.', $field['title'])
              );
              $this->inputErrors[$field['name']] = $field['title'];
            }
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gtf('Invalid check function for field "%s"', $field['name'])
            );
            $this->inputErrors[$field['name']] = $field['title'];
          }
        } elseif ($field['needed'] == FALSE) {
          $this->validatedData[$field['name']] = '';
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gtf('The input in field "%s" is not correct.', $field['title'])
          );
          $this->inputErrors[$field['name']] = $field['title'];
        }
      }
    }
    if (isset($this->inputErrors) && count($this->inputErrors) > 0) {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
  * Get file upload data
  *
  * @param string $fieldName
  * @access public
  * @return mixed
  */
  function getFileUploadData($fieldName) {
    if (isset($_FILES) && is_array($_FILES) && isset($_FILES[$this->paramName]) &&
        isset($_FILES[$this->paramName]['name'][$fieldName]) &&
        isset($_FILES[$this->paramName]['tmp_name'][$fieldName]) &&
        is_uploaded_file($_FILES[$this->paramName]['tmp_name'][$fieldName])) {
      return array(
        'name' => $_FILES[$this->paramName]['name'][$fieldName],
        'tmp_name' => $_FILES[$this->paramName]['tmp_name'][$fieldName],
        'error' => $_FILES[$this->paramName]['error'][$fieldName]
      );
    } else {
      return FALSE;
    }
  }

  /**
  * Get dialog inputs
  *
  * @access public
  * @return mixed
  */
  function getDialogInputs() {
    $this->uploadMediaFiles();
    if (isset($this->validatedData)) {
      return $this->validatedData;
    } else {
      return NULL;
    }
  }

  /**
  * Upload media files.
  *
  * All temp files listed in the uploadFiles array
  * are inserted into the MediaDB. The resulting fileId is subsequently
  * added to the $this->validatedData array so that the reference to the file /
  * image will also be stored in the form data.
  *
  * @access public
  * @return bool TRUE if $mediaDB->addFile is successful or the uploadFiles
  *              array is empty, otherwise FALSE
  */
  function uploadMediaFiles() {
    if (isset($this->uploadFiles) && is_array($this->uploadFiles)) {
      $mediaDB = new base_mediadb_edit;
      $surferId = $this->papaya()->surfer->surferId;
      foreach ($this->uploadFiles as $fieldName => $uploadData) {
        if (isset($uploadData['folder'])) {
          if ($folder = $mediaDB->getFolder($uploadData['folder'])) {
            $fileId = $mediaDB->addFile(
              $uploadData['tmp_name'],
              $uploadData['name'],
              $uploadData['folder'],
              $surferId,
              '',
              'uploaded_file'
            );
            if ($fileId) {
              // Max, 05.09.2008: we need to insert the $fileId into the validatedData array,
              // since this cannot be done in the checkDialogInputs function. We can say
              // that a file is validated as being correct when checkDialogInputs inserts
              // the file name into the uploadFiles array
              $this->validatedData[$fieldName] = $fileId;
              // couldn't figure out, what the deleteFile in previous version meant,
              // since $fieldName was never initialized
              return TRUE;
            }
          }
        }
      }
      return FALSE;
    }
    return TRUE;
  }

  /**
  * Tansform XML to fields
  *
  * @param string $xml
  * @access public
  */
  function xmlToFields($xml) {
    unset($this->groups);
    unset($this->fields);
    $xmlTree = simple_xmltree::createFromXML($xml, $this);
    if (isset($xmlTree) && isset($xmlTree->documentElement) &&
        $xmlTree->documentElement->hasChildNodes()) {
      for ($idx = 0; $idx < $xmlTree->documentElement->childNodes->length; $idx++) {
        $node = $xmlTree->documentElement->childNodes->item($idx);
        if (isset($node) && $node instanceof DOMElement) {
          switch ($node->nodeName) {
          case 'group':
            unset($group);
            if ($node->hasChildNodes()) {
              $group = array(
                'title' => $node->hasAttribute('title') ?
                  $node->getAttribute('title') : $this->_gt('Group'),
              );
              for ($idx2 = 0; $idx2 < $node->childNodes->length; $idx2++) {
                $fieldNode = $node->childNodes->item($idx2);
                unset($field, $legend);
                if ($fieldNode instanceof DOMElement
                    && $fieldNode->nodeName == 'legend') {
                  $group['legend'] = $fieldNode->nodeValue;
                }
                if ($fieldNode instanceof DOMElement
                    && $fieldNode->nodeName == 'text') {
                  $group['legend-text'] = $fieldNode->nodeValue;
                }
                if ($fieldNode instanceof DOMElement && $fieldNode->nodeName == 'field') {
                  if ($fieldNode->hasAttribute('name')) {
                    $field = array(
                      'group' => $group['title'],
                      'name' => $fieldNode->getAttribute('name'),
                      'title' =>
                        $fieldNode->hasAttribute('title') ? $fieldNode->getAttribute('title') : '',
                      'css' =>
                        $fieldNode->hasAttribute('css') ? $fieldNode->getAttribute('css') : '',
                      'validatefunc' =>
                        $fieldNode->hasAttribute('validatefunc') ?
                          $fieldNode->getAttribute('validatefunc') : '',
                      'validateregex' =>
                        $fieldNode->hasAttribute('validateregex') ?
                          $fieldNode->getAttribute('validateregex') : '',
                      'needed' =>
                        $fieldNode->hasAttribute('needed') ?
                          $fieldNode->getAttribute('needed') : '',
                      'type' => 'input',
                      'typeparams' => array()
                    );
                    if ($fieldNode->hasChildNodes()) {
                      for ($idx3 = 0; $idx3 < $fieldNode->childNodes->length; $idx3++) {
                        $typeNode = $fieldNode->childNodes->item($idx3);
                        if ($typeNode instanceof DOMElement && $typeNode->nodeName != '') {
                          $field['type'] = $typeNode->nodeName;
                          if ($typeNode->hasChildNodes()) {
                            for ($idx4 = 0; $idx4 < $typeNode->childNodes->length; $idx4++) {
                              $paramNode = $typeNode->childNodes->item($idx4);
                              $field['typeparams'][$paramNode->nodeName] = $paramNode->nodeValue;
                            }
                          }
                          break;
                        }
                      }
                    }
                    $this->fields[$field['name']] = $field;
                    $group['fields'][] = $field['name'];
                  }
                }
              }
              $this->groups[$group['title']] = $group;
            }
            break;
          }
        }
      }
    }
    simple_xmltree::destroy($xmlTree);
  }

  /**
  * Get field XML for an input field
  *
  * @param array &$field
  * @param string $value
  * @param string $cssClassName
  * @access public
  * @return string HTML
  */
  function getFieldXMLInput(&$field, $value, $cssClassName) {
    if (isset($field['typeparams']['maxlength']) &&
        $field['typeparams']['maxlength'] > 0) {
      $maxLength = ' maxlength="'.(int)$field['typeparams']['maxlength'].'"';
    } else {
      $maxLength = '';
    }
    return sprintf(
      '<input type="text" name="%s" class="%s" value="%s" %s/>',
      papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
      $cssClassName,
      papaya_strings::escapeHTMLChars($value),
      $maxLength
    );
  }

  /**
  * Get field XML for a checkbox
  *
  * @param array &$field
  * @param string $value
  * @param string $cssClassName
  * @access public
  * @return string HTML
  */
  function getFieldXMLCheckbox(&$field, $value, $cssClassName) {
    $valueOnCheck = isset($field['typeparams']['value_on']) &&
      trim($field['typeparams']['value_on']) != '';
    $valueOn = ($valueOnCheck) ? $field['typeparams']['value_on'] : 'X';
    $captionCheck = isset($field['typeparams']['caption']) &&
      trim($field['typeparams']['caption']) != '';
    $caption = ($captionCheck) ? $field['typeparams']['caption'] : $valueOn;
    if ((!isset($value)) || (trim($value) == '')) {
      $checked = (!empty($field['typeparams']['default'])) ? ' checked="checked"' : '';
    } elseif ($valueOn == $value) {
      $checked = ' checked="checked"';
    } else {
      $checked = '';
    }
    return sprintf(
      '<input type="checkbox" name="%s" class="%s" value="%s"%s>%s</input>',
      papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
      papaya_strings::escapeHTMLChars($cssClassName),
      papaya_strings::escapeHTMLChars($valueOn),
      $checked,
      papaya_strings::escapeHTMLChars($caption)
    );
  }

  /**
  * Get field XML for a group of checkboxes
  *
  * @param array &$field
  * @param string $value
  * @param string $cssClassName
  * @access public
  * @return string HTML
  */
  function getFieldXMLCheckboxes(&$field, $value, $cssClassName) {
    $result = '';
    if (isset($value) && is_array($value)) {
      $values = $value;
    } else {
      $values = papaya_strings::splitLines($value);
    }
    if (isset($values) && is_array($values)) {
      $values = array_flip(array_values($values));
    } else {
      $values = array();
    }
    if (isset($field['typeparams']['items'])) {
      $items = papaya_strings::splitLines($field['typeparams']['items']);
    } else {
      $items = NULL;
    }
    if (isset($items) && count($items) > 0) {
      foreach ($items as $item) {
        if (strpos($item, '=') > 0) {
          list($itemKey, $itemVal) = explode('=', $item, 2);
        } else {
          $itemKey = $item;
          $itemVal = $item;
        }
        $selected = (isset($values[$itemKey])) ? ' checked="checked"' : '';
        $result .= sprintf(
          '<input type="checkbox" name="%s[]" value="%s" class="%s" %s>%s</input>',
          papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
          papaya_strings::escapeHTMLChars($itemKey),
          papaya_strings::escapeHTMLChars($cssClassName),
          $selected,
          papaya_strings::escapeHTMLChars($itemVal)
        );
      }
    }
    return $result;
  }

  /**
  * Get field XML for a combobox
  *
  * @param array &$field
  * @param string $value value that willbe signed as selected
  * @param string $cssClassName
  * @access public
  * @return string
  */
  function getFieldXMLCombobox(&$field, $value, $cssClassName) {
    $result = sprintf(
      '<select name="%s" class="%s">',
      papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
      papaya_strings::escapeHTMLChars($cssClassName)
    );
    if (isset($field['typeparams']['items'])) {
      $items = papaya_strings::splitLines($field['typeparams']['items']);
    } else {
      $items = NULL;
    }
    if (isset($items) && count($items) > 0) {
      foreach ($items as $item) {
        if (strpos($item, '=') > 0) {
          list($itemKey, $itemVal) = explode('=', $item, 2);
        } else {
          $itemKey = $item;
          $itemVal = $item;
        }
        $selected = ($value == $itemKey) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>',
          papaya_strings::escapeHTMLChars($itemKey),
          $selected,
          papaya_strings::escapeHTMLChars($itemVal)
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
   * Generates a select form field to select a country out of one continent.
   * The continent can be selected in the settings of this field with the
   * typeparam <i>continent</i>. Additionally the preselection can be set by
   * the parameter <i>preselection</i>.
   *
   * @author Sebastian Janzen <info@papaya-cms.com>
   * @param array &$field Fieldsettings e.g. with Type Params
   * @param string $value Currently selected value
   * @param string $cssClassName CSS Class this element have to be set to
   * @access public
   * @return string XHTML select with countries
   */
  function getFieldXMLCountry(&$field, $value, $cssClassName) {
    // Get Instance of the country module
    $countriesObj = $this->papaya()->plugins->get('99db2c2898403880e1ddeeebf7ee726c', $this);

    // Check whether the module has successfully beein loaded
    if (!is_object($countriesObj)) {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('Module Countries could not be loaded from base_dynform!'),
        TRUE
      );
      return NULL;
    }

    if ($value > 0) {
      $selection = $value;
    } elseif ((isset($field['typeparams']['preselection'])) &&
                ($field['typeparams']['preselection'] != '')) {
      $selection = $field['typeparams']['preselection'];
    } else {
      $selection = NULL;
    }

    $continent = (isset($field['typeparams']['continent']))
      ? $field['typeparams']['continent'] : NULL;

    // Generate select list of countries, filtered by continent
    $result = sprintf(
      '<select name="%s" class="%s">',
      papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
      papaya_strings::escapeHTMLChars($cssClassName)
    );
    $result .= $countriesObj->getCountryOptionsXHTML($selection, $continent);
    $result .= '</select>';
    return $result;
  }

  /**
  * Get XML for a file upload field
  *
  * @param array &$field
  * @param string $value
  * @param string $cssClassName
  * @access public
  * @return string
  */
  function getFieldXMLFile(&$field, $value, $cssClassName) {
    $result = '';
    if (isset($this->data[$field['name']]) && $this->data[$field['name']] != '') {
      $parser = new papaya_parser;
      $parser->files[$this->data[$field['name']]] = FALSE;
      $parser->getMediaInfos();
      if ($parser->mediaFileExists($this->data[$field['name']])) {
        $result .= '<div style="text-align: center; padding: 4px 0px 10px 0px">';
        $params = array(
          'src' => $this->data[$field['name']],
          'download' => 'yes'
        );
        $result .= $parser->createMediaTag($params);
        $queryParams = array(
          $this->paramName => array(
            'dynform_cmd' => 'delfile',
            'dynform_field' => $field['name']
          )
        );
        $result .= sprintf(
          '<br clear="all"/><a href="%s"><button name="del">%s</button></a>',
          $this->baseLink.'?'.$this->getQueryString($queryParams),
          empty($field['typeparams']['delcaption'])
            ? ''
            : papaya_strings::escapeHTMLChars($field['typeparams']['delcaption'])
        );
        $result .= '</div>';
      }
    }
    $result .= sprintf(
      '<input type="file" name="%s" class="%s"/>',
      papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
      papaya_strings::escapeHTMLChars($cssClassName)
    );
    return $result;
  }

  /**
  * Get field XML for an image upload field
  *
  * @param array &$field
  * @param string $value
  * @param string $cssClassName
  * @access public
  * @return string
  */
  function getFieldXMLImage(&$field, $value, $cssClassName) {
    $result = '';
    if (isset($this->data[$field['name']]) && $this->data[$field['name']] != '') {
      $parser = new papaya_parser;
      $parser->files[$this->data[$field['name']]] = FALSE;
      $parser->getMediaInfos();
      if ($parser->mediaFileExists($this->data[$field['name']])) {
        $result .= '<div style="text-align: center; padding: 4px 0px 10px 0px">';
        $params = array(
          'width' => empty($field['typeparams']['width'])
            ? 0 : (int)$field['typeparams']['width'],
          'height' => empty($field['typeparams']['height'])
            ? 0 : (int)$field['typeparams']['height'],
          'src' => $this->data[$field['name']],
          'align' => 'middle'
        );

        $result .= $parser->createMediaTag($params);
        $queryParams = array(
          $this->paramName => array(
            'dynform_cmd' => 'delfile',
            'dynform_field' => $field['name']
          )
        );
        $result .= sprintf(
          '<br clear="all"/><a href="%s"><button name="del">%s</button></a>',
          $this->baseLink.'?'.$this->getQueryString($queryParams),
          empty($field['typeparams']['delcaption'])
            ? ''
            : papaya_strings::escapeHTMLChars($field['typeparams']['delcaption'])
        );
        $result .= '</div>';
      }
    }
    $result .= sprintf(
      '<input type="file" name="%s" class="%s"/>',
      papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
      papaya_strings::escapeHTMLChars($cssClassName)
    );
    return $result;
  }

  /**
  * Generates a Captcha
  *
  * @param array $field
  * @return string
  *
  * @todo need to be reviewed
  */
  function getFieldXMLCaptcha($field) {
    $result = '';
    /* random id berechnen (session var identifier) */
    srand((double)microtime() * 1000000);
    $randId = md5(uniqid(rand()));
    /* hidden-feld mit id ausgeben */
    $result .= sprintf(
      '<input type="hidden" name="%s[%s][captchaident]"'.
      ' class="dialogInput dialogScale" fid="%s_hidden" value="%s"></input>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($field['name']),
      papaya_strings::escapeHTMLChars($field['name']),
      papaya_strings::escapeHTMLChars($randId)
    );
    /* input-feld ausgeben */
    $result .= sprintf(
      '<input type="text" name="%s[%s][captchaanswer]"'.
      ' class="dialogInput dialogScale" fid="%s"></input>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($field['name']),
      papaya_strings::escapeHTMLChars($field['name'])
    );
    /* img-tag ausgeben */
    $result .= sprintf(
      '<img src="%s.image.jpg?img[identifier]=%s" />'.LF,
      empty($field['typeparams']['identifier'])
        ? ''
        : papaya_strings::escapeHTMLChars($field['typeparams']['identifier']),
      papaya_strings::escapeHTMLChars($randId)
    );
    return $result;
  }

  /**
  * Get field XML for a group of radio fields
  *
  * @param array &$field
  * @param string $value value that willbe signed as selected
  * @param string $cssClassName
  * @access public
  * @return string
  */
  function getFieldXMLRadio(&$field, $value, $cssClassName) {
    $result = '';
    if (isset($field['typeparams']['items'])) {
      $items = papaya_strings::splitLines($field['typeparams']['items']);
    } else {
      $items = NULL;
    }
    if (isset($items) && count($items) > 0) {
      foreach ($items as $item) {
        if (strpos($item, '=') > 0) {
          list($itemKey, $itemVal) = explode('=', $item, 2);
        } else {
          $itemKey = $item;
          $itemVal = $item;
        }
        $selected = ($value == $itemKey) ? ' checked="checked"' : '';
        $result .= sprintf(
          '<input type="radio" name="%s" value="%s" class="%s" %s>%s</input>',
          papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
          papaya_strings::escapeHTMLChars($itemKey),
          papaya_strings::escapeHTMLChars($cssClassName),
          $selected,
          papaya_strings::escapeHTMLChars($itemVal)
        );
      }
    }
    return $result;
  }
  /**
  * Get field XML for a textarea
  *
  * @param array &$field
  * @param string $value
  * @param string $cssClassName
  * @access public
  * @return string
  */
  function getFieldXMLTextarea(&$field, $value, $cssClassName) {
    if (isset($field['typeparams']['maxlength']) &&
        $field['typeparams']['maxlength'] > 0) {
      $rows = (int)$field['typeparams']['maxlength'];
    } else {
      $rows = 5;
    }
    return sprintf(
      '<textarea name="%s" class="%s" rows="%d">%s</textarea>',
      papaya_strings::escapeHTMLChars($this->getFieldName($field['name'])),
      papaya_strings::escapeHTMLChars($cssClassName),
      (int)$rows,
      papaya_strings::escapeHTMLChars($value)
    );
  }

  /**
  * Checks whether the answer given by the user is identical to
  * the identifier generated by the captcha module.
  *
  * @param string $answer Answer given by the user
  * @param string $identifier session data array key for the identifier
  * @return TRUE iff $answer is identical to the identifier, otherwise FALSE
  */
  private function checkCaptchaAnswer($answer, $identifier) {
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
}


