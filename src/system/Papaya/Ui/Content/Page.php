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

class PapayaUiContentPage extends PapayaObject {

  /**
   * @var PapayaContentPage
   */
  private $_page = NULL;
  /**
   * @var PapayaContentPageTranslation
   */
  private $_translation = NULL;

  /**
   * @var int
   */
  private $_pageId = 0;

  /**
   * @var int|PapayaContentLanguage|string
   */
  private $_language = '';
  /**
   * @var bool
   */
  private $_isPublic = TRUE;

  /**
   * @param int $pageId
   * @param int|string|\PapayaContentLanguage $language
   * @param bool $isPublic
   */
  public function __construct($pageId, $language, $isPublic = TRUE) {
    $this->_pageId = (int)$pageId;
    $this->_language = $language;
    $this->_isPublic = (boolean)$isPublic;
  }

  /**
   * @param array|\Traversable $data
   */
  public function assign($data) {
    \PapayaUtilConstraints::assertArrayOrTraversable($data);
    $this->page()->assign($data);
    $this->translation()->assign($data);
  }

  /**
   * @param \PapayaContentPage $page
   * @return \PapayaContentPage|\PapayaContentPagePublication
   */
  public function page(\PapayaContentPage $page = NULL) {
    if (isset($page)) {
      $this->_page = $page;
    } elseif (NULL == $this->_page) {
      if ($this->isPublic()) {
        $this->_page = new \PapayaContentPagePublication();
      } else {
        $this->_page = new \PapayaContentPage();
      }
      $this->_page->activateLazyLoad($this->_pageId);
    }
    return $this->_page;
  }

  /**
   * @param \PapayaContentPageTranslation $translation
   * @return \PapayaContentPagePublicationTranslation|\PapayaContentPageTranslation
   */
  public function translation(\PapayaContentPageTranslation $translation = NULL) {
    if (isset($translation)) {
      $this->_translation = $translation;
    } elseif (NULL == $this->_translation) {
      if ($this->isPublic()) {
        $this->_translation = new \PapayaContentPagePublicationTranslation();
      } else {
        $this->_translation = new \PapayaContentPageTranslation();
      }
      if ($language = $this->getPageLanguage()) {
        $this->_translation->activateLazyLoad(
          array('page_id' => $this->_pageId, 'language_id' => $language['id'])
        );
      }
    }
    return $this->_translation;
  }

  /**
   * @return int
   */
  public function getPageId() {
    return $this->_pageId;
  }

  /**
   * @return int
   */
  public function getPageViewId() {
    $translation = $this->translation();
    return $translation['view_id'];
  }

  /**
   * @return null|\PapayaContentLanguage
   */
  public function getPageLanguage() {
    if ($this->_language instanceof \PapayaContentLanguage) {
      return $this->_language;
    } elseif (isset($this->_language) && isset($this->papaya()->languages)) {
      return $this->_language = $this->papaya()->languages->getLanguage($this->_language);
    }
    return NULL;
  }

  /**
   * @return bool
   */
  public function isPublic() {
    return $this->_isPublic;
  }

}
