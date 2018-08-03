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

namespace Papaya\UI\Reference\Page;

/**
 * A application width object that provides data for references
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Factory extends \Papaya\Application\BaseObject {

  /**
   * @var array
   */
  private $_pageData = array();

  /**
   * @var \Papaya\Content\Language
   */
  private $_currentLanguage = NULL;

  /**
   * @var bool
   */
  private $_preview = FALSE;

  /**
   * @var \Papaya\Content\Pages
   */
  private $_pages = NULL;

  /**
   * @var \Papaya\Content\Languages
   */
  private $_languages = NULL;

  /**
   * @var \Papaya\Domains
   */
  private $_domains = NULL;

  /**
   * @var \Papaya\Content\Link\Types
   */
  private $_linkTypes = NULL;

  /**
   * Create a page reference
   *
   * @return \Papaya\UI\Reference\Page
   */
  public function create() {
    return new \Papaya\UI\Reference\Page();
  }

  /**
   * Create and configure a page reference
   *
   * @param string $languageIdentifier
   * @param integer $pageId
   * @return \Papaya\UI\Reference\Page
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
   * @param \Papaya\UI\Reference\Page $reference
   * @return \Papaya\UI\Reference\Page
   */
  public function configure(\Papaya\UI\Reference\Page $reference) {
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
          if ($domain['scheme'] != \Papaya\Utility\Server\Protocol::BOTH) {
            $reference->url()->setScheme(
              \Papaya\Utility\Server\Protocol::get($domain['scheme'])
            );
          } elseif (!$reference->url()->getScheme()) {
            $reference->url()->setScheme('http');
          }
        } elseif (!$domain) {
          $reference->valid(FALSE);
        }
        if ($data['scheme'] != \Papaya\Utility\Server\Protocol::BOTH) {
          $reference->url()->setScheme(
            \Papaya\Utility\Server\Protocol::get($data['scheme'])
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
      \Papaya\Utility\File::normalizeName(
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
    if (
      $this->isPageLoaded($languageIdentifier, $pageId) &&
      is_array($this->_pageData[$languageIdentifier][$pageId])
    ) {
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
      if (
        $language &&
        $pages->load($this->getFilter($pageId, $language->id))
      ) {
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
        $language = $this->languages()->getLanguage(
          $languageIdentifier, \Papaya\Content\Languages::FILTER_IS_CONTENT
        );
        $languageId = $language ? $language->id : 0;
        if ($current &&
          isset($domains[$current['id']]) &&
          $this->isDomainWithLanguage($current, $languageId)) {
          // given page is accessible on current domain
          $result = TRUE;
        } else {
          // return the first domain that can show this page in the requested language
          foreach ($domains as $domain) {
            if (
              $this->isDomainWithLanguage($domain, $languageId) &&
              $this->isDomainWithoutWildcards($domain)
            ) {
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
   * @return NULL|\Papaya\UI\Link\Attributes
   */
  public function getLinkAttributes($languageIdentifier, $pageId) {
    if ($pageData = $this->getPageData($languageIdentifier, $pageId)) {
      $linkTypes = $this->linkTypes();
      if (isset($linkTypes[$pageData['linktype_id']])) {
        $linkType = $linkTypes[$pageData['linktype_id']];
        $attributes = new \Papaya\UI\Link\Attributes();
        $attributes->class = $linkType['class'];
        if ($linkType['is_popup']) {
          $width = \Papaya\Utility\Arrays::get($linkType['popup_options'], 'popup_width', '80%');
          $height = \Papaya\Utility\Arrays::get($linkType['popup_options'], 'popup_height', '80%');
          $top = \Papaya\Utility\Arrays::get($linkType['popup_options'], 'popup_top', NULL);
          $left = \Papaya\Utility\Arrays::get($linkType['popup_options'], 'popup_left', NULL);
          $options = 0;
          $options = $this->setLinkPopupOption(
            $options,
            \Papaya\UI\Link\Attributes::OPTION_RESIZEABLE,
            $linkType['popup_options'],
            'popup_resizable'
          );
          $options = $this->setLinkPopupOption(
            $options,
            \Papaya\UI\Link\Attributes::OPTION_TOOLBAR,
            $linkType['popup_options'],
            'popup_toolbar'
          );
          $options = $this->setLinkPopupOption(
            $options,
            \Papaya\UI\Link\Attributes::OPTION_MENUBAR,
            $linkType['popup_options'],
            'popup_menubar'
          );
          $options = $this->setLinkPopupOption(
            $options,
            \Papaya\UI\Link\Attributes::OPTION_LOCATIONBAR,
            $linkType['popup_options'],
            'popup_location'
          );
          $options = $this->setLinkPopupOption(
            $options,
            \Papaya\UI\Link\Attributes::OPTION_STATUSBAR,
            $linkType['popup_options'],
            'popup_status'
          );
          $scrollbarOptions = array(
            0 => \Papaya\UI\Link\Attributes::OPTION_SCROLLBARS_NEVER,
            1 => \Papaya\UI\Link\Attributes::OPTION_SCROLLBARS_ALWAYS,
            2 => \Papaya\UI\Link\Attributes::OPTION_SCROLLBARS_AUTO
          );
          $scrollbarOptionIndex = \Papaya\Utility\Arrays::get(
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
    if (\Papaya\Utility\Arrays::get($options, $name, $default)) {
      $bitmask |= $bit;
    }
    return $bitmask;
  }

  /**
   * The pages subobject is used to load the acutal page data
   *
   * @param \Papaya\Content\Pages $pages
   * @return \Papaya\Content\Pages
   */
  public function pages(\Papaya\Content\Pages $pages = NULL) {
    if (isset($pages)) {
      $this->_pages = $pages;
    } elseif (is_null($this->_pages)) {
      $this->_pages = $this->isPreview()
        ? new \Papaya\Content\Pages(TRUE) : new \Papaya\Content\Pages\Publications(TRUE);
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }

  /**
   * Access to the link types
   *
   * @param \Papaya\Content\Link\Types $linkTypes
   * @return \Papaya\Content\Link\Types
   */
  public function linkTypes(\Papaya\Content\Link\Types $linkTypes = NULL) {
    if (isset($linkTypes)) {
      $this->_linkTypes = $linkTypes;
    } elseif (is_null($this->_linkTypes)) {
      $this->_linkTypes = new \Papaya\Content\Link\Types();
      $this->_linkTypes->papaya($this->papaya());
      $this->_linkTypes->activateLazyLoad();
    }
    return $this->_linkTypes;
  }

  /**
   * The domains subobject is used to load get domain data for the page id
   *
   * @param \Papaya\Domains $domains
   * @return \Papaya\Domains
   */
  public function domains(\Papaya\Domains $domains = NULL) {
    if (isset($domains)) {
      $this->_domains = $domains;
    } elseif (is_null($this->_domains)) {
      $this->_domains = new \Papaya\Domains();
      $this->_domains->papaya($this->papaya());
    }
    return $this->_domains;
  }

  /**
   * Getter/Setter for a content languages record list.
   *
   * @param \Papaya\Content\Languages $languages
   * @return \Papaya\Content\Languages
   */
  public function languages(\Papaya\Content\Languages $languages = NULL) {
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
    if (!($language && $language->isContent)) {
      if (isset($this->_currentLanguage)) {
        return $this->_currentLanguage->identifier;
      }
      $language = $languages->getLanguageByIdentifier(
        $this->papaya()->request->getParameter(
          'language', '', NULL, \Papaya\Request::SOURCE_PATH
        )
      );
      if (!($language && $language->isContent)) {
        $language = $languages->getLanguage(
          $this->papaya()->options->get('PAPAYA_CONTENT_LANGUAGE', 1),
          \Papaya\Content\Languages::FILTER_IS_CONTENT
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
    $language = $this->languages()->getLanguage($language, \Papaya\Content\Languages::FILTER_IS_CONTENT);
    if ($language) {
      if (isset($this->_pageData[$language->identifier])) {
        $pageIds = array_values(
          array_diff($pageIds, array_keys($this->_pageData[$language->identifier]))
        );
      }
      $pages = $this->pages();
      if (
        !empty($pageIds) &&
        $pages->load($this->getFilter($pageIds, $language->id))
      ) {
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
   *
   * @param \Papaya\UI\Reference\Page $page
   * @return boolean
   */
  public function isStartPage(\Papaya\UI\Reference\Page $page) {
    return $this->domains()->isStartPage($page);
  }
}
