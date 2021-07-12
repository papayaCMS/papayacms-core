<?php
/**
* Atom feed importer
*
* import feeds to atom 1.0
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @version $Id: papaya_atom_import.php 39612 2014-03-18 21:33:14Z weinert $
*/

/**
* atom feed importer
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_import {

  /**
  * Import a DOMDocument into an atom feed object
  * @param papaya_atom_feed $feed
  * @param DOMDocument $dom
  * @return boolean
  */
  function import(papaya_atom_feed $feed, DOMDocument $dom) {
    $rssVersion = '';
    if (isset($dom->documentElement) &&
        in_array($dom->documentElement->nodeName, array('rss', 'rdf:RDF'))  &&
        $dom->documentElement->hasChildNodes()) {
      if ($dom->documentElement->nodeName == 'rss' &&
          $dom->documentElement->hasAttribute('version')) {
        $rssVersion = $dom->documentElement->getAttribute('version');
      } elseif ($dom->documentElement->nodeName == 'rdf:RDF') {
        $rssVersion = '1.0';
      }
      for ($i = 0; $i < $dom->documentElement->childNodes->length; $i++) {
        $channelNode = $dom->documentElement->childNodes->item($i);
        if ($channelNode instanceof DOMElement &&
            $channelNode->nodeName == 'channel') {
          switch ($rssVersion) {
          case '2.0' :
            return $this->importRSS2($feed, $channelNode);
          case '1.0' :
            return $this->importRSS1($feed, $dom);
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Import a DOMNode containing an RSS 2.0 channel into an atom feed object
   * @param papaya_atom_feed $feed
   * @param DOMElement $channelNode
   * @return boolean
   */
  function importRSS2(papaya_atom_feed $feed, DOMElement $channelNode) {
    if ($channelNode->hasChildNodes()) {
      for ($i = 0; $i < $channelNode->childNodes->length; $i++) {
        $feedChildNode = $channelNode->childNodes->item($i);
        if ($feedChildNode instanceof DOMElement) {
          switch ($feedChildNode->nodeName) {
          case 'title' :
            $feed->title->setType('html');
            $feed->title->setValue($feedChildNode->nodeValue);
            break;
          case 'link' :
            $feed->links->add($feedChildNode->nodeValue, 'self');
            $feed->id->set($feedChildNode->nodeValue, 'self');
            break;
          case 'description' :
            $feed->subtitle->setType('html');
            $feed->subtitle->setValue($feedChildNode->nodeValue);
            break;
          case 'copyright' :
            $feed->rights->setType('html');
            $feed->rights->setValue($feedChildNode->nodeValue);
            break;
          case 'managingEditor' :
            $person = $this->parseRSSPersonData($feedChildNode->nodeValue);
            $feed->authors->add($person[0], '', $person[1]);
            break;
          case 'lastBuildDate' :
            $feed->setUpdated($this->parseRSSTimeData($feedChildNode->nodeValue));
            break;
          case 'category' :
            if ($feedChildNode->hasAttribute('domain')) {
              $feed->categories->add(
                $feedChildNode->nodeValue,
                $feedChildNode->getAttribute('domain')
              );
            } else {
              $feed->categories->add($feedChildNode->nodeValue);
            }
            break;
          case 'generator' :
            $feed->generator->name = $feedChildNode->nodeValue;
            break;
          case 'image' :
            if ($feedChildNode->hasAttribute('url')) {
              $feed->logo = $feedChildNode->getAttribute('url');
            }
            break;
          case 'item' :
            if ($feedChildNode->hasChildNodes()) {
              $newItem = $feed->entries->add();
              for ($k = 0; $k < $feedChildNode->childNodes->length; $k++) {
                $itemChildNode = $feedChildNode->childNodes->item($k);
                if ($itemChildNode instanceof DOMElement) {
                  switch ($itemChildNode->nodeName) {
                  case 'title' :
                    $newItem->title->setType('html');
                    $newItem->title->setValue($itemChildNode->nodeValue);
                    break;
                  case 'description' :
                    $newItem->content->setType('html');
                    $newItem->content->setValue($itemChildNode->nodeValue);
                    break;
                  case 'author' :
                    $person = $this->parseRSSPersonData($itemChildNode->nodeValue);
                    $newItem->authors->add($person[0], '', $person[1]);
                    break;
                  case 'enclosure' :
                    if ($itemChildNode->hasAttribute('url') &&
                        $itemChildNode->hasAttribute('length') &&
                        $itemChildNode->hasAttribute('type')) {
                      $newItem->links->add(
                        $itemChildNode->getAttribute('url'),
                        'enclosure',
                        $itemChildNode->getAttribute('type'),
                        NULL,
                        NULL,
                        $itemChildNode->getAttribute('length')
                      );
                    }
                    break;
                  case 'link' :
                    $newItem->links->add($itemChildNode->nodeValue);
                    break;
                  case 'guid' :
                    $newItem->id->set($itemChildNode->nodeValue);
                    break;
                  case 'pubDate' :
                    $newItem->published =
                      $this->parseRSSTimeData($itemChildNode->nodeValue);
                    $newItem->updated =
                      $this->parseRSSTimeData($itemChildNode->nodeValue);
                    break;
                  case 'category' :
                    if ($feedChildNode->hasAttribute('domain')) {
                      $newItem->categories->add(
                        $itemChildNode->nodeValue,
                        $itemChildNode->getAttribute('domain')
                      );
                    } else {
                      $newItem->categories->add($itemChildNode->nodeValue);
                    }
                    break;
                  case 'source' :
                    if ($itemChildNode->hasAttribute('url')) {
                      $newItem->links->add($itemChildNode->getAttribute('url'), 'via');
                    }
                    break;
                  }
                }
              }
            }
          }
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Import a DOMNode containing an RSS/RDF 1.0 channel into an atom feed object
   *
   * @param papaya_atom_feed $feed
   * @param DOMDocument $dom
   * @return boolean
   */
  function importRSS1(papaya_atom_feed $feed, DOMDocument $dom) {
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
    $xpath->registerNamespace('rss', 'http://purl.org/rss/1.0/');
    $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');

    $feed->title->setType('html');
    $feed->title->setValue($xpath->evaluate('string(/rdf:RDF/rss:channel/rss:title)'));

    $feed->subtitle->setType('html');
    $feed->subtitle->setValue($xpath->evaluate('string(/rdf:RDF/rss:channel/rss:description)'));

    $feed->rights->setType('html');
    $feed->rights->setValue($xpath->evaluate('string(/rdf:RDF/rss:channel/dc:rights)'));

    $person = $this->parseRSSPersonData(
      $xpath->evaluate('string(/rdf:RDF/rss:channel/dc:publisher)')
    );
    $feed->authors->add($person[0], '', $person[1]);

    $feed->setUpdated(
      $this->parseRSSTimeData($xpath->evaluate('string(/rdf:RDF/rss:channel/dc:date)'))
    );

    $feed->logo = $xpath->evaluate('string(/rdf:RDF/rss:channel/rss:image/@rdf:resource)');

    foreach ($xpath->evaluate('/rdf:RDF/rss:item') as $feedChildNode) {
      $newItem = $feed->entries->add();

      $newItem->title->setType('html');
      $newItem->title->setValue($xpath->evaluate('string(rss:title)', $feedChildNode));
      $newItem->content->setType('html');
      $newItem->content->setValue($xpath->evaluate('string(rss:description)', $feedChildNode));

      $person = $this->parseRSSPersonData(
        $xpath->evaluate('string(rss:guid)', $feedChildNode)
      );
      $newItem->authors->add($person[0], '', $person[1]);
      $newItem->links->add($xpath->evaluate('string(rss:link)', $feedChildNode));

      $newItem->id->set($xpath->evaluate('string(dc:date)', $feedChildNode));

      $newItem->published = $this->parseRSSTimeData(
        $xpath->evaluate('string(dc:date)', $feedChildNode)
      );
      $newItem->updated = $this->parseRSSTimeData(
        $xpath->evaluate('string(dc:date)', $feedChildNode)
      );
    }
    return TRUE;
  }

  /**
  * Parse rss person data (name and email)
  * @param $str
  * @return array
  */
  function parseRSSPersonData($str) {
    if (preg_match('~([^\s]+)(?:\s+\(([^)]+)\))?~', $str, $match)) {
      return array(
        empty($match[2]) ? '' : $match[2],
        empty($match[1]) ? '' : $match[1]
      );
    } else {
      return array($str, '');
    }
  }

  /**
   * Parse rss time stamp to unix timestamp
   * @param string $timeString
   * @return integer
   */
  function parseRSSTimeData($timeString) {
    return strtotime($timeString);
  }
}

