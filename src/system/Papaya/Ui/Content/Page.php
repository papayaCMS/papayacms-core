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
   * @var \PapayaUiReferencePage
   */
  private $_reference;

  /**
   * @param int $pageId
   * @param int|string|PapayaContentLanguage $language
   * @param bool $isPublic
   */
  public function __construct($pageId, $language, $isPublic = TRUE) {
    $this->_pageId = (int)$pageId;
    $this->_language = $language;
    $this->_isPublic = (boolean)$isPublic;
  }

  /**
   * @param array|Traversable $data
   */
  public function assign($data) {
    PapayaUtilConstraints::assertArrayOrTraversable($data);
    $this->page()->assign($data);
    $this->translation()->assign($data);
  }

  /**
   * @param PapayaContentPage $page
   * @return PapayaContentPage|PapayaContentPagePublication
   */
  public function page(PapayaContentPage $page = NULL) {
    if (isset($page)) {
      $this->_page = $page;
    } elseif (NULL == $this->_page) {
      if ($this->isPublic()) {
        $this->_page = new PapayaContentPagePublication();
      } else {
        $this->_page = new PapayaContentPage();
      }
      $this->_page->activateLazyLoad($this->_pageId);
    }
    return $this->_page;
  }

  /**
   * @param PapayaContentPageTranslation $translation
   * @return PapayaContentPagePublicationTranslation|PapayaContentPageTranslation
   */
  public function translation(PapayaContentPageTranslation $translation = NULL) {
    if (isset($translation)) {
      $this->_translation = $translation;
    } elseif (NULL == $this->_translation) {
      if ($this->isPublic()) {
        $this->_translation = new PapayaContentPagePublicationTranslation();
      } else {
        $this->_translation = new PapayaContentPageTranslation();
      }
      if ($language = $this->getPageLanguage()) {
        $this->_translation->activateLazyLoad(
          array('id' => $this->_pageId, 'language_id' => $language['id'])
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
   * @return null|PapayaContentLanguage
   */
  public function getPageLanguage() {
    if ($this->_language instanceof PapayaContentLanguage) {
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

  /**
   * Append the page teaser
   *
   * @param \PapayaXmlElement $parent
   * @param array $parameters
   */
  public function appendQuoteTo(PapayaXmlElement $parent, array $parameters = []) {
    $moduleGuid = $this->translation()->moduleGuid;
    if (!empty($moduleGuid)) {
      $plugin = $this->papaya()->plugins->get($moduleGuid, $this, $this->translation()->content);
      if ($plugin) {
        $reference = clone $this->reference();
        $reference->setPageId($this->getPageId(), TRUE);
        $teaser = $parent->appendElement(
          'teaser',
          array(
            'page-id' => $this->getPageId(),
            'plugin-guid' => $moduleGuid,
            'plugin' => get_class($plugin),
            'view' => $this->translation()->viewName,
            'href' => $reference->getRelative(),
            'published' => PapayaUtilDate::timestampToString($this->translation()->modified),
            'created' => PapayaUtilDate::timestampToString($this->translation()->created)
          )
        );
        if ($plugin instanceof PapayaPluginQuoteable) {
          if ($plugin instanceof PapayaPluginConfigurable) {
            $plugin->configuration()->merge($parameters);
          }
          $plugin->appendQuoteTo($teaser);
        } elseif ($plugin instanceof base_content &&
                  method_exists($plugin, 'getParsedTeaser')) {
          $teaser->appendXml((string)$plugin->getParsedTeaser($parameters));
        }
        /** @var PapayaXmlDocument $document */
        $document = $teaser->ownerDocument;
        if (0 === (int)$document->xpath()->evaluate('count(node())', $teaser)) {
          $teaser->parentNode->removeChild($teaser);
        }
      }
    }
  }

  /**
   * Getter/Setter for the template reference subobject used to generate links to the subpages
   *
   * @param PapayaUiReferencePage $reference
   * @return PapayaUiReferencePage
   */
  public function reference(PapayaUiReferencePage $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new PapayaUiReferencePage();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}
