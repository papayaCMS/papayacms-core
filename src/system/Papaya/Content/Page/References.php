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
 * Provide data encapsulation for the content page references list.
 *
 * The list can contain additional data, used to display the list.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class References extends \Papaya\Database\BaseObject\Records {

  /**
   * page id used to load the references, will be the source_id in the resulting record arrays
   */
  private $_pageId = 0;

  /**
   * Load the references for a page, if a lanauge id is provided, try to loade page titles for the
   * language.
   *
   * @param integer $pageId
   * @param integer $languageId
   * @return bool
   */
  public function load($pageId, $languageId = 0) {
    $this->_pageId = $pageId;
    $sql = "SELECT tr.topic_source_id, tr.topic_target_id, tr.topic_note,
                   t_src.topic_modified AS topic_source_modified,
                   t_tgt.topic_modified AS topic_target_modified,
                   tt_src.topic_title AS topic_source_title,
                   tt_tgt.topic_title AS topic_target_title
              FROM %1\$s tr
              LEFT JOIN %2\$s t_src ON (t_src.topic_id = tr.topic_source_id)
              LEFT JOIN %2\$s t_tgt ON (t_tgt.topic_id = tr.topic_target_id)
              LEFT JOIN %3\$s tt_src
                ON (tt_src.topic_id = tr.topic_source_id AND tt_src.lng_id = '%5\$d')
              LEFT JOIN %3\$s tt_tgt
                ON (tt_tgt.topic_id = tr.topic_target_id AND tt_tgt.lng_id = '%5\$d')
             WHERE tr.topic_source_id = '%4\$d' OR
                   tr.topic_target_id = '%4\$d'
             ORDER BY tr.topic_source_id, tr.topic_target_id";
    $parameters = array(
      $this->databaseGetTableName(\Papaya\Content\Tables::PAGE_REFERENCES),
      $this->databaseGetTableName(\Papaya\Content\Tables::PAGES),
      $this->databaseGetTableName(\Papaya\Content\Tables::PAGE_TRANSLATIONS),
      $pageId,
      $languageId
    );
    return $this->_loadRecords($sql, $parameters);
  }

  /**
   * Converts the record from database into a values. The mapping here is special because
   * the reference could be saved in either direction, the mapping converts it so that the
   * id used to load the refrences is always the source id.
   *
   * @param \PapayaDatabaseResult $databaseResult
   * @param string $idField
   */
  protected function _fetchRecords($databaseResult, $idField = '') {
    $this->_records = array();
    while ($row = $databaseResult->fetchRow(\PapayaDatabaseResult::FETCH_ASSOC)) {
      if ($row['topic_source_id'] == $this->_pageId) {
        $record = array(
          'source_id' => $row['topic_source_id'],
          'target_id' => $targetId = $row['topic_target_id'],
          'title' => $row['topic_target_title'],
          'modified' => $row['topic_target_modified'],
          'note' => $row['topic_note']
        );
      } else {
        $record = array(
          'source_id' => $row['topic_target_id'],
          'target_id' => $targetId = $row['topic_source_id'],
          'title' => $row['topic_source_title'],
          'modified' => $row['topic_source_modified'],
          'note' => $row['topic_note']
        );
      }
      $this->_records[$targetId] = $record;
    }
  }
}
