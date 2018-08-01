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

namespace Papaya\Content;

/**
 * Load and provide access to the theme definition stored in theme.xml inside the theme directory.
 *
 * @package Papaya-Library
 * @subpackage Theme
 */
class Structure implements \IteratorAggregate {

  /**
   * @var Structure\Pages
   */
  private $_pages;

  /**
   * Load theme data from an xml file
   *
   * @param string|\DOMElement $data
   */
  public function load($data) {
    if (is_string($data)) {
      $data = trim($data);
      if (empty($data)) {
        return;
      }
      $dom = new \Papaya\Xml\Document();
      if (0 === strpos($data, '<')) {
        $dom->loadXml($data);
      } else {
        $dom->load($data);
      }
      if (isset($dom->documentElement)) {
        /** @noinspection PhpParamsInspection */
        $this->pages()->load($dom->documentElement);
      }
    } elseif ($data instanceof \Papaya\Xml\Element) {
      $this->pages()->load($data);
    }
  }

  /**
   * Getter/Setter for the dynamic value definition pages
   *
   * @param Structure\Pages $pages
   * @return Structure\Pages
   */
  public function pages(Structure\Pages $pages = NULL) {
    if (NULL !== $pages) {
      $this->_pages = $pages;
    } elseif (NULL === $this->_pages) {
      $this->_pages = new Structure\Pages();
    }
    return $this->_pages;
  }

  /**
   * Allow to directly loop on the pages.
   *
   * @return Structure\Pages
   */
  public function getIterator() {
    return $this->pages();
  }

  /**
   * Fetch a page by its identifier
   *
   * @param string $identifier
   * @return Structure\Page|NULL
   */
  public function getPage($identifier) {
    /** @var Structure\Page $page */
    foreach ($this->pages() as $page) {
      if ($page->getIdentifier() === $identifier) {
        return $page;
      }
    }
    return NULL;
  }

  /**
   * Convert the definition into an xml variable tree. If the Name of an element is not
   * a valid QName, the element will be ignored.
   *
   * @param array $currentValues
   * @return \Papaya\Xml\Document
   */
  public function getXmlDocument(array $currentValues) {
    $document = new \Papaya\Xml\Document();
    $rootNode = $document->appendElement('values');
    /** @var Structure\Page $page */
    foreach ($this->pages() as $page) {
      $pageNode = $rootNode->appendElement($page->name);
      /** @var Structure\Group $group */
      foreach ($page->groups() as $group) {
        $groupNode = $pageNode->appendElement($group->name);
        /** @var Structure\Value $value */
        foreach ($group->values() as $value) {
          $current = '';
          if (isset($currentValues[$page->name][$group->name][$value->name])) {
            $current = trim($currentValues[$page->name][$group->name][$value->name]);
          }
          if (empty($current) || '0' === $current || 0 === $current) {
            $current = trim($value->default);
          }
          if (!empty($current) || '0' === $current || 0 === $current) {
            $type = empty($value->type) ? 'text' : $value->type;
            if ('xhtml' === $type) {
              $groupNode
                ->appendElement($value->name, array('type' => 'xhtml'))->appendXml($current);
            } else {
              $groupNode
                ->appendElement($value->name, array('type' => $type), $current);
            }
          }
        }
      }
    }
    return $document;
  }

  /**
   * Read the data from an xml document into an recursive array.
   *
   * @param \Papaya\Xml\Element $dataNode
   * @return array
   */
  public function getArray(\Papaya\Xml\Element $dataNode) {
    $result = array();
    /** @var \Papaya\Xml\Document $document */
    $document = $dataNode->ownerDocument;
    /** @var Structure\Page $page */
    foreach ($this->pages() as $page) {
      if ($document->xpath()->evaluate('count('.$page->name.')', $dataNode)) {
        $pageNode = $document->xpath()->evaluate($page->name)->item(0);
        /** @var Structure\Group $group */
        foreach ($page->groups() as $group) {
          if ($document->xpath()->evaluate('count('.$group->name.')', $pageNode)) {
            $groupNode = $document->xpath()->evaluate($group->name, $pageNode)->item(0);
            /** @var Structure\Value $value */
            foreach ($group->values() as $value) {
              if ($document->xpath()->evaluate('count('.$value->name.')', $groupNode)) {
                /** @var \Papaya\Xml\Element $valueNode */
                $valueNode = $document->xpath()->evaluate($value->name, $groupNode)->item(0);
                $type = empty($value->type) ? 'text' : $value->type;
                if ('xhtml' === $type) {
                  $current = trim($valueNode->saveFragment());
                } else {
                  $current = trim($valueNode->textContent);
                }
                if (!empty($current) || '0' === $current) {
                  $result[$page->name][$group->name][$value->name] = $current;
                }
              }
            }
          }
        }
      }
    }
    return $result;
  }
}
