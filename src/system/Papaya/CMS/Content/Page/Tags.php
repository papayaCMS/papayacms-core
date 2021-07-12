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
namespace Papaya\CMS\Content\Page;

use Papaya\CMS\Content;
use Papaya\Database;
use Papaya\Utility;

/**
 * Provide data encapsulation for the content page tag/label list.
 *
 * This is a list of the attached tags for an page. The list can not only contain the link data but
 * additional data like the tag title. For the additional data an language id has to be provided.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Tags extends Database\BaseObject\Records {
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
  protected $_fieldMapping = [
    'link_id' => 'page_id',
    'link_priority' => 'priority',
    'tag_id' => 'id',
    'tag_title' => 'title',
    'tag_image' => 'image',
    'tag_description' => 'description',
    'tag_char' => 'char',
    'category_name' => 'category'
  ];

  /**
   * Load list of tags for a page, load titles, media ids and descriptions if language is provided
   *
   * @param int $pageId
   * @param int $languageId
   * @param array $categoryIds
   *
   * @return bool
   */
  public function load($pageId, $languageId = 0, array $categoryIds = NULL) {
    $categoryCondition = '';
    if ($categoryIds) {
      $categoryCondition = Utility\Text::escapeForPrintf(
        ' AND '.$this->databaseGetSqlCondition(
          ['t.category_id' => $categoryIds]
        )
      );
    }
    $sql = "SELECT tl.link_id, tl.link_priority, tl.tag_id,
                   tt.tag_title, tt.tag_image, tt.tag_description, tt.tag_char,
                   c.category_name
              FROM %s AS tl
              LEFT OUTER JOIN %s AS tt ON (tt.tag_id = tl.tag_id AND tt.lng_id = '%d')
              LEFT OUTER JOIN %s AS t ON (t.tag_id = tl.tag_id)
              LEFT OUTER JOIN %s AS c ON (c.category_id = t.category_id)
             WHERE tl.link_type = '%s'
               AND tl.link_id = '%d'
                $categoryCondition
             ORDER BY tl.link_priority, tt.tag_title";
    $parameters = [
      $this->databaseGetTableName(Content\Tables::TAG_LINKS),
      $this->databaseGetTableName(Content\Tables::TAG_TRANSLATIONS),
      $languageId,
      $this->databaseGetTableName(Content\Tables::TAGS),
      $this->databaseGetTableName(Content\Tables::TAG_CATEGORY),
      $this->_linkType,
      $pageId
    ];
    return $this->_loadRecords($sql, $parameters, 'tag_id');
  }

  /**
   * Remove all tags for a specified page id.
   *
   * @param int $pageId
   *
   * @return bool
   */
  public function clear($pageId) {
    return FALSE !== $this->databaseDeleteRecord(
        $this->databaseGetTableName(Content\Tables::TAG_LINKS),
        [
          'link_type' => $this->_linkType,
          'link_id' => $pageId
        ]
      );
  }

  /**
   * Mass insert of tags for a specified page id
   *
   * @param int $pageId
   * @param array $tagIds
   *
   * @return bool
   */
  public function insert($pageId, array $tagIds) {
    $data = [];
    foreach ($tagIds as $tagId) {
      $data[] = [
        'link_type' => $this->_linkType,
        'link_id' => $pageId,
        'tag_id' => $tagId
      ];
    }
    return FALSE !== $this->databaseInsertRecords(
        $this->databaseGetTableName(Content\Tables::TAG_LINKS),
        $data
      );
  }
}
