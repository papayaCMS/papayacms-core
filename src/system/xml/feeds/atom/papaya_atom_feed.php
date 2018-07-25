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

$path = __DIR__.'/';
/**
* an atom feed element - a single value, it's type, ... - abstract class
*/
require_once($path.'papaya_atom_element.php');
/**
* a list of feed elements - abstract class
*/
require_once($path.'papaya_atom_element_list.php');

/**
* a feed/entry id
*/
require_once($path.'papaya_atom_id.php');

/**
* a text element (<title>, <subtitle>, ...)
*/
require_once($path.'papaya_atom_text.php');
/**
* a content element (<content>)
*/
require_once($path.'papaya_atom_content.php');

/**
* a feed entry
*/
require_once($path.'papaya_atom_entry.php');
/**
* a list of feed entries
*/
require_once($path.'papaya_atom_entry_list.php');

/**
* a person (autor or contributor)
*/
require_once($path.'papaya_atom_person.php');
/**
* list of persons
*/
require_once($path.'papaya_atom_person_list.php');

/**
* a category
*/
require_once($path.'papaya_atom_category.php');
/**
* a list of categories
*/
require_once($path.'papaya_atom_category_list.php');

/**
* a link
*/
require_once($path.'papaya_atom_link.php');
/**
* a list of links
*/
require_once($path.'papaya_atom_link_list.php');

/**
* the generator
*/
require_once($path.'papaya_atom_generator.php');

/**
* a source (used in entry copies)
*/
require_once($path.'papaya_atom_source.php');

