<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\UI\Content {

  use Papaya\Application\Access as ApplicationAccess;
  use Papaya\Content\Link\Types as LinkTypes;
  use Papaya\Content\Page\Publications as PagePublications;
  use Papaya\Content\Page\Tree as PageTree;
  use Papaya\Content\Pages;
  use Papaya\Iterator\Tree\DepthLimit;
  use Papaya\Utility\Arrays as ArrayUtility;
  use Papaya\Utility\Date as DateUtility;
  use Papaya\XML\Appendable as XMLAppendable;
  use Papaya\XML\Element as XMLElement;

  class Sitemap implements XMLAppendable, ApplicationAccess {

    use ApplicationAccess\Aggregation;

    const MODE_PATH = 'path';
    const MODE_FIX = 'fix';
    const MODE_BREADCRUMB = 'breadcrumb';

    const CHANGE_FREQUENCIES = [
      0 => 'never',
      1 => 'yearly',
      2 => 'monthly',
      3 => 'weekly',
      4 => 'daily',
      5 => 'hourly',
      6 => 'always'
    ];

    public static $MODES = [
      self::MODE_PATH => 'Path',
      self::MODE_FIX => 'Fixed',
      self::MODE_BREADCRUMB => 'Breadcrumb',
    ];

    /**
     * @var PageTree
     */
    private $_staticPageTree;
    /**
     * @var PageTree
     */
    private $_dynamicPageTree;

    /**
     * @var Pages
     */
    private $_pages;

    /**
     * @var LinkTypes
     */
    private $_linkTypes;

    private $_mode;
    private $_ancestorPageId;
    private $_currentPageId;
    private $_languageId;

    private $_staticDepth = 1;
    private $_staticOffset = 0;

    /**
     * @var bool
     */
    private $_includeHiddenPages = FALSE;

    /**
     * @param int $ancestorPageId
     * @param int $currentPageId
     * @param int $languageId
     * @param string $mode
     */
    public function __construct($ancestorPageId, $currentPageId, $languageId, $mode = self::MODE_PATH) {
      $this->_ancestorPageId = (int)$ancestorPageId;
      $this->_currentPageId = (int)$currentPageId;
      $this->_languageId = (int)$languageId;
      $this->_mode = isset(self::$MODES[$mode]) ? $mode : self::MODE_PATH;
    }

    /**
     * @param int $offset
     * @param int $depth
     */
    public function setOffsetAndStaticDepth($offset, $depth) {
      $this->_staticOffset = $offset;
      $this->_staticDepth = $depth;
    }

    /**
     * @param bool $includeHiddenPages
     */
    public function setIncludeHiddenPages($includeHiddenPages) {
      $this->_includeHiddenPages = (bool)$includeHiddenPages;
    }

    /**
     * Create dom node structure of the given object and append it to the given xml
     * element node.
     *
     * @param XMLElement $parent
     */
    public function appendTo(XMLElement $parent) {
      $path = $this->getPathIds();
      if (count($this->staticPageTree()) > 0) {
        $this->appendPagesTo(
          $parent,
          new DepthLimit($this->staticPageTree()->getIterator(), $this->_staticDepth, $this->_staticOffset),
          $path
        );
      } else {
        $this->appendPagesTo($parent, $this->dynamicPageTree()->getIterator(), $path);
      }
    }

    public function appendPagesTo(XMLElement $parent, \RecursiveIterator $pages, $paths) {
      $linkTypes = $this->linkTypes();
      foreach ($pages as $page) {
        $pageId = (int)$page['id'];
        $linkType = isset($linkTypes[$page['link_type_id']]) ? $linkTypes[$page['link_type_id']] : NULL;
        if (!($this->_includeHiddenPages || $linkType === NULL || $linkType['is_visible'])) {
          continue;
        }
        $reference = $this->papaya()->pageReferences->get(
          $this->papaya()->request->languageIdentifier,
          $pageId
        );
        $active = ($pageId === $this->_currentPageId);
        $activeWithin = in_array($pageId, $paths->active, FALSE);
        $pageNode = $parent->appendElement(
          'page',
          [
            'id' => $pageId,
            'active' => $active ? 'true' : NULL,
            'active-within' => (!$active) && $activeWithin ? 'true' : NULL,
            'title' => $page['title'],
            'view' => trim($page['view_name']) !== '' ? $page['view_name'] : NULL,
            'module' => trim($page['module_guid']) !== '' ? $page['module_guid'] : NULL,
            'href' => $reference->valid() ? (string)$reference->getRelative() : NULL,
            'target' => $linkType && $linkType['target'] !== '' && $linkType['target'] !== '_self'
              ? $linkType['target'] : NULL,
            'hidden' => ($linkType && !$linkType['is_visible']) ? 'true' : NULL,
            'last-modified' => DateUtility::timestampToString($page['modified']),
            'priority' => number_format($page['priority'] / 100, 1, '.', ''),
            'change-frequency' => $this->mapChangeFrequencyToString($page['change_frequency'])
          ]
        );
        if ($linkType) {
          if (!$linkType['is_visible']) {
            $pageNode->setAttribute('hidden', 'true');
          }
          if ($linkType['class'] !== '') {
            $pageNode->setAttribute('class', $linkType['class']);
          }
          if ($linkType['is_popup']) {
            $pageNode->setAttribute('popup', json_encode(self::getPopupConfiguration($linkType['popup_options'])));
          }
        }
        if ($paths->dynamicAncestor === $pageId) {
          $this->appendPagesTo($pageNode, $this->dynamicPageTree()->getIterator(), $paths);
        } elseif ($pages->hasChildren()) {
          $this->appendPagesTo($pageNode, $pages->getChildren(), $paths);
        }
      }
    }

    public function pages(Pages $pages = NULL) {
      if (NULL !== $pages) {
        $this->_pages = $pages;
      } elseif (NULL === $this->_pages) {
        $this->_pages = $this->papaya()->request->isPreview ? new Pages() : new PagePublications();
        $this->_pages->papaya($this->papaya());
        $this->_pages->activateLazyLoad(
          [
            'id' => [$this->_ancestorPageId, $this->_currentPageId],
            'language_id' => [$this->_languageId]
          ]
        );
      }
      return $this->_pages;
    }

    public function staticPageTree(PageTree $pageTree = NULL) {
      if (NULL !== $pageTree) {
        $this->_staticPageTree = $pageTree;
      } elseif (NULL === $this->_staticPageTree) {
        $this->_staticPageTree = new PageTree($this->papaya()->request->isPreview);
        $this->_staticPageTree->papaya($this->papaya());
        if ($this->_staticDepth > 0 && ($this->_mode === self::MODE_PATH || $this->_mode === self::MODE_FIX)) {
          $ancestors = $this->getAncestorIds();
          array_unshift($ancestors, 0);
          $maximumDepth = count($ancestors) + $this->_staticOffset + max($this->_staticDepth, 0) - 2;
          $filter = [
            'language_id' => [$this->_languageId],
            'maximum-depth' => $maximumDepth,
            'or' => [
              'ancestors,like' => ArrayUtility::encodeAndQuoteIdList($ancestors).'*',
              'parent_id' => $this->_ancestorPageId
            ]
          ];
          $this->_staticPageTree->activateLazyLoad($filter);
        }
      }
      return $this->_staticPageTree;
    }

    /**
     * @param PageTree|NULL $pageTree
     * @return PageTree
     */
    public function dynamicPageTree(PageTree $pageTree = NULL) {
      if (NULL !== $pageTree) {
        $this->_dynamicPageTree = $pageTree;
      } elseif (NULL === $this->_dynamicPageTree) {
        $this->_dynamicPageTree = new PageTree($this->papaya()->request->isPreview);
        $this->_dynamicPageTree->papaya($this->papaya());
        if ($this->_mode === self::MODE_PATH) {
          $pathIds = $this->getPathIds()->dynamic;
          if (count($pathIds) > 0) {
            $filter = [
              'language_id' => [$this->_languageId],
              'parent_id' => $pathIds
            ];
            $this->_dynamicPageTree->activateLazyLoad($filter);
          }
        } elseif ($this->_mode === self::MODE_BREADCRUMB) {
          $filter = [
            'language_id' => [$this->_languageId],
            'id' => $this->getPathIds()->active
          ];
          $this->_dynamicPageTree->activateLazyLoad($filter);
        }
      }
      return $this->_dynamicPageTree;
    }

    public function linkTypes(LinkTypes $linkTypes = NULL) {
      if (NULL !== $linkTypes) {
        $this->_linkTypes = $linkTypes;
      } elseif (NULL === $this->_linkTypes) {
        $this->_linkTypes = new LinkTypes();
        $this->_linkTypes->papaya($this->papaya());
        $this->_linkTypes->activateLazyLoad();
      }
      return $this->_linkTypes;
    }

    private function getAncestorIds() {
      $pages = $this->pages();
      if (isset($pages[$this->_ancestorPageId])) {
        $ids = $pages[$this->_ancestorPageId]['path'];
        $ids[] = $pages[$this->_ancestorPageId]['id'];
        return $ids;
      }
      return [];
    }

    private function getPathIds($fullPath = TRUE) {
      $result = new \stdClass();
      $result->active = [];
      $result->dynamic = [];
      $result->dynamicAncestor = NULL;
      $pages = $this->pages();
      if (isset($pages[$this->_ancestorPageId], $pages[$this->_currentPageId])) {
        // build path including current page id
        $result->active = $pages[$this->_currentPageId]['path'];
        $result->active[] = $pages[$this->_currentPageId]['parent'];
        $result->active[] = $pages[$this->_currentPageId]['id'];
        // validate ancestor is in path of current page
        if (in_array($this->_ancestorPageId, $result->active, FALSE)) {
          // remove static part
          $result->dynamic = array_slice(
            $result->active, count($this->getAncestorIds()) + $this->_staticOffset + $this->_staticDepth
          );
          $result->dynamicAncestor = (int)reset($result->dynamic);
        }
      }
      return $result;
    }

    private static function getPopupConfiguration($options) {
      $width = ArrayUtility::get($options, 'width');
      $height = ArrayUtility::get($options, 'height');
      $hasScrollBars = ArrayUtility::get($options, 'scrollbars');
      $isResizable = ArrayUtility::get($options, 'resizable');
      $hasToolbar = ArrayUtility::get($options, 'toolbar');
      $top = ArrayUtility::get($options, 'top', NULL);
      $left = ArrayUtility::get($options, 'left', NULL);
      $hasMenubar = ArrayUtility::get($options, 'menubar');
      $hasLocationInput = ArrayUtility::get($options, 'location');
      $hasStatusBar = ArrayUtility::get($options, 'status');
      $disabledStates = [FALSE, '', 'no'];
      $result = [
        'width' => $width,
        'height' => $height
      ];
      if (!empty($top)) {
        $result['top'] = $top;
      }
      if (!empty($left)) {
        $result['left'] = $left;
      }
      $result['scollBars'] = $hasScrollBars;
      $result['resizable'] = (int)in_array($isResizable, $disabledStates, FALSE);
      $result['toolBar'] = (int)in_array($hasToolbar, $disabledStates, FALSE);
      $result['menuBar'] = (int)in_array($hasMenubar, $disabledStates, FALSE);
      $result['locationBar'] = (int)in_array($hasLocationInput, $disabledStates, FALSE);
      $result['statusBar'] = (int)in_array($hasStatusBar, $disabledStates, FALSE);
      return $result;
    }

    private function mapChangeFrequencyToString($changeFrequency) {
      return NULL !== self::CHANGE_FREQUENCIES[$changeFrequency]
        ? self::CHANGE_FREQUENCIES[$changeFrequency] : 'monthly';
    }
  }
}


