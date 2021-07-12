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
namespace Papaya\CMS\Reference {

  use Papaya\Request;
  use Papaya\UI;
  use Papaya\URL;

  /**
   * Papaya Interface Page Reference (Hyperlink Reference)
   */
  class Page extends UI\Reference implements \IteratorAggregate {
    /**
     * Page identification data
     *
     * @var array
     */
    protected $_pageData = [
      'title' => 'index',
      'category_id' => 0,
      'id' => 0,
      'language' => '',
      'mode' => 'html',
      'preview' => FALSE,
      'preview_time' => 0
    ];

    private $_pageReferences;

    public function getIterator(): \ArrayIterator {
      return new \ArrayIterator($this->_pageData);
    }

    /**
     * Static create function to allow fluent calls.
     *
     * @param URL $url
     *
     * @return self
     */
    public static function create(URL $url = NULL) {
      return new self($url);
    }

    /**
     * @param bool $forPublic
     *
     * @return string
     * @see \Papaya\CMS\Reference::get()
     *
     */
    public function get($forPublic = FALSE) {
      $result = $this->cleanupPath($this->url()->getHostURL().$this->_basePath, $forPublic);
      if (!$this->isStartPage()) {
        $result .= $this->_pageData['title'];
        if ($this->_pageData['category_id'] > 0) {
          $result .= '.'.$this->_pageData['category_id'];
        }
        if (
          $this->_pageData['id'] > 0 ||
          $this->_pageData['category_id'] > 0
        ) {
          $result .= '.'.$this->_pageData['id'];
        }
        if (!empty($this->_pageData['language'])) {
          $result .= '.'.$this->_pageData['language'];
        }
        $result .= '.'.$this->_pageData['mode'];
        if ($this->_pageData['preview']) {
          $result .= '.preview';
          if ($this->_pageData['preview_time'] > 0) {
            $result .= '.'.$this->_pageData['preview_time'];
          }
        }
      }
      $result .= $this->getQueryString();
      $result .= $this->getFragment();
      return $result;
    }

    /**
     * @param Request $request
     *
     * @return $this
     * @see \Papaya\CMS\Reference::load()
     *
     */
    public function load(Request $request) {
      parent::load($request);
      $this->setPageTitle(
        $request->getParameter('page_title', 'index', NULL, Request::SOURCE_PATH)
      );
      $this->setPageId(
        $request->getParameter('page_id', 0, NULL, Request::SOURCE_PATH),
        FALSE
      );
      $this->setPageLanguage(
        $request->getParameter('language', '', NULL, Request::SOURCE_PATH),
        FALSE
      );
      $this->setOutputMode(
        $request->getParameter('output_mode', 'html', NULL, Request::SOURCE_PATH)
      );
      $this->setPreview(
        $request->getParameter('preview', FALSE, NULL, Request::SOURCE_PATH),
        $request->getParameter('preview_time', 0, NULL, Request::SOURCE_PATH)
      );
      return $this;
    }

    /**
     * Set page id
     *
     * @param int $pageId
     * @param bool $autoConfigure
     *
     * @return $this
     */
    public function setPageId($pageId, $autoConfigure = TRUE) {
      $this->prepare();
      if ($pageId > 0) {
        $this->_pageData['id'] = (int)$pageId;
        if ($autoConfigure && ($factory = $this->pageReferences())) {
          $factory->configure($this);
        }
      }
      return $this;
    }

    /**
     * Get page id
     *
     * @return int
     */
    public function getPageId() {
      return $this->_pageData['id'];
    }

    /**
     * Set page title (normalized string)
     *
     * @param $pageTitle
     *
     * @return $this
     */
    public function setPageTitle($pageTitle) {
      $this->prepare();
      if (\preg_match('(^[a-zA-Z\d_-]+$)D', $pageTitle)) {
        $this->_pageData['title'] = (string)$pageTitle;
      } else {
        $pageTitle = \Papaya\Utility\File::normalizeName($pageTitle, 100, $this->getPageLanguage());
        if (!empty($pageTitle)) {
          $this->_pageData['title'] = (string)$pageTitle;
        }
      }
      return $this;
    }

    /**
     * Get page title
     *
     * @return string
     */
    public function getPageTitle() {
      return $this->_pageData['title'];
    }

    /**
     * Set page language identifier
     *
     * @param string $languageIdentifier
     * @param bool $autoConfigure
     *
     * @return $this
     */
    public function setPageLanguage($languageIdentifier, $autoConfigure = TRUE) {
      $this->prepare();
      if (\preg_match('(^[a-z]{2,6}$)D', $languageIdentifier)) {
        $this->_pageData['language'] = (string)$languageIdentifier;
        if ($this->_pageData['id'] > 0) {
          if ($autoConfigure && isset($this->papaya()->pageReferences)) {
            $this->papaya()->pageReferences->configure($this);
          }
        }
      }
      return $this;
    }

    public function getPageLanguage() {
      return $this->_pageData['language'];
    }

    /**
     * Set category id
     *
     * @param int $categoryId
     *
     * @return $this
     */
    public function setCategoryId($categoryId) {
      $this->prepare();
      if ($categoryId >= 0) {
        $this->_pageData['category_id'] = (int)$categoryId;
      }
      return $this;
    }

    /**
     * Set output mode identifier
     *
     * @param string $outputMode
     *
     * @return $this
     */
    public function setOutputMode($outputMode) {
      $this->prepare();
      if (\preg_match('(^[a-z]{1,20}$)D', $outputMode)) {
        $this->_pageData['mode'] = (string)$outputMode;
      }
      return $this;
    }

    /**
     * Get output mode identifier
     *
     * @return string
     */
    public function getOutputMode() {
      return $this->_pageData['mode'];
    }

    /**
     * Set preview mode and time
     *
     * @param bool $isPreview
     * @param int $previewTime optional, default value 0
     *
     * @return $this
     */
    public function setPreview($isPreview, $previewTime = NULL) {
      $this->prepare();
      $this->_pageData['preview'] = (bool)$isPreview;
      if ($isPreview && NULL !== $previewTime) {
        $this->_pageData['preview_time'] = (int)$previewTime;
      } elseif (!$isPreview) {
        $this->_pageData['preview_time'] = 0;
      }
      return $this;
    }

    /**
     * Getter/Setter for the page reference factory - an object that load page and domain
     * data for links.
     *
     * @param Page\Factory $factory
     *
     * @return Page\Factory
     */
    public function pageReferences(Page\Factory $factory = NULL) {
      if (NULL !== $factory) {
        $this->_pageReferences = $factory;
      } elseif (NULL === $this->_pageReferences && isset($this->papaya()->pageReferences)) {
        $this->_pageReferences = $this->papaya()->pageReferences;
      }
      return $this->_pageReferences;
    }

    /**
     * @return bool
     */
    public function isStartPage() {
      if ($this->_pageData['preview'] || $this->_pageData['category_id']) {
        return FALSE;
      }
      if ($pageReferences = $this->pageReferences()) {
        return $pageReferences->isStartPage($this);
      }
      return FALSE;
    }
  }
}
