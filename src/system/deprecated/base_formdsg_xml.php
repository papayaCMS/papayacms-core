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
* form designer
*
* @package Papaya
* @subpackage Core
*/
class base_formdesigner_xml extends base_object {
  /**
  * Groups
  * @var array $groups
  */
  var $groups;
  /**
  * Fields
  * @var array $fields
  */
  var $fields;

  /**
  * Types
  * @var array $types
  */
  var $types = array(
    'input' => array(
      'maxlength' => array('Max length', 'isNum', TRUE, 'input', 3, '', 100)
    ),
    'checkbox' => array(
      'value_on' => array('On', 'isNoHTML', TRUE, 'input', 100, '', 'X'),
      'value_off' => array('Off', 'isNoHTML', TRUE, 'input', 100, '', 'O'),
      'default' => array('Default', 'isNum', TRUE, 'combo',
        array(1 => 'Checked', 0 => 'Not checked'), '', 0),
      'caption' => array('Caption', 'isNoHTML', FALSE, 'input', 100, '', '')
    ),
    'checkboxes' => array(
      'items' => array('Items', 'isNoHTML', TRUE, 'textarea', 6, '', 'value=caption')
    ),
    'combobox' => array(
      'items' => array('Items', 'isNoHTML', TRUE, 'textarea', 6, '', 'value=caption')
    ),
    'file' => array(
      'folder' => array('Folder', 'isNum', TRUE, 'function', 'getMediaDirectoryCombo'),
      'delcaption' => array('Caption: Delete button', 'isNoHTML',
        TRUE, 'input', 30, '', 'Delete')
    ),
    'imagefile' => array(
      'folder' => array('Folder', 'isNum', TRUE, 'function', 'getMediaDirectoryCombo'),
      'delcaption' => array('Caption: Delete button', 'isNoHTML',
        TRUE, 'input', 30, '', 'Delete'),
      'width' => array('Width', 'isNum', TRUE, 'input', 4, '', 240),
      'height' => array('Height', 'isNum', TRUE, 'input', 4, '', 180),
      'popup' => array('Popup', 'isAlphaNumChar', FALSE, 'combo',
        array('yes' => 'Yes', 'no' => 'No'), '', '')
    ),
    'image' => array(
      'folder' => array('Folder', 'isNum', TRUE, 'function', 'getMediaDirectoryCombo'),
      'delcaption' => array('Caption: Delete button', 'isNoHTML',
        TRUE, 'input', 30, '', 'Delete'),
      'width' => array('Width', 'isNum', TRUE, 'input', 4, '', 240),
      'height' => array('Height', 'isNum', TRUE, 'input', 4, '', 180),
      'popup' => array('Popup', 'isAlphaNumChar', FALSE, 'combo',
        array('yes' => 'Yes', 'no' => 'No'), '', '')
    ),
    'captcha' => array(
      'identifier' => array('Captcha', 'isAlphaNum', TRUE, 'function', 'getCaptchasCombo'),
    ),
    'radio' => array(
      'items' => array('Items', 'isNoHTML', TRUE, 'textarea', 6, '', 'value=caption')
    ),
    'textarea' => array(
      'rows' => array('Rows', 'isNum', TRUE, 'input', 3, '', 5)
    ),
    'url' => array(
      'maxlength' => array('Max length', 'isNum', TRUE, 'input', 3, '', 200),
      'linktext' => array('Link text', 'isAlphaNumChar', FALSE, 'input', 40, '', ''),
      'target' => array('Link target', 'isAlphaNumChar', FALSE, 'combo',
        array('_blank' => '_blank', '_parent' => '_parent',
              '_self' => '_self', '_top' => '_top'), '', '_self')
    ),
    'country' => array (
      'continent' => array('Continent', 'isNum', FALSE, 'function', 'getContinents'),
      'preselection' => array('Preselection', 'isNoHTML', FALSE, 'function', 'getCountries')
    )
  );

