<?php
/**
* An interface for a plugin with a teaser appendable to an DOM element. It
* provides an additional method to PapayaPluginAppendable to append a "quote"/short version
* of the content to the DOM.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Plugins
* @version $Id: Quoteable.php 39416 2014-02-27 17:02:47Z weinert $
*/

/**
* An interface to define that an plguin with a teaser appendable to an DOM element. It
* is provides an addiitonal method to PapayaPluginAppendable to append a "quote"/short version
* of the content to the DOM.
*
* @package Papaya-Library
* @subpackage Plugins
*/
interface PapayaPluginQuoteable {

  /**
   * Append short content (aka "quote") to the parent xml element.
   *
   * @param PapayaXmlElement $parent
   * @return NULL|PapayaXmlElement
   */
  function appendQuoteTo(PapayaXmlElement $parent);
}