<?php
/**
* <content> either contains, or links to, the complete content of the entry.
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
* @version $Id: papaya_atom_content.php 39626 2014-03-19 12:43:41Z weinert $
*/


/**
* <content> either contains, or links to, the complete content of the entry.
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_content extends papaya_atom_text {

  /**
  * The type attribute determines how this information is encoded (default="text")
  * @var string
  * @access private
  */
  var $_type = 'text';

  /**
  * If the src attribute is present, it represents
  * the URI of where the content can be found.
  * @var string
  * @access private
  */
  var $_src = NULL;

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
  * @param string $type The type attribute determines how
                        this information is encoded (default="text")
  * @param string $value optional, internal content
  * @param string $src optional, if the src attribute is present,
  *                    it represents the URI of where the content can be found.
  * @access public
  */
  function __construct(papaya_atom_feed $feed, $type, $value = NULL, $src = NULL) {
    $this->_feed = $feed;
    $this->setType($type);
    $this->setValue($value);
    $this->setSrc($src);
  }

  /**
  * assign data of another content object
  *
  * @param papaya_atom_content $content
  * @access public
  * @return papaya_atom_content boolean
  */
  function assign($content) {
    if ($content instanceof papaya_atom_content) {
      $this->setType($content->getType());
      $this->setValue($content->getValue());
      $this->setSrc($content->getSrc());
      return TRUE;
    }
    return FALSE;
  }


  /**
  * set value of this element - deletes src
  *
  * @param $value
  * @access public
  * @return boolean
  */
  function setValue($value) {
    if (!empty($value)) {
      $this->_value = $value;
      $this->_src = NULL;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * set src of this element - deletes value
  *
  * @param string $src
  * @access public
  * @return boolean
  */
  function setSrc($src) {
    if (!empty($src)) {
      $this->_value = NULL;
      $this->_src = $this->_feed->getAbsoluteHref($src);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * get the src of this element
  *
  * @access public
  * @return string
  */
  function getSrc() {
    if (!empty($this->_src)) {
      return $this->_src;
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
    $value = parent::saveXML($tagName);
    if (empty($value) && !empty($this->_src)) {
      return '<'.$tagName.' type="'.
        htmlspecialchars($this->_type).'" src="'.
        htmlspecialchars($this->_src).'"></'.$tagName.'>'."\n";
    } else {
      return $value;
    }
  }

  /**
  * load content data from xml
  *
  * @param DOMElement $node
  * @access public
  */
  function load($node) {
    $this->_type = 'text';
    if ($node->hasAttribute('type')) {
      $this->_type = $node->getAttribute('type');
    }
    //is here an src attribute and do the type is not an internal type
    if ($node->hasAttribute('src') &&
        !in_array($this->_type, array('text', 'html', 'xhtml'))) {
      $this->setSrc($node->getAttribute('src'));
    } else {
      parent::load($node);
    }
  }

  /**
  * check if this element has content
  *
  * @access public
  * @return boolean
  */
  function isEmpty() {
    if (!empty($this->_src)) {
      return FALSE;
    } else {
      return parent::isEmpty();
    }
  }
}

