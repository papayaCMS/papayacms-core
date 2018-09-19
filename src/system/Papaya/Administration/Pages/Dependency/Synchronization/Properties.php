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
namespace Papaya\Administration\Pages\Dependency\Synchronization;

use Papaya\Content\Page;

/**
 * Synchronize properties of the page working copy
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Properties
  extends Content {
  /**
   * Page database record object
   *
   * @var Page\Work
   */
  private $_page;

  /**
   * Synchronize a dependency
   *
   * @param array $targetIds
   * @param int $originId
   * @param array|null $languages
   *
   * @return bool
   */
  public function synchronize(array $targetIds, $originId, array $languages = NULL) {
    $result = parent::synchronize($targetIds, $originId, $languages);
    if ($result && $this->page()->load($originId)) {
      return $this->updatePages($this->page(), $targetIds);
    }
    return FALSE;
  }

  /**
   * Getter/Setter for the content page object
   *
   * @param Page\Work $page
   *
   * @return Page\Work
   */
  public function page(Page\Work $page = NULL) {
    if (NULL !== $page) {
      $this->_page = $page;
    } elseif (NULL === $this->_page) {
      $this->_page = new Page\Work();
    }
    return $this->_page;
  }

  /**
   * Update target translation properties
   *
   * @param Page\Translation $origin
   * @param array $targetIds
   *
   * @return bool
   */
  protected function updateTranslations(Page\Translation $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
        $databaseAccess->getTableName(\Papaya\Content\Tables::PAGE_TRANSLATIONS),
        [
          'topic_title' => $origin->title,
          'meta_title' => $origin->metaTitle,
          'meta_keywords' => $origin->metaKeywords,
          'meta_descr' => $origin->metaDescription
        ],
        [
          'lng_id' => $origin->languageId,
          'topic_id' => $targetIds
        ]
      );
  }

  /**
   * Update target page properties
   *
   * @param Page\Work $origin
   * @param array $targetIds
   *
   * @return bool
   */
  protected function updatePages(Page\Work $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
        $databaseAccess->getTableName(\Papaya\Content\Tables::PAGES),
        [
          'topic_modified' => $databaseAccess->getTimestamp(),
          'topic_mainlanguage' => $origin->defaultLanguage,
          'linktype_id' => $origin->linkType,
          'topic_changefreq' => $origin->changeFrequency,
          'topic_priority' => $origin->priority,
          'topic_protocol' => $origin->scheme,
          'topic_cachemode' => $origin->cacheMode,
          'topic_cachetime' => $origin->cacheTime,
          'topic_expiresmode' => $origin->expiresMode,
          'topic_expirestime' => $origin->expiresTime
        ],
        [
          'topic_id' => $targetIds
        ]
      );
  }
}
