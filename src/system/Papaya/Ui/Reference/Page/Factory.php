<?php
/**
* A application width object that provides data for references
*
* Allows to load pages and provides basic function for the working copy and publication.
*
* This is an abstract superclass, please use {@see PapayaContentPageWork} to modify the
* working copy of a page or {@see PapayaContentPagePublication} to use the published page.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Ui
* @version $Id: Factory.php 39698 2014-03-27 16:57:37Z weinert $
*/

/**
* A application width object that provides data for references
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiReferencePageFactory extends PapayaObject {

  /**
   * @var array
   */
  private $_pageData = array();

  /**
   * @var PapayaContentLanguage
   */
  private $_currentLanguage = NULL;

  /**
   * @var bool
   */
  private $_preview = FALSE;

  /**
   * @var PapayaContentPages
   */
  private $_pages = NULL;

  /**
   * @var PapayaContentLanguages
   */
  private $_languages = NULL;

  /**
   * @var PapayaDomains
   */
  private $_domains = NULL;

  /**
   * @var PapayaContentLinkTypes
   */
  private $_linkTypes = NULL;

  /**
   * Create a page reference
   *
   * @return \PapayaUiReferencePage
   */
  public function create() {
    return new PapayaUiReferencePage();
  }

  /**
  * Create and configure a page reference
  *
  * @param string $languageIdentifier
  * @param integer $pageId
   * @return \PapayaUiReferencePage
   */
  public function get($languageIdentifier, $pageId) {
    $languageIdentifier = $this->validateLanguageIdentifier($languageIdentifier);
    $reference = $this->create();
    $reference->pageReferences($this);
    $reference->papaya($this->papaya());
    $reference->setPageLanguage($languageIdentifier);
    $reference->setPageId($pageId, FALSE);
    return $this->configure($reference);
  }

  /**
  * Configure a given page reference
  *
  * @param PapayaUiReferencePage $reference
   * @return \PapayaUiReferencePage
   */
  public function configure(PapayaUiReferencePage $reference) {
    $languageIdentifier = $this->validateLanguageIdentifier($reference->getPageLanguage());
    $pageId = $reference->getPageId();
    $reference->setPreview($this->isPreview());
    if ($pageId > 0) {
      if ($data = $this->getPageData($languageIdentifier, $pageId)) {
        $reference->setPageLanguage($languageIdentifier, FALSE);
        $reference->setPageTitle($this->prepareTitle($data['title'], $languageIdentifier));
        if ($this->isPreview()) {
          return $reference;
        } elseif (is_array($domain = $this->getDomainData($languageIdentifier, $pageId))) {
          $reference->url()->setHost($domain['host']);
          if ($domain['scheme'] != PapayaUtilServerProtocol::BOTH) {
            $reference->url()->setScheme(
              PapayaUtilServerProtocol::get($domain['scheme'])
            );
          } elseif (!$reference->url()->getScheme()) {
            $reference->url()->setScheme('http');
          }
        } elseif (!$domain) {
          $reference->valid(FALSE);
        }
        if ($data['scheme'] != PapayaUtilServerProtocol::BOTH) {
          $reference->url()->setScheme(
            PapayaUtilServerProtocol::get($data['scheme'])
          );
        }
      } else {
        $reference->valid(FALSE);
      }
    }
    return $reference;
  }

  /**
  * Prepare the page ttile to be used in the filename/url.
  *
  * @param string $title
  * @param string $languageIdentifier
  * @return string
  */
  private function prepareTitle($title, $languageIdentifier) {
    return strtolower(
      PapayaUtilFile::normalizeName(
        $title,
        $this->papaya()->options->get('PAPAYA_URL_NAMELENGTH', 50),
        $languageIdentifier
      )
    );
  }

  /**
  * Check if the object/cms is in preview mode or not
  *
  * @return boolean
  */
  public function isPreview() {
    return $this->_preview;
  }

  /**
  * Set the preview stat, unset the pages subobject if needed.
  *
  * @param boolean $preview
  */
  public function setPreview($preview) {
    if ($this->_preview != $preview) {
      $this->_preview = (boolean)$preview;
      $this->_pages = NULL;
    }
  }

  /**
  * Get the page data
  *
  * @param string $languageIdentifier
  * @param integer $pageId
  * @return array|FALSE
  */
  public function getPageData($languageIdentifier, $pageId) {
    $languageIdentifier = $this->validateLanguageIdentifier($languageIdentifier);
    $this->lazyLoadPage($languageIdentifier, $pageId);
    if ($this->isPageLoaded($languageIdentifier, $pageId) &&
        is_array($this->_pageData[$languageIdentifier][$pageId])) {
      return $this->_pageData[$languageIdentifier][$pageId];
    }
    return FALSE;
  }

  /**
  * Lazy load the data for given page (translation)
  *
  * @param string $languageIdentifier
  * @param integer $pageId
  */
  private function lazyLoadPage($languageIdentifier, $pageId) {
    if (!$this->isPageLoaded($languageIdentifier, $pageId)) {
      $language = $this->languages()->getLanguageByIdentifier($languageIdentifier);
      $pages = $this->pages();
      if ($language &&
          $pages->load($this->getFilter($pageId, $language->id))) {
        $this->_pageData[$languageIdentifier][$pageId] = isset($pages[$pageId])
          ? $pages[$pageId] : FALSE;
      }
    }
  }

  /**
  * Check if the data for a given language and page id is loaded.
  *
  * @param string $languageIdentifier
  * @param integer $pageId
  * @return boolean
  */
  private function isPageLoaded($languageIdentifier, $pageId) {
    if (isset($this->_pageData[$languageIdentifier][$pageId])) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
  * Get domain data for the specified language/page.
  *
  * TRUE means that it is the same domain and a relative url is possible
  * FALSE means it is not accessible
  * array() is the target domain
  *
  * The data ist stored for each page, so it needs to be caclulated only once for each page.
  *
  * @param string $languageIdentifier
  * @param integer $pageId
  * @return array|boolean
  */
  public function getDomainData($languageIdentifier, $pageId) {
    $result = FALSE;
    if ($pageData = $this->getPageData($languageIdentifier, $pageId)) {
      if (isset($pageData['domain'])) {
        $result = $pageData['domain'];
      } else {
        $path = $pageData['path'];
        array_push($path, $pageData['parent'], $pageData['id']);
        $domains = $this->domains()->getDomainsByPath($path);
        $current = $this->domains()->getCurrent();
        $languageId = $this->languages()->getLanguage($languageIdentifier)->id;
        if ($current &&
            isset($domains[$current['id']]) &&
            $this->isDomainWithLanguage($current, $languageId)) {
          // given page is accessible on current domain
          $result = TRUE;
        } else {
          // return the first domain that can show this page in the requested language
          foreach ($domains as $domain) {
            if ($this->isDomainWithLanguage($domain, $languageId) &&
                $this->isDomainWithoutWildcards($domain)) {
              if (!$result) {
                $result = $domain;
              }
              if ($domain['group_id'] == $current['group_id']) {
                $result = $domain;
                break;
              }
            }
          }
        }
        if (!$result && !$current) {
          $result = TRUE;
        }
        //store result for a second call
        $this->_pageData[$languageIdentifier][$pageId]['domain'] = $result;
      }
    }
    return $result;
  }

  /**
  * A callback to filter out all domain with wildcards in the hostname. These domains
  * are ambigous and can not be used as target. However it can be the current domain, so they
  * can not be filtered on loading.
  *
  * @param array $domain
   * @return bool
   */
  public function isDomainWithoutWildcards($domain) {
    return FALSE === strpos($domain['host'], '*');
  }

  private function isDomainWithLanguage($domain, $languageId) {
    return (
      $domain['language_id'] == 0 ||
      $domain['language_id'] == $languageId
    );
  }

  /**
  * Get link attributes object for the given page
  *
  * @param string $languageIdentifier
  * @param integer $pageId
  * @return NULL|PapayaUiLinkAttributes
  */
  public function getLinkAttributes($languageIdentifier, $pageId) {
    if ($pageData = $this->getPageData($languageIdentifier, $pageId)) {
      $linkTypes = $this->linkTypes();
      if (isset($linkTypes[$pageData['linktype_id']])) {
        $linkType = $linkTypes[$pageData['linktype_id']];
        $attributes = new PapayaUiLinkAttributes();
        $attributes->class = $linkType['class'];
        if ($linkType['is_popup']) {
          $width = PapayaUtilArray::get($linkType['popup_options'], 'popup_width', '80%');
          $height = PapayaUtilArray::get($linkType['popup_options'], 'popup_height', '80%');
          $top = PapayaUtilArray::get($linkType['popup_options'], 'popup_top', NULL);
          $left = PapayaUtilArray::get($linkType['popup_options'], 'popup_left', NULL);
          $options = 0;
          $options = $this->setLinkPopupOption(
            $options,
            PapayaUiLinkAttributes::OPTION_RESIZEABLE,
            $linkType['popup_options'],
            'popup_resizable'
          );
          $options = $this->setLinkPopupOption(
            $options,
            PapayaUiLinkAttributes::OPTION_TOOLBAR,
            $linkType['popup_options'],
            'popup_toolbar'
          );
          $options = $this->setLinkPopupOption(
            $options,
            PapayaUiLinkAttributes::OPTION_MENUBAR,
            $linkType['popup_options'],
            'popup_menubar'
          );
          $options = $this->setLinkPopupOption(
            $options,
            PapayaUiLinkAttributes::OPTION_LOCATIONBAR,
            $linkType['popup_options'],
            'popup_location'
          );
          $options = $this->setLinkPopupOption(
            $options,
            PapayaUiLinkAttributes::OPTION_STATUSBAR,
            $linkType['popup_options'],
            'popup_status'
          );
          $scrollbarOptions = array(
            0 => PapayaUiLinkAttributes::OPTION_SCROLLBARS_NEVER,
            1 => PapayaUiLinkAttributes::OPTION_SCROLLBARS_ALWAYS,
            2 => PapayaUiLinkAttributes::OPTION_SCROLLBARS_AUTO
          );
          $scrollbarOptionIndex = PapayaUtilArray::get(
            $linkType['popup_options'], 'popup_scrollbars', 2
          );
          if (!empty($scrollbarOptions[$scrollbarOptionIndex])) {
            $options |= $scrollbarOptions[$scrollbarOptionIndex];
          }
          $attributes->setPopup($linkType['target'], $width, $height, $top, $left, $options);
        } else {
          $attributes->target = $linkType['target'];
        }
        return $attributes;
      }
    }
    return NULL;
  }

  /**
  * set a bit in the options bitmask, depending on a true/false option value
  *
  * @param integer $bitmask
  * @param integer $bit
  * @param array $options
  * @param string $name
  * @param boolean $default
   * @return int
   */
  private function setLinkPopupOption($bitmask, $bit, $options, $name, $default = FALSE) {
    if (PapayaUtilArray::get($options, $name, $default)) {
      $bitmask |= $bit;
    }
    return $bitmask;
  }

  /**
  * The pages subobject is used to load the acutal page data
  *
  * @param PapayaContentPages $pages
  * @return PapayaContentPages
  */
  public function pages(PapayaContentPages $pages = NULL) {
    if (isset($pages)) {
      $this->_pages = $pages;
    } elseif (is_null($this->_pages)) {
      $this->_pages = $this->isPreview()
        ? new PapayaContentPages(TRUE) : new PapayaContentPagesPublications(TRUE);
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }

  /**
  * Access to the link types
  *
  * @param PapayaContentLinkTypes $linkTypes
  * @return PapayaContentLinkTypes
  */
  public function linkTypes(PapayaContentLinkTypes $linkTypes = NULL) {
    if (isset($linkTypes)) {
      $this->_linkTypes = $linkTypes;
    } elseif (is_null($this->_linkTypes)) {
      $this->_linkTypes = new PapayaContentLinkTypes();
      $this->_linkTypes->papaya($this->papaya());
      $this->_linkTypes->activateLazyLoad();
    }
    return $this->_linkTypes;
  }

  /**
  * The domains subobject is used to load get domain data for the page id
  *
  * @param PapayaDomains $domains
  * @return PapayaDomains
  */
  public function domains(PapayaDomains $domains = NULL) {
    if (isset($domains)) {
      $this->_domains = $domains;
    } elseif (is_null($this->_domains)) {
      $this->_domains = new PapayaDomains();
      $this->_domains->papaya($this->papaya());
    }
    return $this->_domains;
  }

  /**
  * Getter/Setter for a content languages record list.
  *
  * @param PapayaContentLanguages $languages
  * @return PapayaContentLanguages
  */
  public function languages(PapayaContentLanguages $languages = NULL) {
    if (isset($languages)) {
      $this->_languages = $languages;
    } elseif (is_null($this->_languages)) {
      $this->_languages = $this->papaya()->languages;
    }
    return $this->_languages;
  }

  /**
  * Validate language identifer. If the given language identifer is not valid, try to get it from
  * the request object and if that is empty from the option. Store it in a member variable
  * for a repeated call.
  *
  * @param string $languageIdentifier
  * @return string
  */
  public function validateLanguageIdentifier($languageIdentifier) {
    $language = NULL;
    $languages = $this->languages();
    if (preg_match('(^[a-z]{1,6}$)', $languageIdentifier)) {
      $language = $languages->getLanguageByIdentifier($languageIdentifier);
    }
    if (!$language) {
      if (isset($this->_currentLanguage)) {
        return $this->_currentLanguage->identifier;
      }
      $language = $languages->getLanguageByIdentifier(
        $this->papaya()->request->getParameter(
          'language', '', NULL, PapayaRequest::SOURCE_PATH
        )
      );
      if (!$language) {
        $language = $languages->getLanguage(
          $this->papaya()->options->get('PAPAYA_CONTENT_LANGUAGE', 1)
        );
      }
      $this->_currentLanguage = $language;
    }
    return $language ? $language->identifier : '';
  }

  /**
  * Preload page data for a given list of page ids
  *
  * @param string|integer $language Identifier or id
  * @param array $pageIds
  */
  public function preload($language, array $pageIds) {
    if (is_int($language)) {
      $language = $this->languages()->getLanguage($language);
    } else {
      $language = $this->languages()->getLanguageByIdentifier(
        $this->validateLanguageIdentifier($language)
      );
    }
    if ($language) {
      if (isset($this->_pageData[$language->identifier])) {
        $pageIds = array_values(
          array_diff($pageIds, array_keys($this->_pageData[$language->identifier]))
        );
      }
      $pages = $this->pages();
      if (!empty($pageIds) &&
          $pages->load($this->getFilter($pageIds, $language->id))) {
        foreach ($pageIds as $pageId) {
          $this->_pageData[$language->identifier][$pageId] =
            isset($pages[$pageId]) ? $pages[$pageId] : FALSE;
        }
      }
    }
  }

  /**
   * Get a filter array for page ids (include current time if it is not preview)
   *
   * @param array|int $pageId
   * @param $languageId
   * @return array
   */
  private function getFilter($pageId, $languageId) {
    $filter = array(
      'id' => $pageId,
      'language_id' => $languageId
    );
    if (!$this->isPreview()) {
      $filter['time'] = time();
    }
    return $filter;
  }

  /**
   * Set start page reference
   * @param PapayaUiReferencePage $page
   * @return boolean
   */
  public function isStartPage(PapayaUiReferencePage $page) {
    return $this->domains()->isStartPage($page);
  }
}