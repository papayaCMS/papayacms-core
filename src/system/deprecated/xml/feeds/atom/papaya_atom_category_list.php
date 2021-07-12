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
* atom category list class
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_category_list extends papaya_atom_element_list {

  /**
  * add a new person to this list
  *
  * @param string $term identifies the category
  * @param string $scheme optional, identifies the categorization scheme via a URI.
  * @param string $label optional, provides a human-readable label for display
  * @access public
  * @return papaya_atom_category $result new entry
  */
  public function add($term = '', $scheme = NULL, $label = NULL) {
    $result = new papaya_atom_category($term, $scheme, $label);
    $this->_elements[] = $result;
    return $result;
  }

  /**
  * add a copy of another category entry to this category list
  *
  * @param papaya_atom_category $element
  * @access public
  * @return papaya_atom_category
  */
  public function addEntry($element) {
    $result = NULL;
    if ($element instanceof papaya_atom_category) {
      $result = $this->add($element->term, $element->scheme, $element->label);
    }
    return $result;
  }
}
