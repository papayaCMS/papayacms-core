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
namespace Papaya\Content\Page;

use Papaya\Content;
use Papaya\Database;
use Papaya\Utility;

/**
 * Provide data encapsulation for the working copy of content page.
 *
 * Allows to edit the pages. It contains no validation, only the database access
 * encapsulation.
 *
 * @property int $id page id
 * @property int $parentId direct page parent/ancestor id,
 * @property array $parentPath all page ancestor ids,
 * @property string $owner administration user that own this page
 * @property int $group administration user group that own this page
 * @property string $permissions administration permissions,
 * @property int $inheritVisitorPermissions inherit visitor permisssion from anchestors (mode)
 * @property array $visitorPermissions visitor permission for this node
 * @property int $created page creation timestamp
 * @property int $modified last modification timestamp
 * @property int $position page position relative to its siblings
 * @property bool $inheritBoxes box inheritance
 * @property int $defaultLanguage default/fallback language,
 * @property int $linkType page link type for navigations,
 * @property bool $inheritMetaInfo inherit meta informations like page title and keywords,
 * @property int $changeFrequency change frequency (for search engines)
 * @property int $priority content priority (for search engines)
 * @property int $scheme page scheme (http, https or both)
 * @property int $cacheMode page content cache mode (system, none, own)
 * @property int $cacheTime page content cache time, if mode == own
 * @property int $expiresMode page browser cache mode (system, none, own)
 * @property int $expiresTime page browser cache time, if mode == own
 * @property int $unpublishedTranslations internal counter for unpublished translations
 */
class Work extends Content\Page {
  /**
   * Create child page object (but do not save it yet)
   *
   * To create an child page for an existing page you call:
   *
   * <code>
   * $parentPage = new \Papaya\Content\Page\Work();
   * $parentPage->load($parentId);
   * $childPage = $parentPage->createChild();
   * ...
   * $childPage->save();
   * </code>
   *
   * @return self
   */
  public function createChild() {
    $child = new self();
    $child->parentId = $this->id;
    $parentPath = $this->parentPath;
    $parentPath[] = $this->id;
    $child->assign(
      [
        'parent_path' => $parentPath,
        'owner' => $this->owner,
        'group' => $this->group,
        'permissions' => $this->permissions,
        'inherit_visitor_permissions' => Content\Options::INHERIT_PERMISSIONS_PARENT,
        'visitor_permissions' => [],
        'position' => 999999,
        'inherit_boxes' => TRUE,
        'default_language' => $this->defaultLanguage,
        'link_type' => 1,
        'is_deleted' => FALSE,
        'inherit_meta_information' => TRUE,
        'change_frequency' => $this->changeFrequency,
        'priority' => $this->priority,
        'scheme' => $this->scheme,
        'cache_mode' => $this->cacheMode,
        'cache_time' => $this->cacheTime,
        'expires_mode' => $this->expiresMode,
        'expires_time' => $this->expiresTime,
        'unpublished_translations' => 0
      ]
    );
    return $child;
  }

  /**
   * Get a publication encapsulation object
   *
   * @return Publication
   */
  protected function _createPublicationObject() {
    $publication = new Publication();
    $publication->setDatabaseAccess($this->getDatabaseAccess());
    return $publication;
  }

  /**
   * Publish the currently loaded page data and the defined translations.
   *
   * @param array $languageIds
   * @param int $publishedFrom
   * @param int $publishedTo
   *
   * @return bool
   */
  public function publish(array $languageIds = NULL, $publishedFrom = 0, $publishedTo = 0) {
    if ($this->id > 0) {
      $publication = $this->_createPublicationObject();
      $publication->assign($this);
      $publication->publishedFrom = $publishedFrom;
      $publication->publishedTo = $publishedTo;
      if ($publication->save()) {
        return $this->_publishTranslations($publication, $languageIds);
      }
    }
    return FALSE;
  }

  /**
   * Publish the translations of the given languages.
   *
   * @param Publication $publication
   * @param array $languageIds
   *
   * @return bool
   */
  private function _publishTranslations(
    Publication $publication, array $languageIds = NULL
  ) {
    $databaseAccess = $this->getDatabaseAccess();
    if (!empty($languageIds)) {
      $deleted = $databaseAccess->deleteRecord(
        $databaseAccess->getTableName(Content\Tables::PAGE_PUBLICATION_TRANSLATIONS),
        [
          'topic_id' => $this->id,
          'lng_id' => $languageIds
        ]
      );
      if (FALSE !== $deleted) {
        $filter = \str_replace('%', '%%', $databaseAccess->getSqlCondition(['lng_id' => $languageIds]));
        $now = \time();
        $sql = "INSERT INTO %s
                       (topic_id, lng_id, topic_title,
                        topic_content, author_id, view_id,
                        topic_trans_created, topic_trans_modified,
                        topic_trans_checked,
                        meta_title, meta_keywords, meta_descr)
                SELECT t.topic_id, t.lng_id, t.topic_title, t.topic_content,
                       t.author_id, t.view_id,
                       t.topic_trans_created, '%d', '%d',
                       t.meta_title, t.meta_keywords, t.meta_descr
                  FROM %s t
                 WHERE t.topic_id = %d AND $filter";
        $parameters = [
          $databaseAccess->getTableName(Content\Tables::PAGE_PUBLICATION_TRANSLATIONS),
          $now,
          $now,
          $databaseAccess->getTableName(Content\Tables::PAGE_TRANSLATIONS),
          $this->id
        ];
        if (FALSE !== $databaseAccess->queryFmtWrite($sql, $parameters)) {
          $publication->load($this->id);
          $this->unpublishedTranslations =
            \count($this->translations()) - \count($publication->translations());
          $data = [
            'topic_unpublished_languages' => $this->unpublishedTranslations
          ];
          return FALSE !== $databaseAccess->updateRecord(
              $databaseAccess->getTableName($this->_tableName), $data, ['topic_id' => $this->id]
            );
        }
      }
      return FALSE;
    }
    return TRUE;
  }
}
