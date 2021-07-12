<?php
/**
* <author> and <contributor> describe a person, corporation, or similar entity.
* It has one required element, name, and two optional elements: uri, email.
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
* @version $Id: papaya_atom_person.php 39721 2014-04-07 13:13:23Z weinert $
*/


/**
* <author> and <contributor> describe a person, corporation, or similar entity.
* It has one required element, name, and two optional elements: uri, email.
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_person extends papaya_atom_element {

  /**
  * conveys a human-readable name for the person.
  * @var string
  */
  var $name = '';

  /**
  * contains a home page for the person.
  * @var string
  */
  var $uri = '';

  /**
  * contains an email address for the person.
  * @var string
  */
  var $email = '';

  /**
  * constructor - initialize properties
  *
  * @param papaya_atom_feed $feed reference of feed object
  * @param string $name conveys a human-readable name for the person.
  * @param string $uri optional, contains a home page for the person.
  * @param string $email optional, contains an email address for the person.
  * @access public
  */
  function __construct(papaya_atom_feed $feed, $name, $uri = '', $email = '') {
    $this->_feed = $feed;
    $this->name = $name;
    $this->uri = $uri;
    $this->email = $email;
  }

  /**
  * assign data of another person object
  *
  * @param papaya_atom_person $person
  * @access public
  * @return papaya_atom_person boolean
  */
  function assign($person) {
    if ($person instanceof papaya_atom_person) {
      $this->name = $person->name;
      $this->uri = $person->uri;
      $this->email = $person->email;
      return TRUE;
    }
    return FALSE;
  }


  /**
  * return person data as xml
  *
  * @param string $tagName name of the tag to save the element
  * @access public
  * @return string
  */
  function saveXML($tagName) {
    if (!empty($this->name)) {
      $result = '<'.$tagName.'>'."\n";
      $result .= '<name>'.htmlspecialchars($this->name).'</name>'."\n";
      if (!empty($this->uri)) {
        $result .= '<uri>'.htmlspecialchars($this->uri).'</uri>'."\n";
      }
      if (!empty($this->email)) {
        $result .= '<email>'.htmlspecialchars($this->email).'</email>'."\n";
      }
      $result .= '</'.$tagName.'>'."\n";
      return $result;
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
    $this->name = '';
    $this->uri = '';
    $this->email = '';
    if ($node->hasChildNodes()) {
      for ($i = 0; $i < $node->childNodes->length; $i++) {
        $childNode = $node->childNodes->item($i);
        if ($childNode instanceof DOMElement &&
            in_array($childNode->nodeName, array('name', 'uri', 'email'))) {
          if (method_exists($childNode, 'valueOf')) {
            $buffer = $childNode->valueOf();
          } else {
            $buffer = $childNode->nodeValue;
          }
          switch ($childNode->nodeName) {
          case 'name' :
            $this->name = $buffer;
            break;
          case 'uri' :
            $this->name = $buffer;
            break;
          case 'email' :
            $this->name = $buffer;
            break;
          }
        }
      }
    }
  }
}

