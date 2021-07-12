<?php
/**
* Atom entry  list class
*
* allows to have multiple entries in a feed
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
* @version $Id: papaya_atom_entry_list.php 39626 2014-03-19 12:43:41Z weinert $
*/

/**
* atom entry  list class
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_entry_list extends papaya_atom_element_list {

  /**
  * add a new entry to this list
  *
  * @access public
  * @return papaya_atom_entry $result new entry
  */
  function add() {
    $result = new papaya_atom_entry($this->_feed);
    $this->_elements[] = $result;
    return $result;
  }

  /**
  * add an existing entry (from antoehr feed to this feed).
  *
  * the new entry will be a copy
  *
  * @param papaya_atom_entry $entry
  * @access public
  * @return papaya_atom_entry
  */
  function addEntry($entry) {
    $result = NULL;
    if ($entry instanceof papaya_atom_entry) {
      $result = $this->add();
      $result->assign($entry);
    }
    return $result;
  }
}
