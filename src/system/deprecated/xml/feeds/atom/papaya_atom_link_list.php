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
* atom link list class
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_link_list extends papaya_atom_element_list {

  /**
  * add a new link to this list
  *
  * @param string $href is the URI of the referenced resource (typically a Web page)
  * @param string $rel optional, contains a single link relationship type.
  * @param string $type optional, indicates the media type of the resource.
  * @param string $hreflang optional, indicates the language of the referenced resource.
  * @param string $title optional, human readable information about the link,
                         typically for display purposes.
  * @param integer $length optional, the length of the resource, in bytes
  * @access public
  * @return papaya_atom_link $result new entry
  */
  function add(
    $href = '', $rel = NULL, $type = NULL, $hreflang = NULL, $title = NULL, $length = NULL
  ) {
    $result = new papaya_atom_link(
      $this->_feed, $href, $rel, $type, $hreflang, $title, $length
    );
    $this->_elements[] = $result;
    return $result;
  }

  /**
  * add a copy of another link entry to this link list
  *
  * @param papaya_atom_link $element
  * @access public
  * @return papaya_atom_link
  */
  function addEntry($element) {
    $result = NULL;
    if ($element instanceof papaya_atom_link) {
      $result = $this->add(
        $element->href,
        $element->rel,
        $element->type,
        $element->hreflang,
        $element->title,
        $element->length
      );
    }
    return $result;
  }

  /**
   * return the default link (the first "alternate" link)
   *
   * @access public
   * @param string $mimeType
   * @return papaya_atom_link
   */
  function &getDefaultLink($mimeType) {
    foreach ($this->_elements as $element) {
      if ($element->rel == 'alternate' && $element->type == $mimeType) {
        return $element;
      }
    }
    $result = NULL;
    return $result;
  }
}
