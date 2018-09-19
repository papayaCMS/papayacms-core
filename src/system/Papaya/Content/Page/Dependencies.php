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

/**
 * Provide data encapsulation for the content page dependencies list.
 *
 * The list contains not only the dependency information but the needed data to output it to
 * the user.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Dependencies extends \Papaya\Database\BaseObject\Records {
  /**
   * Map field names to value identfiers
   *
   * @var array
   */
  protected $_fieldMapping = [
    'topic_id' => 'id',
    'topic_origin_id' => 'origin_id',
    'topic_synchronization' => 'synchronization',
    'topic_note' => 'note',
    'topic_title' => 'title',
    'topic_modified' => 'modified',
    'topic_published' => 'published',
    'view_id' => 'view_id',
    'published_from' => 'published_from',
    'published_to' => 'published_to',
    'topic_unpublished_languages' => 'unpublished_languages'
  ];

  /**
   * Load a list of references for a specified origin page id
   *
   * @param int $originId
   * @param int $languageId
   * @param int $limit
   * @param int $offset
   *
   * @return bool
   */
  public function load($originId, $languageId = 0, $limit = NULL, $offset = NULL) {
    $sql = "SELECT td.topic_id, td.topic_origin_id, td.topic_synchronization, td.topic_note,
                   tt.topic_title, tt.view_id,
                   t.topic_modified, t.topic_unpublished_languages,
                   tp.topic_modified as topic_published,
                   tp.published_from, tp.published_to
              FROM %s AS td
             INNER JOIN %s AS t ON (t.topic_id = td.topic_id)
              LEFT JOIN %s AS tt ON (tt.topic_id = td.topic_id AND tt.lng_id = '%d')
              LEFT JOIN %s AS tp ON (tp.topic_id = td.topic_id)
             WHERE td.topic_origin_id = '%d'
             ORDER BY tt.topic_title, t.topic_id";
    $parameters = [
      $this->databaseGetTableName(\Papaya\Content\Tables::PAGE_DEPENDENCIES),
      $this->databaseGetTableName(\Papaya\Content\Tables::PAGES),
      $this->databaseGetTableName(\Papaya\Content\Tables::PAGE_TRANSLATIONS),
      (int)$languageId,
      $this->databaseGetTableName(\Papaya\Content\Tables::PAGE_PUBLICATIONS),
      (int)$originId
    ];
    return $this->_loadRecords($sql, $parameters, 'topic_id', $limit, $offset);
  }

  /**
   * Get a dependency record object, to edit a dependency.
   *
   * If a valid page id is provided that exists in the list, the data will be assigned to the new
   * record object.
   *
   * @param int $pageId
   *
   * @return \Papaya\Content\Page\Dependency
   */
  public function getDependency($pageId) {
    $result = new \Papaya\Content\Page\Dependency();
    if (isset($this->_records[$pageId])) {
      $result->assign($this->_records[$pageId]);
    }
    return $result;
  }

  /**
   * Delete a defined dependency by the target/clone page id.
   *
   * @param int $pageId
   *
   * @return bool
   */
  public function delete($pageId) {
    $result = $this->databaseDeleteRecord(
      $this->databaseGetTableName(\Papaya\Content\Tables::PAGE_DEPENDENCIES),
      'topic_id',
      (int)$pageId
    );
    if (FALSE !== $result && isset($this->_records[$pageId])) {
      unset($this->_records[$pageId]);
    }
    return $result;
  }

  /**
   * Change to origin if of a list of referenced defined by the origin.
   *
   * @param int $originId
   * @param int $newOriginId
   *
   * @return bool|int
   */
  public function changeOrigin($originId, $newOriginId) {
    $result = FALSE;
    $dependency = new \Papaya\Content\Page\Dependency();
    $dependency->setDatabaseAccess($this->getDatabaseAccess());
    $dependency->load($newOriginId);
    if ($this->delete($newOriginId)) {
      $result = $this->databaseUpdateRecord(
        $this->databaseGetTableName(\Papaya\Content\Tables::PAGE_DEPENDENCIES),
        ['topic_origin_id' => $newOriginId],
        ['topic_origin_id' => $originId]
      );
      if (FALSE !== $result) {
        $dependency->assign(
          [
            'id' => $originId,
            'origin_id' => $newOriginId
          ]
        );
        $result = $dependency->save();
      }
    }
    return $result;
  }
}
