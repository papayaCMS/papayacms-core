<?php
/**
* <link> is patterned after html's link element.
* It has one required attribute, href, and five optional attributes:
* rel, type, hreflang, title, and length.
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
* @version $Id: papaya_atom_link.php 39733 2014-04-08 18:10:55Z weinert $
*/


/**
* <link> is patterned after html's link element.
* It has one required attribute, href, and five optional attributes:
* rel, type, hreflang, title, and length.
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_link extends papaya_atom_element {

  /**
  * is the URI of the referenced resource (typically a Web page)
  * @var string
  */
  var $href = '';

  /**
  * contains a single link relationship type.
  * @var string
  */
  var $rel = 'alternate';

  /**
  * indicates the media type of the resource.
  * @var string
  */
  var $type = 'text/html';

  /**
  * indicates the language of the referenced resource.
  * @var string
  */
  var $hreflang = NULL;

  /**
  * human readable information about the link, typically for display purposes.
  * @var string
  */
  var $title = NULL;

  /**
  * the length of the resource, in bytes
  * @var integer
  */
  var $length = 0;

  /**
  * constructor - initialize properties
  *
  * @param papaya_atom_feed $feed reference of feed object
  * @param string $href is the URI of the referenced resource (typically a Web page)
  * @param string $rel optional, contains a single link relationship type.
  * @param string $type optional, indicates the media type of the resource.
  * @param string $hreflang optional, indicates the language of the referenced resource.
  * @param string $title optional, human readable information about the link,
  *                      typically for display purposes.
  * @param integer $length optional, the length of the resource, in bytes
  * @access public
  */
  function __construct(
    papaya_atom_feed $feed,
    $href,
    $rel = NULL,
    $type = NULL,
    $hreflang = NULL,
    $title = NULL,
    $length = NULL
  ) {
    $this->_feed = $feed;
    $this->href = $this->_feed->getAbsoluteHref($href);
    if (isset($rel)) {
      $this->rel = $rel;
    }
    if (isset($type)) {
      $this->type = $type;
    }
    $this->hreflang = $hreflang;
    $this->title = $title;
    $this->length = (int)$length;
  }

  /**
  * assign data of another link object
  *
  * @param papaya_atom_link $link
  * @access public
  * @return papaya_atom_link boolean
  */
  function assign($link) {
    if ($link instanceof papaya_atom_link) {
      $this->href = $this->_feed->getAbsoluteHref($link->href);
      $this->rel = $link->rel;
      $this->type = $link->type;
      $this->hreflang = $link->hreflang;
      $this->title = $link->title;
      $this->length = $link->length;
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
    if (!empty($this->href)) {
      $result = '<'.$tagName.' href="'.htmlspecialchars($this->href).'"';
      if (!empty($this->rel)) {
        $result .= ' rel="'.htmlspecialchars($this->rel).'"';
      }
      if (!empty($this->type)) {
        $result .= ' type="'.htmlspecialchars($this->type).'"';
      }
      if (!empty($this->hreflang)) {
        $result .= ' hreflang="'.htmlspecialchars($this->hreflang).'"';
      }
      if (!empty($this->title)) {
        $result .= ' title="'.htmlspecialchars($this->title).'"';
      }
      if (!empty($this->length) && $this->length > 0) {
        $result .= ' length="'.(int)$this->length.'"';
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
    if ($node->hasAttribute('href')) {
      $this->href = $this->_feed->getAbsoluteHref($node->getAttribute('href'));
      if ($node->hasAttribute('rel')) {
        $this->type = $node->getAttribute('rel');
      }
      if ($node->hasAttribute('type')) {
        $this->type = $node->getAttribute('type');
      }
      if ($node->hasAttribute('hreflang')) {
        $this->hreflang = $node->getAttribute('hreflang');
      }
      if ($node->hasAttribute('title')) {
        $this->title = $node->getAttribute('title');
      }
      if ($node->hasAttribute('length')) {
        $this->length = (int)$node->getAttribute('length');
      }
    }
  }
}

