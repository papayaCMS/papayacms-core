<?php
/**
* <title>, <summary>, <content>, and <rights> contain human-readable text,
* usually in small quantities.
* The type attribute determines how this information is encoded (default="text")
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
* @subpackage XML-Feed
* @version $Id: papaya_atom_text.php 39644 2014-03-20 10:31:16Z weinert $
*/


/**
* <title>, <summary>, <content>, and <rights> contain human-readable text,
* usually in small quantities.
* The type attribute determines how this information is encoded (default="text")
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_text extends papaya_atom_element {
   /**
  * The type attribute determines how this information is encoded (default="text")
  * @var string
  * @access private
  */
  var $_type = 'text';

  /**
  * contains the content value of this element.
  * @var string
  * @access private
  */
  var $_value = NULL;
   /**
  * constructor - initialize properties
  *
  * @param papaya_atom_feed $feed reference of feed object
  * @param string $value
  * @param string $type optional, default value 'text'
  * @access public
  */
  function __construct(papaya_atom_feed $feed, $value, $type = 'text') {
    $this->_feed = $feed;
    $this->setType($type);
    $this->setValue($value);
  }

  /**
  * assign data of another text object
  *
  * @param papaya_atom_text $text
  * @access public
  * @return papaya_atom_text boolean
  */
  function assign($text) {
    if ($text instanceof papaya_atom_text) {
      $this->setType($text->getType());
      $this->setValue($text->getValue());
      return TRUE;
    }
    return FALSE;
  }

  /**
  * set (mime)type of this element
  *
  * @param string $type
  * @access public
  */
  function setType($type) {
    $this->_type = $type;
  }

  /**
  * get (mime)type of this element
  *
  * @access public
  * @return string
  */
  function getType() {
    if (!empty($this->_type)) {
      return $this->_type;
    }
    return 'text';
  }

  /**
  * set value of this element
  *
  * @param $value
  * @access public
  * @return boolean
  */
  function setValue($value) {
    if (!empty($value)) {
      $this->_value = $value;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * get the current value - encoding depends on the type
  *
  * @param boolean $encoded optional, get encoded content?
  * @access public
  * @return string
  */
  function getValue($encoded = FALSE) {
    if (!empty($this->_value)) {
      if ($encoded) {
        switch ($this->_type) {
        case 'text' :
        case 'html' :
          return htmlspecialchars($this->_value);
        case 'xhtml' :
          return $this->_value;
        }
        $typeEnd = substr($this->_type, -4);
        if ($typeEnd == '+xml' || $typeEnd == '/xml') {
          return $this->_value;
        } elseif (substr($this->_type, 0, -4) == 'text') {
          return htmlspecialchars($this->_value);
        } else {
          return base64_encode($this->_value);
        }
      } else {
        return $this->_value;
      }
    }
    return '';
  }

  /**
  * save data of this element to xml
  *
  * @param string $tagName
  * @access public
  * @return string
  */
  function saveXML($tagName) {
    $value = $this->getValue(TRUE);
    if (!empty($value)) {
      return '<'.$tagName.' type="'.
        htmlspecialchars($this->_type).'">'.$value.'</'.$tagName.'>'."\n";
    }
    return '';
  }

  /**
  * load text data from xml node
  *
  * @param $node
  * @access public
  */
  function load($node) {
    $this->_type = 'text';
    if ($node->hasAttribute('type')) {
      $this->_type = $node->getAttribute('type');
    }
    $typeEnd = substr($this->_type, -4);
    if ($this->_type == 'xhtml' || $typeEnd == '+xml' || $typeEnd == '/xml') {
      //read xml subnodes
      if ($node->hasChildNodes()) {
        $xmlString = '';
        for ($i = 0; $i < $node->childNodes->length; $i++) {
          $childNode = $node->childNodes->item($i);
          $xmlString .= $childNode->ownerDocument->saveXML($childNode);
        }
      }
    } elseif ($this->_type == 'text' ||
              $this->_type == 'html' ||
              substr($this->_type, 0, 5) == 'text/') {
      //ok it is just text
      $this->setValue($node->nodeValue);
    } else {
      // uhh base64 encoded binary data - funny stuff
      $this->setValue(base64_decode($node->nodeValue));
    }
  }

  /**
  * check if this element has content
  *
  * @access public
  * @return boolean
  */
  function isEmpty() {
    if (!empty($this->_value)) {
      return FALSE;
    } else {
      return TRUE;
    }
  }
}

