<?php
/**
* Atom elements list class
*
* basic class for all lists in a feed
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
* @version $Id: papaya_atom_element_list.php 39626 2014-03-19 12:43:41Z weinert $
*/

/**
* atom elements list class
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_element_list
  implements IteratorAggregate, Countable {

  /**
  * internal elements list
  * @var array:papaya_atom_element
  * @access private
  */
  var $_elements = array();

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
  * add a new element to the list
  *
  * needs specific implementations
  *
  * @access public
  * @return papaya_atom_element
  */
  function add() {
    $result = NULL;
    return $result;
  }

  /**
  *
  *
  * @param $index
  * @access public
  * @return papaya_atom_element
  */
  function item($index) {
    if ($index >= 0 && $index < $this->count()) {
      $result = $this->_elements[$index];
    } else {
      $result = NULL;
    }
    return $result;
  }

  /**
  * delete an element from the list
  *
  * @param $index
  * @access public
  * @return boolean
  */
  function delete($index) {
    if ($index >= 0 && $index < $this->count()) {
      array_splice($this->_elements, $index, 1);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * delete all elements
  *
  * @access public
  */
  function clear() {
    $this->_elements = array();
  }

  /**
  * get elements count (Countable interface)
  *
  * @access public
  * @return integer
  */
  function count() {
    return count($this->_elements);
  }

  /**
  * get iterator for list elements (IteratorAggragate interface)
  *
  * @access public
  * @return ArrayIterator
  */
  function getIterator() {
    return new ArrayIterator($this->_elements);
  }

  /**
  * return elements of the list as xml string
  *
  * @param string $tagName name of the tag to save the element
  * @access public
  * @return string
  */
  function saveXML($tagName) {
    if ($this->count() > 0) {
      $result = '';
      /** @var papaya_atom_element $element */
      foreach ($this->_elements as $element) {
        $result .= $element->saveXML($tagName);
      }
      return $result;
    }
    return '';
  }

  /**
  * assign content from another papaya_atom_node
  *
  * @param papaya_atom_element_list $elements
  * @access public
  * @return boolean
  */
  function assign($elements) {
    if ($elements instanceof papaya_atom_element_list) {
      for ($i = 0; $i < $elements->count(); $i++) {
        $element = $elements->item($i);
        $this->addEntry($element);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Add new entry to list dummy method
  * @param papaya_atom_element $element
  * @return NULL
  */
  function addEntry($element) {
    $result = NULL;
    return $result;
  }
}
