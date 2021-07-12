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

/**
 * Provide data encapsulation for the content page version list. The versions are created if
 * a page is published. They are not changeable.
 *
 * The list does not contain all detail data, it is for list outputs etc. To get the full data
 * use {@see \Papaya\CMS\Content\Page\Version}.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Versions extends Database\BaseObject\Records {
  /**
   * Map field names to value identfiers
   *
   * @var array
   */
  protected $_fieldMapping = [
    'version_id' => 'id',
    'version_time' => 'created',
    'version_author_id' => 'owner',
    'version_message' => 'message',
    'topic_change_level' => 'level',
    'topic_id' => 'page_id'
  ];

  /**
   * Version table name
   *
   * @var string
   */
  protected $_versionsTableName = Content\Tables::PAGE_VERSIONS;

  /**
   * Load version list informations
   *
   * @param int $pageId
   * @param null|int $limit maximum records returned
   * @param null|int $offset start offset for limited results
   *
   * @return bool
   */
  public function load($pageId, $limit = NULL, $offset = NULL) {
    $sql = 'SELECT version_id, version_time, version_author_id, version_message,
                   topic_change_level, topic_id
              FROM %s
             WHERE topic_id = %d
             ORDER BY version_time DESC';
    $parameters = [
      $this->databaseGetTableName($this->_versionsTableName),
      (int)$pageId
    ];
    return $this->_loadRecords($sql, $parameters, 'version_id', $limit, $offset);
  }

  /**
   * Create a new version record object and load the specified version data
   *
   * @param int $versionId
   *
   * @return Version|null
   */
  public function getVersion($versionId) {
    $result = new Version();
    $result->setDatabaseAccess($this->getDatabaseAccess());
    $result->load($versionId);
    return $result;
  }
}
