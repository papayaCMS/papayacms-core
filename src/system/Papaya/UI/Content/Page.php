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
namespace Papaya\UI\Content;

use Papaya\Application;
use Papaya\BaseObject\Parameters;
use Papaya\Content;
use Papaya\Plugin;
use Papaya\Request;
use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;
use Papaya\XML\Document as XMLDocument;

class Page implements Application\Access {
  use Application\Access\Aggregation;

  /**
   * @var \Papaya\Content\Page
   */
  private $_page;

  /**
   * @var Content\Page\Translation
   */
  private $_translation;

  /**
   * @var int
   */
  private $_pageId;

  /**
   * @var int|Content\Language|string
   */
  private $_language;

  /**
   * @var bool
   */
  private $_isPublic;

  /**
   * @var UI\Reference\Page
   */
  private $_reference;

  /**
   * @param int $pageId
   * @param int|string|Content\Language $language
   * @param bool $isPublic
   */
  public function __construct($pageId, $language, $isPublic = TRUE) {
    $this->_pageId = (int)$pageId;
    $this->_language = $language;
    $this->_isPublic = (bool)$isPublic;
  }

  /**
   * @param array|\Traversable $data
   */
  public function assign($data) {
    Utility\Constraints::assertArrayOrTraversable($data);
    $this->page()->assign($data);
    $this->translation()->assign($data);
  }

  /**
   * @param \Papaya\Content\Page $page
   *
   * @return \Papaya\Content\Page|Content\Page\Publication
   */
  public function page(Content\Page $page = NULL) {
    if (NULL !== $page) {
      $this->_page = $page;
    } elseif (NULL === $this->_page) {
      $this->_page = $this->isPublic() ? new Content\Page\Publication() : new Content\Page();
      $this->_page->activateLazyLoad($this->_pageId);
    }
    return $this->_page;
  }

  /**
   * @param Content\Page\Translation $translation
   *
   * @return Content\Page\Publication\Translation|Content\Page\Translation
   */
  public function translation(Content\Page\Translation $translation = NULL) {
    if (NULL !== $translation) {
      $this->_translation = $translation;
    } elseif (NULL === $this->_translation) {
      $this->_translation = $this->isPublic() ? new Content\Page\Publication\Translation()
        : new Content\Page\Translation();
      if ($language = $this->getPageLanguage()) {
        $this->_translation->activateLazyLoad(
          ['id' => $this->_pageId, 'language_id' => $language['id']]
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
   * @return null|Content\Language
   */
  public function getPageLanguage() {
    if ($this->_language instanceof Content\Language) {
      return $this->_language;
    }
    if (NULL !== $this->_language && isset($this->papaya()->languages)) {
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
   * @param XML\Element $parent
   * @param array|Parameters $configuration
   * @param array $viewData
   */
  public function appendQuoteTo(XML\Element $parent, $configuration = [], array $viewData = NULL) {
    $moduleGuid = $this->translation()->moduleGuid;
    if (!empty($moduleGuid)) {
      $plugin = $this->papaya()->plugins->get($moduleGuid, $this, $this->translation()->content);
      if ($plugin) {
        $teaser = $parent->appendElement(
          'teaser',
          [
            'page-id' => $this->getPageId(),
            'plugin-guid' => $moduleGuid,
            'plugin' => \get_class($plugin),
            'view' => $this->translation()->viewName,
            'href' => $this->getPageHref($plugin, $configuration, $viewData),
            'published' => Utility\Date::timestampToString($this->translation()->modified),
            'created' => Utility\Date::timestampToString($this->translation()->created)
          ]
        );
        if ($plugin instanceof Plugin\Quoteable) {
          if ($plugin instanceof Plugin\Configurable\Context) {
            $plugin->configuration()->merge($configuration);
          }
          $plugin->appendQuoteTo($teaser);
        } elseif ($plugin instanceof \base_content &&
          \method_exists($plugin, 'getParsedTeaser')) {
          $teaser->appendXML((string)$plugin->getParsedTeaser((array)$configuration));
        }
        /** @var XMLDocument $document */
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
   * @param UI\Reference\Page $reference
   *
   * @return UI\Reference\Page
   */
  public function reference(UI\Reference\Page $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new UI\Reference\Page();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }

  /**
   * @param object $plugin
   * @param array|\ArrayAccess $configuration
   * @param array|null $viewData
   *
   * @return string
   */
  private function getPageHref($plugin, $configuration, array $viewData = NULL) {
    $href = '';
    if ($viewData) {
      $reference = clone $this->reference();
      $reference->setPageId($this->getPageId(), TRUE);
      if (isset($configuration['query_string'])) {
        $reference->setParameters(
          Request\Parameters::createFromString($configuration['query_string'])
        );
      }
      $href = $reference->get();
      $validatedHref = FALSE;
      if ($plugin instanceof Plugin\Addressable) {
        $request = new Request($this->papaya()->options);
        $request->load($reference->url());
        $validatedHref = $plugin->validateURL($request);
      }
      if (\is_string($validatedHref) && ('' !== $validatedHref)) {
        $href = $validatedHref;
      }
    }
    return $href;
  }
}