/**
* atom feed class
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_feed {

  /**
  * Identifies the feed using a universally unique and permanent URI.
  * @var papaya_atom_id
  */
  var $id;

  /**
  * Indicates the last time the feed was modified in a significant way.
  * @var integer
  */
  var $updated = 0;

  /**
  * Contains a human readable title for the feed.
  * @var papaya_atom_text
  */
  var $title;

  /**
  * Contains a human-readable description or subtitle for the feed.
  * @var papaya_atom_text
  */
  var $subtitle;

  /**
  * Identifies a small image which provides iconic visual
  * identification for the feed. Icons should be square.
  * @var string
  */
  var $icon;

  /**
  * Identifies a larger image which provides visual identification for the feed.
  * Images should be twice as wide as they are tall.
  * @var string
  */
  var $logo;

  /**
  * Conveys information about rights, e.g. copyrights, held in and over the feed.
  * @var papaya_atom_text
  */
  var $rights;

  /**
  * Identifies the software used to generate the feed.
  * @var papaya_atom_generator
  */
  var $generator;

  /**
  * Names the authors of the feed.
  * A feed must contain at least one author element unless
  * all of the entry elements contain at least one author element.
  * @var papaya_atom_person_list
  */
  var $authors;

  /**
  * Names the contributors to the feed.
  * @var papaya_atom_person_list
  */
  var $contributors;

  /**
  * Identifies a related Web pages.
  * @var  papaya_atom_link_list
  */
  var $links;

  /**
  * Specifies a categories that the feed belongs to.
  * @var papaya_atom_category_list
  */
  var $categories;

  /**
  * entries list
  * @var papaya_atom_entry_list
  */
  var $entries;

  /**
   * @var \PapayaUrlTransformerAbsolute
   */
  private $_urlTransformer = NULL;

  /**
   * @var \Papaya\Url
   */
  private $_baseUrl;

  /**
  * constructor - initialize subobjects
  *
  * @access public
  */
  function __construct() {
    $this->updated = time();
    $this->id = new papaya_atom_id($this);
    $this->title = new papaya_atom_text($this, '');
    $this->subtitle = new papaya_atom_text($this, '');
    $this->rights = new papaya_atom_text($this, '');
    $this->generator = new papaya_atom_generator($this, 'papaya CMS');

    $this->authors = new papaya_atom_person_list($this);
    $this->contributors = new papaya_atom_person_list($this);
    $this->links = new papaya_atom_link_list($this);
    $this->categories = new papaya_atom_category_list($this);

    $this->entries = new papaya_atom_entry_list($this);
  }

  /**
  * save the feed to a xml string
  *
  * @access public
  * @return string
  */
  function saveXML() {
    $result = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
    $result .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n";
    $result .= '<updated>'.gmdate('Y-m-d\\TH:i:s\\Z', $this->updated).'</updated>'."\n";
    $result .= $this->id->saveXML('id');
    $result .= $this->title->saveXML('title');
    $result .= $this->subtitle->saveXML('subtitle');
    if (!empty($this->icon)) {
      $result .= '<icon>'.htmlspecialchars($this->icon).'</icon>'."\n";
    }
    if (!empty($this->logo)) {
      $result .= '<logo>'.htmlspecialchars($this->logo).'</logo>'."\n";
    }
    $result .= $this->generator->saveXML('generator');
    $result .= $this->authors->saveXML('author');
    $result .= $this->contributors->saveXML('contributor');
    $result .= $this->categories->saveXML('category');
    $result .= $this->links->saveXML('link');
    $result .= $this->entries->saveXML('entry');
    $result .= '</feed>';
    return $result;
  }

  /**
  * load the feed from a DomDocument (or papaya expat document)
  *
  * @param DOMDocument $dom
  * @param NULL|string|\Papaya\Url $baseUrl
  * @access public
  * @return boolean
  */
  function load($dom, $baseUrl = NULL) {
    if (isset($baseUrl)) {
      $this->setBaseUrl($baseUrl);
    }
    $result = FALSE;
    if (isset($dom) && isset($dom->documentElement)) {
      if ($dom->documentElement->nodeName == 'feed' &&
          $dom->documentElement->hasChildNodes()) {
        for ($i = 0; $i < $dom->documentElement->childNodes->length; $i++) {
          $feedChildNode = $dom->documentElement->childNodes->item($i);
          if ($feedChildNode instanceof DOMElement) {
            switch ($feedChildNode->nodeName) {
            case 'id' :
              $this->id->load($feedChildNode);
              break;
            case 'title' :
              $this->title->load($feedChildNode);
              break;
            case 'subtitle' :
              $this->subtitle->load($feedChildNode);
              break;
            case 'rights' :
              $this->rights->load($feedChildNode);
              break;
            case 'generator' :
              $this->generator->load($feedChildNode);
              break;
            case 'author' :
              $entry = $this->authors->add('');
              $entry->load($feedChildNode);
              break;
            case 'contributor' :
              $entry = $this->contributors->add('');
              $entry->load($feedChildNode);
              break;
            case 'link' :
              $entry = $this->links->add('');
              $entry->load($feedChildNode);
              break;
            case 'category' :
              $entry = $this->categories->add('');
              $entry->load($feedChildNode);
              break;
            case 'entry' :
              $entry = $this->entries->add();
              $entry->load($feedChildNode);
              break;
            case 'updated' :
              if (method_exists($feedChildNode, 'valueOf')) {
                $timeString = $feedChildNode->valueOf();
              } else {
                $timeString = $feedChildNode->nodeValue;
              }
              $this->updated = strtotime($timeString);
              break;
            }
          }
        }
        $result = TRUE;
      } elseif (in_array($dom->documentElement->nodeName, array('rss', 'rdf:RDF')) &&
                $dom->documentElement->hasChildNodes()) {
        include_once(dirname(__FILE__).'/papaya_atom_import.php');
        $importer = new papaya_atom_import();
        $result = $importer->import($this, $dom);
        unset($importer);
      }
    }
    return $result;
  }

  /**
  * Try to make the given url absolute
  *
  * @param string $href
  * @return string
  */
  public function getAbsoluteHref($href) {
    if (isset($this->_baseUrl)) {
      $result = $this->urlTransformer()->transform($this->_baseUrl, $href);
    } else {
      $result = $href;
    }
    return $result;
  }

  /**
  * Set the base url (of the feed) - not used in the xml directly but to make links absolute
  *
  * @param string|\Papaya\Url $url
  */
  public function setBaseUrl($url) {
    $this->_baseUrl = ($url instanceof \Papaya\Url) ? $url : new \Papaya\Url($url);
  }

  /**
  * Getter/Setter for the url transformer
  *
  * @param \PapayaUrlTransformerAbsolute $transformer
  * @return \PapayaUrlTransformerAbsolute
  */
  public function urlTransformer(PapayaUrlTransformerAbsolute $transformer = NULL) {
    if (isset($transformer)) {
      $this->_urlTransformer = $transformer;
    } elseif (is_null($this->_urlTransformer)) {
      $this->_urlTransformer = new PapayaUrlTransformerAbsolute();
    }
    return $this->_urlTransformer;
  }

  /**
  * set the time of the last significant update
  *
  * @param integer $time optional - time of the last significant update,
                         default value current time
  * @access public
  */
  function setUpdated($time = NULL) {
    if (isset($time)) {
      $this->updated = (int)$time;
    } else {
      $this->updated = time();
    }
  }

  /**
  * Validate this feed using an external validator object
  * @return papaya_atom_validate
  */
  function &validate() {
    include_once(dirname(__FILE__).'/papaya_atom_validate.php');
    $validator = new papaya_atom_validate($this);
    $validator->validate();
    return $validator;
  }
}

