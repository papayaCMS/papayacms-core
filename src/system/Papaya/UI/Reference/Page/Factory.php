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

use Papaya\Application;
use Papaya\Content;
use Papaya\Domains;
use Papaya\UI;
use Papaya\Utility;

/**
 * A application width object that provides data for references
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Factory implements Application\Access {
  use Application\Access\Aggregation;

  /**
   * @var array
   */
  private $_pageData = [];

  /**
   * @var \Papaya\Content\Language
   */
  private $_currentLanguage;

  /**
   * @var bool
   */
  private $_preview = FALSE;

  /**
   * @var Content\Pages
   */
  private $_pages;

  /**
   * @var Content\Languages
   */
  private $_languages;

  /**
   * @var Domains
   */
  private $_domains;

  /**
   * @var Content\Link\Types
   */
  private $_linkTypes;

  /**
   * Create a page reference
   *
   * @return UI\Reference\Page
   */
  public function create() {
    return new UI\Reference\Page();
  }

  /**
   * Create and configure a page reference
   *
   * @param string $languageIdentifier
   * @param int $pageId
   *
   * @return UI\Reference\Page
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
   * @param UI\Reference\Page $reference
   *
   * @return UI\Reference\Page
   */
  public function configure(UI\Reference\Page $reference) {
    $languageIdentifier = $this->validateLanguageIdentifier($reference->getPageLanguage());
    $pageId = $reference->getPageId();
    $reference->setPreview($this->isPreview());
    if ($pageId > 0) {
      if ($data = $this->getPageData($languageIdentifier, $pageId)) {
        $reference->setPageLanguage($languageIdentifier, FALSE);
        $reference->setPageTitle($this->prepareTitle($data['title'], $languageIdentifier));
        if ($this->isPreview()) {
          return $reference;
        }
        if (\is_array($domain = $this->getDomainData($languageIdentifier, $pageId))) {
          $reference->url()->setHost($domain['host']);
          if (Utility\Server\Protocol::BOTH !== $domain['scheme']) {
            $reference->url()->setScheme(
              Utility\Server\Protocol::get($domain['scheme'])
            );
          } elseif (!$reference->url()->getScheme()) {
            $reference->url()->setScheme('http');
          }
        } elseif (!$domain) {
          $reference->valid(FALSE);
        }
        if (Utility\Server\Protocol::BOTH !== $data['scheme']) {
          $reference->url()->setScheme(
            Utility\Server\Protocol::get($data['scheme'])
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
   *
   * @return string
   */
  private function prepareTitle($title, $languageIdentifier) {
    return \strtolower(
      Utility\File::normalizeName(
        $title,
        $this->papaya()->options->get('PAPAYA_URL_NAMELENGTH', 50),
        $languageIdentifier
      )
    );
  }

  /**
   * Check if the object/cms is in preview mode or not
   *
   * @return bool
   */
  public function isPreview() {
    return $this->_preview;
  }

  /**
   * Set the preview stat, unset the pages subobject if needed.
   *
   * @param bool $preview
   */
  public function setPreview($preview) {
    if ($this->_preview !== (bool)$preview) {
      $this->_preview = (bool)$preview;
      $this->_pages = NULL;
    }
  }

  /**
   * Get the page data
   *
   * @param string $languageIdentifier
   * @param int $pageId
   *
   * @return array|false
   */
  public function getPageData($languageIdentifier, $pageId) {
    $languageIdentifier = $this->validateLanguageIdentifier($languageIdentifier);
    $this->lazyLoadPage($languageIdentifier, $pageId);
    if (
      isset($this->_pageData[$languageIdentifier][$pageId]) &&
      \is_array($this->_pageData[$languageIdentifier][$pageId]) &&
      $this->isPageLoaded($languageIdentifier, $pageId)
    ) {
      return $this->_pageData[$languageIdentifier][$pageId];
    }
    return FALSE;
  }

  /**
   * Lazy load the data for given page (translation)
   *
   * @param string $languageIdentifier
   * @param int $pageId
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
   * @param int $pageId
   *
   * @return bool
   */
  private function isPageLoaded($languageIdentifier, $pageId) {
    return isset($this->_pageData[$languageIdentifier][$pageId]) ? TRUE : FALSE;
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
   * @param int $pageId
   *
   * @return array|bool
   */
  public function getDomainData($languageIdentifier, $pageId) {
    $result = FALSE;
    if ($pageData = $this->getPageData($languageIdentifier, $pageId)) {
      if (isset($pageData['domain'])) {
        $result = $pageData['domain'];
      } else {
        $path = $pageData['path'];
        \array_push($path, $pageData['parent'], $pageData['id']);
        $domains = $this->domains()->getDomainsByPath($path);
        $current = $this->domains()->getCurrent();
        $language = $this->languages()->getLanguage(
          $languageIdentifier, Content\Languages::FILTER_IS_CONTENT
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
              if ((int)$domain['group_id'] === (int)$current['group_id']) {
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
   *
   * @return bool
   */
  public function isDomainWithoutWildcards($domain) {
    return FALSE === \strpos($domain['host'], '*');
  }

  private function isDomainWithLanguage($domain, $languageId) {
    return (
      0 === (int)$domain['language_id'] ||
      (int)$domain['language_id'] === (int)$languageId
    );
  }

  /**
   * Get link attributes object for the given page
   *
   * @param string $languageIdentifier
   * @param int $pageId
   *
   * @return null|UI\Link\Attributes
   */
  public function getLinkAttributes($languageIdentifier, $pageId) {
    if ($pageData = $this->getPageData($languageIdentifier, $pageId)) {
      $linkTypes = $this->linkTypes();
      if (isset($linkTypes[$pageData['linktype_id']])) {
        $linkType = $linkTypes[$pageData['linktype_id']];
        $attributes = new UI\Link\Attributes();
        $attributes->class = $linkType['class'];
        if ($linkType['is_popup']) {
          $width = Utility\Arrays::get($linkType['popup_options'], 'popup_width', '80%');
          $height = Utility\Arrays::get($linkType['popup_options'], 'popup_height', '80%');
          $top = Utility\Arrays::get($linkType['popup_options'], 'popup_top', NULL);
          $left = Utility\Arrays::get($linkType['popup_options'], 'popup_left', NULL);
          $options = 0;
          $options = $this->setLinkPopupOption(
            $options,
            UI\Link\Attributes::OPTION_RESIZEABLE,
            $linkType['popup_options'],
            'popup_resizable'
          );
          $options = $this->setLinkPopupOption(
            $options,
            UI\Link\Attributes::OPTION_TOOLBAR,
            $linkType['popup_options'],
            'popup_toolbar'
          );
          $options = $this->setLinkPopupOption(
            $options,
            UI\Link\Attributes::OPTION_MENUBAR,
            $linkType['popup_options'],
            'popup_menubar'
          );
          $options = $this->setLinkPopupOption(
            $options,
            UI\Link\Attributes::OPTION_LOCATIONBAR,
            $linkType['popup_options'],
            'popup_location'
          );
          $options = $this->setLinkPopupOption(
            $options,
            UI\Link\Attributes::OPTION_STATUSBAR,
            $linkType['popup_options'],
            'popup_status'
          );
          $scrollbarOptions = [
            0 => UI\Link\Attributes::OPTION_SCROLLBARS_NEVER,
            1 => UI\Link\Attributes::OPTION_SCROLLBARS_ALWAYS,
            2 => UI\Link\Attributes::OPTION_SCROLLBARS_AUTO
          ];
          $scrollbarOptionIndex = Utility\Arrays::get(
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
   * @param int $bitmask
   * @param int $bit
   * @param array $options
   * @param string $name
   * @param bool $default
   *
   * @return int
   */
  private function setLinkPopupOption($bitmask, $bit, $options, $name, $default = FALSE) {
    if (Utility\Arrays::get($options, $name, $default)) {
      $bitmask |= $bit;
    }
    return $bitmask;
  }

  /**
   * The pages subobject is used to load the acutal page data
   *
   * @param Content\Pages $pages
   *
   * @return Content\Pages
   */
  public function pages(Content\Pages $pages = NULL) {
    if (NULL !== $pages) {
      $this->_pages = $pages;
    } elseif (NULL === $this->_pages) {
      $this->_pages = $this->isPreview()
        ? new Content\Pages(TRUE) : new Content\Page\Publications(TRUE);
      $this->_pages->papaya($this->papaya());
    }
    return $this->_pages;
  }

  /**
   * Access to the link types
   *
   * @param Content\Link\Types $linkTypes
   *
   * @return Content\Link\Types
   */
  public function linkTypes(Content\Link\Types $linkTypes = NULL) {
    if (NULL !== $linkTypes) {
      $this->_linkTypes = $linkTypes;
    } elseif (NULL === $this->_linkTypes) {
      $this->_linkTypes = new Content\Link\Types();
      $this->_linkTypes->papaya($this->papaya());
      $this->_linkTypes->activateLazyLoad();
    }
    return $this->_linkTypes;
  }

  /**
   * The domains subobject is used to load get domain data for the page id
   *
   * @param Domains $domains
   *
   * @return Domains
   */
  public function domains(Domains $domains = NULL) {
    if (NULL !== $domains) {
      $this->_domains = $domains;
    } elseif (NULL === $this->_domains) {
      $this->_domains = new Domains();
      $this->_domains->papaya($this->papaya());
    }
    return $this->_domains;
  }

  /**
   * Getter/Setter for a content languages record list.
   *
   * @param Content\Languages $languages
   *
   * @return Content\Languages
   */
  public function languages(Content\Languages $languages = NULL) {
    if (NULL !== $languages) {
      $this->_languages = $languages;
    } elseif (NULL === $this->_languages) {
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
   *
   * @return string
   */
  public function validateLanguageIdentifier($languageIdentifier) {
    $language = NULL;
    $languages = $this->languages();
    if (\preg_match('(^[a-z]{1,6}$)', $languageIdentifier)) {
      $language = $languages->getLanguageByIdentifier($languageIdentifier);
    }
    if (!($language && $language->isContent)) {
      if (NULL !== $this->_currentLanguage) {
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
          Content\Languages::FILTER_IS_CONTENT
        );
      }
      $this->_currentLanguage = $language;
    }
    return $language ? $language->identifier : '';
  }

  /**
   * Preload page data for a given list of page ids
   *
   * @param string|int $languageIdentifier or id
   * @param array $pageIds
   */
  public function preload($languageIdentifier, array $pageIds) {
    $language = $this->languages()->getLanguage($languageIdentifier, Content\Languages::FILTER_IS_CONTENT);
    if ($language) {
      if (isset($this->_pageData[$language->identifier])) {
        $pageIds = \array_values(
          \array_diff($pageIds, \array_keys($this->_pageData[$language->identifier]))
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
   *
   * @return array
   */
  private function getFilter($pageId, $languageId) {
    $filter = [
      'id' => $pageId,
      'language_id' => $languageId
    ];
    if (!$this->isPreview()) {
      $filter['time'] = \time();
    }
    return $filter;
  }

  /**
   * Set start page reference
   *
   * @param UI\Reference\Page $page
   *
   * @return bool
   */
  public function isStartPage(UI\Reference\Page $page) {
    return $this->domains()->isStartPage($page);
  }
}
