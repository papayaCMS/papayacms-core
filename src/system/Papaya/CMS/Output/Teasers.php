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
namespace Papaya\CMS\Output;

use Papaya\CMS\Content;
use Papaya\Filter\IntegerValue;
use Papaya\Iterator;
use Papaya\Request\Parameters\Name as ParameterName;
use Papaya\UI\Control as UIControl;
use Papaya\CMS\Reference\Page as PageReference;
use Papaya\XML;

/**
 * Build teaser list xml from a list of pages.
 *
 * @package Papaya-Library
 * @subpackage UI-Content
 */
class Teasers extends UIControl implements \Countable {
  /**
   * @var Content\Pages
   */
  private $_pages;

  /**
   * @var UI\Reference\Page
   */
  private $_reference;

  /**
   * thumbnail width
   *
   * @var int
   */
  private $_width;

  /**
   * thumbnail height
   *
   * @var int
   */
  private $_height;

  /**
   * thumbnail resize mode (abs, max, min, mincrop)
   *
   * @var int
   */
  private $_resizeMode;

  /**
   * @var \Papaya\CMS\Content\Views
   */
  private $_viewConfigurations;
  /**
   * @var bool
   */
  private $_replaceImageTags;
  /**
   * @var Teaser\Images
   */
  private $_teaserImages;
  /**
   * @var string|ParameterName
   */
  private $_pagingParameterName;
  /**
   * @var int
   */
  private $_pagingLimit = 0;

  /**
   * Create list, store pages and optional thumbnail configuration
   *
   * @param Content\Pages $pages
   * @param int $width
   * @param int $height
   * @param string $resizeMode
   * @param bool $replaceImageTags Replace the existing image tags inside the teaser (or append new ones, next to the teasers)
   */
  public function __construct(
    Content\Pages $pages, $width = 0, $height = 0, $resizeMode = 'mincrop', $replaceImageTags = FALSE
  ) {
    $this->pages($pages);
    $this->_width = $width;
    $this->_height = $height;
    $this->_resizeMode = $resizeMode;
    $this->_replaceImageTags = $replaceImageTags;
  }

  public function count() {
    return $this->pages()->count();
  }

  /**
   * @param string|ParameterName $parameterName
   * @param int $itemsPerPage
   */
  public function definePaging($parameterName, $itemsPerPage) {
    $this->_pagingParameterName = $parameterName;
    $this->_pagingLimit = (int)$itemsPerPage;
  }

  /**
   * Getter/Setter for the pages subobject
   *
   * @param Content\Pages $pages
   *
   * @return Content\Pages
   */
  public function pages(Content\Pages $pages = NULL) {
    if (NULL !== $pages) {
      $this->_pages = $pages;
    }
    return $this->_pages;
  }

  /**
   * Getter/Setter for the view configurations
   *
   * @param Content\View\Configurations $viewConfigurations
   *
   * @return Content\View\Configurations
   */
  public function viewConfigurations(Content\View\Configurations $viewConfigurations = NULL) {
    if (NULL !== $viewConfigurations) {
      $this->_viewConfigurations = $viewConfigurations;
    } elseif (NULL === $this->_viewConfigurations) {
      $this->_viewConfigurations = new Content\View\Configurations();
      $this->_viewConfigurations->papaya($this->papaya());
      $viewIds = \iterator_to_array(
        new Iterator\ArrayMapper($this->pages(), 'view_id'), FALSE
      );
      $this->_viewConfigurations->activateLazyLoad(
        [
          'id' => $viewIds,
          'mode_id' => $this->papaya()->request->modeId,
          'type' => Content\View\Configurations::TYPE_OUTPUT
        ]
      );
    }
    return $this->_viewConfigurations;
  }

  /**
   * Getter/Setter for the template reference subobject used to generate links to the subpages
   *
   * @param PageReference|NULL $reference
   * @return PageReference
   */
  public function reference(PageReference $reference = NULL): PageReference {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new PageReference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }

  /**
   * Fetch teasers from plugins and append them to parent xml element. Append thumnbails
   * if configuration was provided.
   *
   * @see \Papaya\XML\Appendable::appendTo()
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $teasers = $parent->appendElement('teasers');
    $pages = $this->pages();
    if ($this->_pagingLimit > 0) {
      $pages = new \LimitIterator(new \IteratorIterator($this->pages()), 0, $this->_pagingLimit);
      $currentPage = $this->papaya()->request->getParameter(
        $this->_pagingParameterName, 1, new IntegerValue(1)
      );
      if ($currentPage > 1) {
        $reference = clone $this->reference();
        $reference->setParameters(
          $reference->getParameters()->set($this->_pagingParameterName, $currentPage > 2 ? $currentPage - 1 : null)
        );
        $teasers->setAttribute(
          'href-previous', (string)$reference
        );
      }
      if (count($this->pages()) > $this->_pagingLimit) {
        $reference = clone $this->reference();
        $reference->setParameters(
          $reference->getParameters()->set($this->_pagingParameterName, $currentPage + 1)
        );
        $teasers->setAttribute(
          'href-next', (string)$reference
        );
      }
    }
    foreach ($pages as $pageData) {
      $this->appendTeaserTo($teasers, $pageData);
    }
    if (!$this->_replaceImageTags && ($this->_width > 0 || $this->_height > 0)) {
      $teasers->append($this->teaserImages());
    }
  }

  /**
   * Instantiate plugin and fetch the teaser from it.
   *
   * @param XML\Element $parent
   * @param array $pageData
   */
  public function appendTeaserTo(XML\Element $parent, array $pageData) {
    if (!empty($pageData['module_guid'])) {
      $page = new Page(
        $pageData['id'], $pageData['language_id'], $this->pages()->isPublic()
      );
      $page->papaya($this->papaya());
      $page->assign($pageData);
      if (NULL !== $pageData['viewmode_id']) {
        $viewData = $this->viewConfigurations()->offsetGet(
          [$pageData['view_id'], $pageData['viewmode_id'], Content\View\Configurations::TYPE_OUTPUT]
        );
      } else {
        $viewData = ['id' => -1, 'mode_id' => -1, 'type' => Content\View\Configurations::TYPE_OUTPUT];
      }
      $page->appendQuoteTo($parent, [], $viewData);
      if ($this->_replaceImageTags && ($this->_width > 0 || $this->_height > 0)) {
        $this->teaserImages()->replaceIn($parent);
      }
    }
  }

  public function teaserImages(Teaser\Images $teaserImages = NULL) {
    if (NULL !== $teaserImages) {
      $this->_teaserImages = $teaserImages;
    } elseif (NULL === $this->_teaserImages) {
      $this->_teaserImages = new Teaser\Images(
        $this->_width, $this->_height, $this->_resizeMode
      );
      $this->_teaserImages->papaya($this->papaya());
    }
    return $this->_teaserImages;
  }
}
