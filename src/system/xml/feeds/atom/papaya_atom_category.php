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
* <author> and <contributor> describe a person, corporation, or similar entity.
* It has one required element, name, and two optional elements: uri, email.
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_category extends papaya_atom_element {

  /**
  * identifies the category
  * @var string
  */
  public $term = '';

  /**
  * identifies the categorization scheme via a URI.
  * @var string
  */
  public $scheme;

  public $label;

  /**
  * constructor - initialize properties
  *
  * @param string $term identifies the category
  * @param string $scheme optional, identifies the categorization scheme via a URI.
  * @param string $label optional, provides a human-readable label for display
  * @access public
  */
  function __construct($term, $scheme = NULL, $label = NULL) {
    $this->term = $term;
    $this->scheme = $scheme;
    $this->label = $label;
  }

  /**
  * assign data of another category object
  *
  * @param papaya_atom_category $category
  * @access public
  * @return papaya_atom_category boolean
  */
  function assign($category) {
    if ($category instanceof papaya_atom_category) {
      $this->term = $category->term;
      $this->scheme = $category->scheme;
      $this->label = $category->label;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * save data of this element to xml
  *
  * @param string $tagName
  * @access public
  * @return string
  */
  function saveXML($tagName) {
    if (!empty($this->term)) {
      $result = '<'.$tagName.' term="'.htmlspecialchars($this->term).'"';
      if (!empty($this->scheme)) {
        $result .= ' scheme="'.htmlspecialchars($this->scheme).'"';
      }
      if (!empty($this->label)) {
        $result .= ' label="'.htmlspecialchars($this->label).'"';
      }
      $result .= '/>'."\n";
      return $result;
    }
    return '';
  }

  /**
  * load link data from xml
  *
  * @param DOMElement $node
  * @access public
  */
  function load($node) {
    if ($node->hasAttribute('term')) {
      $this->term = $node->getAttribute('term');
      if ($node->hasAttribute('scheme')) {
        $this->scheme = $node->getAttribute('scheme');
      }
      if ($node->hasAttribute('label')) {
        $this->label = $node->getAttribute('label');
      }
    }
  }
}

