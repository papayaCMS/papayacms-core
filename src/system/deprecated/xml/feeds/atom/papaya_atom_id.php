<?php
/**
* Identifies the entry/feed using a universally unique and permanent URI.
* Two entries in a feed can have the same value for id if they represent
* the same entry at different points in time.
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
* @version $Id: papaya_atom_id.php 39626 2014-03-19 12:43:41Z weinert $
*/


/**
* Identifies the entry/feed using a universally unique and permanent URI.
* Two entries in a feed can have the same value for id if they represent
* the same entry at different points in time.
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_id extends papaya_atom_element {
  /**
  * private property for id value
  * @var string
  * @access private
  */
  var $_id = '';

  /**
  * constructor - initialize properties
  *
  * @param papaya_atom_feed $feed reference of feed object
  * @access public
  */
  function __construct(papaya_atom_feed $feed) {
    $this->_feed = $feed;
  }

  /**
  * assign data of another id object
  *
  * @param papaya_atom_id $id
  * @access public
  * @return papaya_atom_id boolean
  */
  function assign($id) {
    if ($id instanceof papaya_atom_id) {
      $this->set($id->get());
      return TRUE;
    }
    return FALSE;
  }

  /**
   * set value of this element
   *
   * @param string $id
   * @access public
   * @return boolean
   */
  function set($id) {
    if (!empty($id)) {
      $this->_id = $id;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * get the current id
  *
  * @access public
  * @return string
  */
  function get() {
    if (!empty($this->_id)) {
      return $this->_id;
    }
    return '';
  }

  /**
  * generate an id
  *
  * @access public
  */
  function generate($randomSuffix = TRUE) {
    $feedURI = 'tag:';
    if (!empty($_SERVER['HTTP_HOST'])) {
      $feedURI .= $_SERVER['HTTP_HOST'];
    }
    $feedURI .= date(',Y-m-d:');
    if (!empty($_SERVER['PHP_SELF'])) {
      if (preg_match('~^(.+)\.\w+$~', $_SERVER['PHP_SELF'], $match)) {
        $feedURI .= $match[1];
      } else {
        $feedURI .= $_SERVER['PHP_SELF'];
      }
    }
    $feedURI = str_replace('#', '/', $feedURI);
    if ($randomSuffix) {
      $feedURI .= '/'.md5(uniqid(rand(), TRUE));
    }
    $this->_id = $feedURI;
  }

  /**
  * save data of this element to xml
  *
  * @param string $tagName
  * @access public
  * @return string
  */
  function saveXML($tagName) {
    if (!empty($this->_id)) {
      return '<'.$tagName.'>'.htmlspecialchars($this->_id).'</'.$tagName.'>'."\n";
    }
    return '';
  }

  /**
  * load id data from xml node
  *
  * @param $node
  * @access public
  */
  function load($node) {
    if (method_exists($node, 'valueOf')) {
      $this->set($node->valueOf());
    } else {
      $this->set($node->nodeValue);
    }
  }
}

