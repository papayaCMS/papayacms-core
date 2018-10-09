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
namespace Papaya\Content\Box;

use Papaya\Content;
use Papaya\Database;

/**
 * Provide data encapsulation for the content box working copy.
 *
 * Allows to load/save/publish the working copy of a box.
 * It contains no validation, only the database access encapsulation.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property int $id box id
 * @property int $groupId box group id
 * @property string $name administration interface box name
 * @property int $created box creation timestamp
 * @property int $modified last modification timestamp
 * @property int $cacheMode box content cache mode (system, none, own)
 * @property int $cacheTime box content cache time, if mode == own
 * @property int $unpublishedTranslations internal counter for unpublished translations
 */
class Work extends Content\Box {
  /**
   * Save box to database
   *
   * @return int|bool
   */
  public function save() {
    if (empty($this['id'])) {
      $this['created'] = $this['modified'] = \time();
    } else {
      $this['modified'] = \time();
    }
    return parent::save();
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
   * Publish the box and it's translations (depending on the $languageIds
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
    if (!empty($languageIds)) {
      $deleted = $this->databaseDeleteRecord(
        $this->databaseGetTableName(Content\Tables::BOX_PUBLICATION_TRANSLATIONS),
        [
          'box_id' => $this->id,
          'lng_id' => $languageIds
        ]
      );
      if (FALSE !== $deleted) {
        $filter = \str_replace('%', '%%', $this->databaseGetSqlCondition(['lng_id' => $languageIds]));
        $now = \time();
        $sql = "INSERT INTO %s
                       (box_id, lng_id, box_title, box_data, view_id,
                        box_trans_created, box_trans_modified)
                SELECT t.box_id, t.lng_id, t.box_title, t.box_data, t.view_id,
                       t.box_trans_created, '%d'
                  FROM %s t
                 WHERE t.box_id = %d AND $filter";
        $parameters = [
          $this->databaseGetTableName(Content\Tables::BOX_PUBLICATION_TRANSLATIONS),
          $now,
          $this->databaseGetTableName(Content\Tables::BOX_TRANSLATIONS),
          $this->id
        ];
        if (FALSE !== $this->databaseQueryFmt($sql, $parameters)) {
          $publication->load($this->id);
          $this->unpublishedTranslations =
            \count($this->translations()) - \count($publication->translations());
          $data = [
            'box_unpublished_languages' => $this->unpublishedTranslations
          ];
          return FALSE !== $this->databaseUpdateRecord(
              $this->databaseGetTableName($this->_tableName), $data, ['box_id' => $this->id]
            );
        }
      }
      return FALSE;
    }
    return TRUE;
  }
}
