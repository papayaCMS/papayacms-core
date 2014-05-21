<?php
/**
* Synchronize properties of the page working copy
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Administration
* @version $Id: Properties.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Synchronize properties of the page working copy
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesDependencySynchronizationProperties
  extends PapayaAdministrationPagesDependencySynchronizationContent {

  /**
  * Page database record object
  *
  * @var PapayaContentPageWork
  */
  private $_page = NULL;

  /**
   * Synchronize a dependency
   *
   * @param array $targetIds
   * @param integer $originId
   * @param array|NULL $languages
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
  * @param PapayaContentPageWork $page
  * @return PapayaContentPageWork
  */
  public function page(PapayaContentPageWork $page = NULL) {
    if (isset($page)) {
      $this->_page = $page;
    } elseif (is_null($this->_page)) {
      $this->_page = new PapayaContentPageWork();
    }
    return $this->_page;
  }

  /**
  * Update target translation properties
  *
  * @param PapayaContentPageTranslation $origin
  * @param array $targetIds
  * @return boolean
  */
  protected function updateTranslations(PapayaContentPageTranslation $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
      $databaseAccess->getTableName(PapayaContentTables::PAGE_TRANSLATIONS),
      array(
        'topic_title' => $origin->title,
        'meta_title' => $origin->metaTitle,
        'meta_keywords' => $origin->metaKeywords,
        'meta_descr' => $origin->metaDescription
      ),
      array(
        'lng_id' => $origin->languageId,
        'topic_id' => $targetIds
      )
    );
  }

  /**
  * Update target page properties
  *
  * @param PapayaContentPageWork $origin
  * @param array $targetIds
  * @return boolean
  */
  protected function updatePages(PapayaContentPageWork $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
      $databaseAccess->getTableName(PapayaContentTables::PAGES),
      array(
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
      ),
      array(
        'topic_id' => $targetIds
      )
    );
  }

}