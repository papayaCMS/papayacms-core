<?php
/**
* XML handling factory class
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
* @subpackage XML
* @version $Id: sys_simple_xmltree.php 39602 2014-03-18 14:21:20Z weinert $
*/

/**
* @package Papaya-Library
* @subpackage XML
*/
class simple_xmltree {

  /**
  * create an XML tree object
  *
  * @access public
  * @return DOMDocument|NULL xml document object
  */
  function create() {
    $path = dirname(__FILE__).'/xml/';
    include_once($path.'papaya_dom_element_node.php');
    include_once($path.'papaya_dom_text_node.php');
    // DomDocument takes version and encoding as parameters, if neccessary
    $tree = new DomDocument('1.0', 'UTF-8');
    $tree->registerNodeClass('DOMElement', 'papaya_dom_element_node');
    $tree->registerNodeClass('DOMText', 'papaya_dom_text_node');
    if (function_exists('libxml_use_internal_errors')) {
      libxml_use_internal_errors(TRUE);
    }
    return $tree;
  }

  /**
  * Handle lib xml error reporting
  * @param string $errorType
  * @return array|NULL
  */
  function handleLibxmlErrors($errorType = 'xml') {
    $errors = libxml_get_errors();
    if (is_array($errors) && count($errors) > 0) {
      foreach ($errors as $error) {
        if ($error->level >= 3) {
          $lastError[0] = $errorType;
          $lastError[1] = array(
            'errorno' => $error->code,
            'error' => $error->message,
            'line' => $error->line
          );
          return $lastError;
        }
      }
    }
    libxml_clear_errors();
    return NULL;
  }

  /**
  * create an xml tree from and load data
  *
  * @param string $xmlData
  * @param object $owner
  * @access public
  * @return DOMDocument|NULL xml document object
  */
  public static function createFromXML($xmlData, $owner) {
    if (trim($xmlData) != '') {
      $xmlTree = simple_xmltree::create();
      if (is_object($xmlTree)) {
        if (@$xmlTree->loadXML($xmlData)) {
          if (is_object($xmlTree->documentElement)) {
            return $xmlTree;
          }
        } elseif (defined('PAPAYA_DBG_XML_USERINPUT') &&
                  PAPAYA_DBG_XML_USERINPUT === '1' &&
                  is_object($owner)) {
          if (is_a($xmlTree, 'DOMDocument')) {
            $owner->lastXMLError = simple_xmltree::handleLibxmlErrors();
          } else {
            $owner->lastXMLError = empty($xmlTree->lastError) ? '' : $xmlTree->lastError;
          }
        }
        simple_xmltree::destroy($xmlTree);
        unset($xmlTree);
      }
    }
    $result = FALSE;
    return $result;
  }

  /**
  * destroy an xmltree instance
  *
  * @param object $xmlTree
  * @access public
  */
  public static function destroy($xmlTree) {
    if (isset($xmlTree) && is_object($xmlTree)) {
      if (method_exists($xmlTree, 'free')) {
        $xmlTree->free();
      }
      unset($xmlTree);
    }
  }

  /**
  * Transform normal string into XML-string
  *
  * Transform all non-alphanumeric characters into ASCII safe entities of character
  *
  * @param string $str string
  * @param boolean $encodeXMLChars optional, default value FALSE
  * @access public
  * @return string $result transformed
  */
  function strToXML($str, $encodeXMLChars = FALSE) {
    if ($encodeXMLChars) {
      return papaya_strings::utf8ToUnicodeEntities(papaya_strings::escapeHTMLChars($str));
    } else {
      return papaya_strings::utf8ToUnicodeEntities($str);
    }
  }

  /**
  * Transform XML string into normal string
  *
  * @param string $str XML-string
  * @access public
  * @return string $result transformed
  */
  function XMLtoStr($str) {
    //for compatibility - old style encoding
    $str = preg_replace_callback(
      "(\&\#([0-9]{1,3});)",
      array('simple_xmltree', 'XMLToStrCallback'),
      $str
    );
    //decode the unicode entities
    return papaya_strings::unicodeEntitiesToUTF8($str);
  }

  /**
  * Convert byte number in match to char
  * @see simple_xmltree::XMLtoStr
  * @param array $match
  * @return string
  */
  function XMLToStrCallback($match) {
    return chr($match[1]);
  }

  /**
  * Get a list of empty tags
  * @return string
  */
  function getEmptyTags() {
    return array('area', 'base', 'basefont', 'br', 'col', 'frame', 'hr', 'img', 'input',
    'isindex', 'link', 'meta', 'param');
  }

  /**
  * Unserialize array from xml
  *
  * @param string $tagName
  * @param array &$dataArr
  * @param string $str
  * @access public
  */
  function unserializeArrayFromXML($tagName, &$dataArr, $str) {
    $dataArr = PapayaUtilStringXml::unserializeArray($str);
  }

  /**
  * serialize an php array to xml
  *
  * this function adds xml root tags around the xml elements
  *
  * @param string $tagName
  * @param array $dataArr
  * @access public
  * @return string
  */
  function serializeArrayToXML($tagName, $dataArr) {
    return PapayaUtilStringXml::serializeArray($dataArr, $tagName);
  }

  /**
  * Chek string is wellformed xml
  * @param string $str
  * @param object $owner
  * @return boolean
  */
  function isXML($str, $owner) {
    $result = FALSE;
    if (!empty($str)) {
      $xmlTree = simple_xmltree::create();
      if (@$xmlTree->loadXML($str)) {
        $result = TRUE;
      } elseif (defined('PAPAYA_DBG_XML_USERINPUT') &&
                PAPAYA_DBG_XML_USERINPUT === '1' &&
                is_object($owner)) {
        if (is_a($xmlTree, 'DOMDocument')) {
          $owner->lastXMLError = @simple_xmltree::handleLibxmlErrors();
        } else {
          $owner->lastXMLError = empty($xmlTree->lastError) ? '' : $xmlTree->lastError;
        }
      }
      simple_xmltree::destroy($xmlTree);
      unset($xmlTree);
    }
    return $result;
  }
}