  /**
  * XML to fields
  *
  * @param string $xml
  * @access public
  */
  function xmlToFields($xml) {
    $xmlTree = simple_xmltree::createFromXML($xml, $this);
    if (isset($xmlTree) && isset($xmlTree->documentElement) &&
        $xmlTree->documentElement->hasChildNodes()) {
      for ($idx = 0; $idx < $xmlTree->documentElement->childNodes->length; $idx++) {
        $node = $xmlTree->documentElement->childNodes->item($idx);
        if ($node instanceof DOMElement) {
          switch ($node->nodeName) {
          case 'group':
            unset($group);
            if ($node->hasChildNodes()) {
              $group = array(
                'title' => $node->hasAttribute('title')
                  ? $node->getAttribute('title') : $this->papaya()->administrationPhrases->get('Group')
              );
              for ($idx2 = 0; $idx2 < $node->childNodes->length; $idx2++) {
                $fieldNode = $node->childNodes->item($idx2);
                unset($field);
                if ($fieldNode instanceof DOMElement
                    && ($fieldNode->nodeName == 'text')) {
                  $group['text'] = $fieldNode->nodeValue;
                }
                if ($fieldNode instanceof DOMElement
                    && ($fieldNode->nodeName == 'field')) {
                  if ($fieldNode->hasAttribute('name')) {
                    $field = array(
                      'group' => $group['title'],
                      'name' =>
                        $fieldNode->hasAttribute('name') ? $fieldNode->getAttribute('name') : '',
                      'title' =>
                        $fieldNode->hasAttribute('title') ? $fieldNode->getAttribute('title') : '',
                      'css' =>
                        $fieldNode->hasAttribute('css') ? $fieldNode->getAttribute('css') : '',
                      'validatefunc' =>
                        $fieldNode->hasAttribute('validatefunc')
                          ? $fieldNode->getAttribute('validatefunc') : '',
                      'validateregex' =>
                        $fieldNode->hasAttribute('validateregex')
                          ? $fieldNode->getAttribute('validateregex') : '',
                      'needed' =>
                        $fieldNode->hasAttribute('needed')
                          ? $fieldNode->getAttribute('needed') : '',
                      'type' => 'input',
                      'typeparams' => array()
                    );
                    if ($fieldNode->hasChildNodes()) {
                      for ($idx3 = 0; $idx3 < $fieldNode->childNodes->length; $idx3++) {
                        $typeNode = $fieldNode->childNodes->item($idx3);
                        if ($typeNode instanceof DOMElement &&
                            isset($this->types[$typeNode->nodeName])) {
                          $field['type'] = $typeNode->nodeName;
                          $fieldType = $this->types[$field['type']];
                          if ($typeNode->hasChildNodes()) {
                            for ($idx4 = 0; $idx4 < $typeNode->childNodes->length; $idx4++) {
                              $paramNode = $typeNode->childNodes->item($idx4);
                              if ($paramNode instanceof DOMElement) {
                                if (isset($fieldType[$paramNode->nodeName])) {
                                  $field['typeparams'][$paramNode->nodeName] =
                                    $paramNode->nodeValue;
                                }
                              }
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
  }

  /**
  * Fields to XML
  *
  * @access public
  * @return string XML
  */
  function fieldsToXML() {
    $result = '<fields>'.LF;
    if (isset($this->groups) && is_array($this->groups)) {
      foreach ($this->groups as $group) {
        $result .= sprintf(
          '<group title="%1$s">'.LF.'<legend>%1$s</legend>',
          papaya_strings::escapeHTMLChars($group['title'])
        );
        if (isset($group['text'])) {
          $result .= sprintf(
            '<text>%s</text>'.LF,
            papaya_strings::escapeHTMLChars($group['text'])
          );
        }
        if (isset($group['fields']) && is_array($group['fields'])) {
          foreach ($group['fields'] as $fieldName) {
            if (isset($this->fields[$fieldName])) {
              $field = $this->fields[$fieldName];
              $result .= sprintf(
                '<field name="%s" title="%s" css="%s" validatefunc="%s"'.
                  ' validateregex="%s" needed="%d">'.LF,
                empty($field['name']) ? '' : papaya_strings::escapeHTMLChars($field['name']),
                empty($field['title']) ? '' : papaya_strings::escapeHTMLChars($field['title']),
                empty($field['css']) ? '' : papaya_strings::escapeHTMLChars($field['css']),
                empty($field['validatefunc'])
                  ? '' : papaya_strings::escapeHTMLChars($field['validatefunc']),
                empty($field['validateregex'])
                  ? '' : papaya_strings::escapeHTMLChars($field['validateregex']),
                empty($field['needed']) ? 0 : (int)($field['needed'])
              );
              if (isset($field['type'])) {
                $result .= '<'.papaya_strings::escapeHTMLChars($field['type']).'>'.LF;
                if (isset($field['typeparams']) && is_array($field['typeparams'])) {
                  foreach ($field['typeparams'] as $attributeName => $attributeValue) {
                    if (empty($attributeName)) {
                      $attributeName = 'n'.(int)$attributeName;
                    }
                    $result .= '<'.papaya_strings::escapeHTMLChars($attributeName).'><![CDATA['.
                      papaya_strings::escapeHTMLChars($attributeValue).']]></'.
                      papaya_strings::escapeHTMLChars($attributeName).'>'.LF;
                  }
                }
                $result .= '</'.papaya_strings::escapeHTMLChars($field['type']).'>'.LF;
              }
              $result .= '</field>'.LF;
            }
          }
        }
        $result .= '</group>'.LF;
      }
    }
    $result .= '</fields>'.LF;
    return $result;
  }
}

