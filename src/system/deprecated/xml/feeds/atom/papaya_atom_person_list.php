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
* atom entry  list class
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_person_list extends papaya_atom_element_list {

  /**
  * add a new person to this list
  *
  * @param string $name conveys a human-readable name for the person.
  * @param string $uri optional, contains a home page for the person.
  * @param string $email optional, contains an email address for the person.
  * @access public
  * @return papaya_atom_person $result new entry
  */
  function add($name = '', $uri = NULL, $email = NULL) {
    $result = new papaya_atom_person($this->_feed, $name, $uri, $email);
    $this->_elements[] = $result;
    return $result;
  }

  /**
  * add a copy of another person entry to this person list
  *
  * @param papaya_atom_person $element
  * @access public
  * @return papaya_atom_person
  */
  function addEntry($element) {
    $result = NULL;
    if ($element instanceof papaya_atom_person) {
      $result = $this->add($element->name, $element->uri, $element->email);
    }
    return $result;
  }
}
