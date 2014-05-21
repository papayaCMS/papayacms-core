<?php
/**
* Atom element class
*
* represents an Atom feed element - a single value, it's type, ...
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
* @version $Id: papaya_atom_element.php 38664 2013-09-09 18:54:43Z weinert $
*/

/**
* atom element class
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_element {

  /**
  *  reference of feed object
  * @var papaya_atom_feed
  */
  var $_feed = NULL;

  /**
  * constructor
  *
  * @param papaya_atom_feed $feed reference of feed object
  * @access public
  */
  function __construct(papaya_atom_feed $feed) {
    $this->_feed = $feed;
  }

  /**
  * return the feed of this element
  *
  * @access public
  * @return papaya_atom_feed
  */
  function feed() {
    return $this->_feed;
  }

  /**
  * assign content from another papaya_atom_node
  *
  * abstract function - needs specific implementations
  *
  * @param papaya_atom_element $element
  * @access public
  * @return boolean
  */
  function assign($element) {
    return FALSE;
  }

  /**
  * return content of an element as xml
  *
  * @param string $tagName name of the tag to save the element
  * @access public
  * @return string
  */
  function saveXML($tagName) {
    return '';
  }

  /**
  * load content from a DOMElement (or papaya_expat_element_node)
  *
  * abstract function - needs specific implementations
  *
  * @param DOMElement $node
  * @access public
  */
  function load($node) {

  }
}

