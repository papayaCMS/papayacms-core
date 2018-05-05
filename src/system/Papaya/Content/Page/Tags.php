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

/**
* Provide data encapsulation for the content page tag/label list.
*
* This is a list of the attached tags for an page. The list can not only contain the link data but
* additional data like the tag title. For the additional data an language id has to be provided.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentPageTags extends \PapayaDatabaseObjectList {

  /**
  * All tag links are saved into one table, the type specified the link group
  *
  * @var string
  */
  private $_linkType = 'topic';

  /**
  * Map the fields to array keys
  *
  * @var array(string=>string)
  */
  protected $_fieldMapping = array(
    'link_id' => 'page_id',
    'link_priority' => 'priority',
    'tag_id' => 'id',
    'tag_title' => 'title',
    'tag_image' => 'image',
    'tag_description' => 'description',
    'tag_char' => 'char'
  );

  /**
  * Load list of tags for a page, load titles, media ids and descriptions if language is provided
  *
  * @param integer $pageId
  * @param integer $languageId
  * @return boolean
  */
  public function load($pageId, $languageId = 0) {
    $sql = "SELECT tl.link_id, tl.link_priority, tl.tag_id,
                   tt.tag_title, tt.tag_image, tt.tag_description, tt.tag_char
              FROM %s AS tl
              LEFT OUTER JOIN %s AS tt ON (tt.tag_id = tl.tag_id AND tt.lng_id = '%d')
             WHERE tl.link_type = '%s'
               AND tl.link_id = '%d'
             ORDER BY tl.link_priority, tt.tag_title";
    $parameters = array(
      $this->databaseGetTableName(\PapayaContentTables::TAG_LINKS),
      $this->databaseGetTableName(\PapayaContentTables::TAG_TRANSLATIONS),
      $languageId,
      $this->_linkType,
      $pageId
    );
    return $this->_loadRecords($sql, $parameters, 'tag_id');
  }

  /**
  * Remove all tags for a specified page id.
  *
  * @param integer $pageId
  * @return boolean
  */
  public function clear($pageId) {
    return FALSE !== $this->databaseDeleteRecord(
      $this->databaseGetTableName(\PapayaContentTables::TAG_LINKS),
      array(
        'link_type' => $this->_linkType,
        'link_id' => $pageId
      )
    );
  }

  /**
   * Mass insert of tags for a specified page id
   *
   * @param int $pageId
   * @param array $tagIds
   * @return boolean
   */
  public function insert($pageId, array $tagIds) {
    $data = array();
    foreach ($tagIds as $tagId) {
      $data[] = array(
        'link_type' => $this->_linkType,
        'link_id' => $pageId,
        'tag_id' => $tagId
      );
    }
    return FALSE !== $this->databaseInsertRecords(
      $this->databaseGetTableName(\PapayaContentTables::TAG_LINKS),
      $data
    );
  }
}
