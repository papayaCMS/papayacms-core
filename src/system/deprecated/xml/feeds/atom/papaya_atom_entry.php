<?php
/**
* Atom entry class
*
* represents an atom feed entry
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
* @version $Id: papaya_atom_entry.php 39630 2014-03-19 14:40:47Z weinert $
*/


/**
* atom entry class
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_entry extends papaya_atom_element {

  /**
  * Identifies the entry using a universally unique and permanent URI.
  * @var papaya_atom_id
  */
  var $id;

  /**
  * entry title
  * @var papaya_atom_element
  */
  var $title = NULL;

  /**
  * Indicates the last time the feed was modified in a significant way.
  * @var integer
  */
  var $updated = 0;

  /**
  * Indicates the first time the feed was published.
  * @var integer
  */
  var $published = 0;

  /**
  * feed authors
  * @var papaya_atom_person_list
  */
  var $authors;

  /**
  * feed contributors
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
  * Conveys information about rights, e.g. copyrights, held in and over the feed.
  * @var papaya_atom_text
  */
  var $rights;

  /**
  * If an entry is copied from one feed into another feed, then the source feed's metadata
  * @var papaya_atom_source
  */
  var $source;

  public $generator;

  /**
  * constructor - initialize subobjects
  *
  * @param papaya_atom_feed $feed reference of feed object
  * @access public
  */
  function __construct(papaya_atom_feed $feed) {
    $this->_feed = $feed;
    $this->updated = time();
    $this->published = 0;
    $this->id = new papaya_atom_id($feed);
    $this->title = new papaya_atom_text($feed, '');
    $this->summary = new papaya_atom_text($feed, '');
    $this->content = new papaya_atom_content($feed, '');
    $this->rights = new papaya_atom_text($feed, '');
    $this->authors = new papaya_atom_person_list($feed);
    $this->contributors = new papaya_atom_person_list($feed);
    $this->links = new papaya_atom_link_list($feed);
    $this->categories = new papaya_atom_category_list($feed);
    $this->source = new papaya_atom_source($feed);
    $this->generator = new papaya_atom_generator($feed, '');
  }

  /**
  * assign all data from another entry
  *
  * @param papaya_atom_entry $entry
  * @access public
  * @return boolean
  */
  function assign($entry) {
    if ($entry instanceof papaya_atom_entry) {
      $this->updated = $entry->updated;
      $this->published = $entry->published;
      $this->id->assign($entry->id);
      $this->title->assign($entry->title);
      $this->summary->assign($entry->summary);
      $this->content->assign($entry->content);
      $this->rights->assign($entry->rights);
      $this->authors->assign($entry->authors);
      $this->contributors->assign($entry->contributors);
      $this->links->assign($entry->links);
      $this->categories->assign($entry->categories);
      $this->source->assign($entry);
    }
  }

  /**
  * save etry to xml - evelope tag is $tagName
  *
  * @param string $tagName
  * @access public
  * @return string
  */
  function saveXML($tagName) {
    $result = '<'.$tagName.'>'."\n";
    $result .= $this->id->saveXML('id');
    $result .= $this->title->saveXML('title');
    $result .= '<updated>'.gmdate('Y-m-d\\TH:i:s\\Z', $this->updated).
      '</updated>'."\n";
    if ($this->published > 0) {
      $result .= '<published>'.gmdate('Y-m-d\\TH:i:s\\Z', $this->published).
        '</published>'."\n";
    }
    $result .= $this->summary->saveXML('summary');
    $result .= $this->content->saveXML('content');
    $result .= $this->authors->saveXML('author');
    $result .= $this->contributors->saveXML('contributor');
    $result .= $this->rights->saveXML('rights');
    $result .= $this->categories->saveXML('category');
    $result .= $this->links->saveXML('link');
    $result .= $this->source->saveXML('source');
    $result .= '</'.$tagName.'>'."\n";
    return $result;
  }

  /**
  * load entry data from xml
  *
  * @param DOMElement $node
  * @access public
  */
  function load($node) {
    if (isset($node) && isset($node) && $node->hasChildNodes()) {
      for ($i = 0; $i < $node->childNodes->length; $i++) {
        $childNode = $node->childNodes->item($i);
        if ($childNode instanceof DOMElement) {
          switch ($childNode->nodeName) {
          case 'id' :
            $this->id->load($childNode);
            break;
          case 'title' :
            $this->title->load($childNode);
            break;
          case 'summary' :
            $this->summary->load($childNode);
            break;
          case 'content' :
            $this->content->load($childNode);
            break;
          case 'rights' :
            $this->rights->load($childNode);
            break;
          case 'generator' :
            $this->generator->load($childNode);
            break;
          case 'source' :
            $this->source->load($childNode);
            break;
          case 'author' :
            $entry = $this->authors->add('');
            $entry->load($childNode);
            break;
          case 'contributor' :
            $entry = $this->contributors->add('');
            $entry->load($childNode);
            break;
          case 'link' :
            $entry = $this->links->add('');
            $entry->load($childNode);
            break;
          case 'category' :
            $entry = $this->categories->add('');
            $entry->load($childNode);
            break;
          case 'updated' :
            if (method_exists($childNode, 'valueOf')) {
              $timeString = $childNode->valueOf();
            } else {
              $timeString = $childNode->nodeValue;
            }
            $this->updated = strtotime($timeString);
            break;
          case 'published' :
            if (method_exists($childNode, 'valueOf')) {
              $timeString = $childNode->valueOf();
            } else {
              $timeString = $childNode->nodeValue;
            }
            $this->published = strtotime($timeString);
            break;
          }
        }
      }
    }
  }
}

