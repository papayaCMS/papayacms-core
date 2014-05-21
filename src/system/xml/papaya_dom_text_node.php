<?php
/**
* wrapper for DOMText to add valueOf() (it is not possbile to emulate
* a property like nodeValue in PHP 4)
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
* @subpackage XML-DOM-Emulation
* @version $Id: papaya_dom_text_node.php 38664 2013-09-09 18:54:43Z weinert $
*/

/***
* wrapper for DOMText to add valueOf()
* (it is not possbile to emulate a property like nodeValue in PHP 4)
*
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/
class papaya_dom_text_node extends DOMText {

  /**
  * return node value
  *
  * @access public
  * @return string
  */
  function valueOf() {
    return $this->nodeValue;
  }

  /**
  * this is the destrcutor in the emulation, we need it here to avoid error messages
  *
  * @access public
  */
  function free() {

  }
}

