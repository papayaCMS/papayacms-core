<?php
/**
* atom feed entry source element
*
* If an entry is copied from one feed into another feed,
* then the source feed's metadata (all child elements of feed
* other than the entry elements) should be preserved if
* the source feed contains any of the child elements author,
* contributor, rights, or category and those child elements
* are not present in the source entry.
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
* @version $Id: papaya_atom_source.php 39721 2014-04-07 13:13:23Z weinert $
*/

/**
* atom feed entry source element
*
* If an entry is copied from one feed into another feed,
* then the source feed's metadata (all child elements of feed
* other than the entry elements) should be preserved if
* the source feed contains any of the child elements author,
* contributor, rights, or category and those child elements
* are not present in the source entry.
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_source extends papaya_atom_element {

  /**
  * Identifies the source using a universally unique and permanent URI.
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
  * Identifies a small image which provides iconic visual identification
  * for the feed. Icons should be square.
  * @var string
  */
  var $icon;

  /**
  * Identifies a larger image which provides visual identification
  * for the feed. Images should be twice as wide as they are tall.
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
  * A feed must contain at least one author element unless all of
  * the entry elements contain at least one author element.
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
  * constructor - initialize properties
  *
  * @param papaya_atom_feed $feed reference of feed object
  * @access public
  */
  function __construct(papaya_atom_feed $feed) {
    $this->_feed = $feed;
    $this->updated = 0;
    $this->id = new papaya_atom_id($feed);
    $this->title = new papaya_atom_text($feed, '');
    $this->subtitle = new papaya_atom_text($feed, '');
    $this->rights = new papaya_atom_text($feed, '');
    $this->generator = new papaya_atom_generator($feed, '');
    $this->authors = new papaya_atom_person_list($feed);
    $this->contributors = new papaya_atom_person_list($feed);
    $this->links = new papaya_atom_link_list($feed);
    $this->categories = new papaya_atom_category_list($feed);
  }

  /**
   * assign data of another source object
   *
   * @param papaya_atom_element $element
   * @access public
   * @return papaya_atom_element boolean
   */
  function assign($element) {
    if ($element instanceof papaya_atom_source) {
      $this->updated = $element->updated;
      $this->id->assign($element->id);
      $this->title->assign($element->title);
      $this->subtitle->assign($element->subtitle);
      $this->rights->assign($element->rights);
      $this->generator->assign($element->generator);
      $this->authors->assign($element->authors);
      $this->contributors->assign($element->contributors);
      $this->links->assign($element->links);
      $this->categories->assign($element->categories);
      return TRUE;
    } elseif ($element instanceof papaya_atom_entry) {
      //if the source of the entry has an if - assign data of the source element
      if (trim($element->source->id->get()) != '') {
        $this->assign($element->source);
      } else {
        $feed = $element->feed();
        /*atom specification says you should add a source element with data from feed if
          contributers, authors, categories or rights is present in the feed
          but not in the entry */
        if (($element->authors->count() == 0 && $feed->authors->count() > 0) ||
            ($element->contributors->count() == 0 && $feed->contributors->count() > 0) ||
            ($element->categories->count() == 0 && $feed->categories->count() > 0) ||
            ($element->rights->isEmpty() && !$feed->rights->isEmpty())) {
          $this->updated = $feed->updated;
          $this->id->assign($feed->id);
          $this->title->assign($feed->title);
          $this->subtitle->assign($feed->subtitle);
          $this->generator->assign($feed->generator);
          $this->links->assign($feed->links);
          if ($element->rights->isEmpty()) {
            $this->rights->assign($feed->rights);
          }
          if ($element->authors->count() == 0) {
            $this->authors->assign($feed->authors);
          }
          if ($element->contributors->count() == 0) {
            $this->contributors->assign($feed->contributors);
          }
          if ($element->categories->count() == 0) {
            $this->categories->assign($feed->categories);
          }
        }
      }
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
    if ('' != $this->id->get()) {
      $result = '<'.$tagName.'>'."\n";
      $result .= $this->id->saveXML('id');
      if ($this->updated > 0) {
        $result .= '<updated>'.
          gmdate('Y-m-d\\TH:i:s\\Z', $this->updated).'</updated>'."\n";
      }
      $result .= $this->title->saveXML('title');
      $result .= $this->subtitle->saveXML('subtitle');
      if (!empty($this->logo)) {
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
      $result .= '</'.$tagName.'>'."\n";
      return $result;
    }
    return '';
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
          case 'subtitle' :
            $this->title->load($childNode);
            break;
          case 'rights' :
            $this->rights->load($childNode);
            break;
          case 'generator' :
            $this->generator->load($childNode);
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
          }
        }
      }
    }
  }
}

