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
namespace Papaya\CMS\Content\Box\Version;

use Papaya\CMS\Content;
use Papaya\Database;

/**
 * Provide data encapsulation for the content box version translations list.
 *
 * The list does not contain all detail data, it is for list outputs etc. To get the full data
 * use {@see \Papaya\CMS\Content\Box\Version\Translation}.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Translations extends Database\BaseObject\Records {
  /**
   * Map field names to value identifiers
   *
   * @var array
   */
  protected $_fieldMapping = [
    'box_id' => 'id',
    'lng_id' => 'language_id',
    'box_title' => 'title',
    'box_trans_modified' => 'modified',
    'view_title' => 'view'
  ];

  protected $_translationsTableName = Content\Tables::BOX_VERSION_TRANSLATIONS;

  /**
   * Load translation list informations
   *
   * @param int $boxId
   *
   * @return bool
   */
  public function load($boxId) {
    $sql = 'SELECT bt.box_id, bt.lng_id, bt.box_trans_modified,
                   bt.topic_title,
                   v.view_title
              FROM %s bt
              LEFT OUTER JOIN %s v ON (v.view_id = bt.view_id)
             WHERE tt.box_id = %d';
    $parameters = [
      $this->databaseGetTableName($this->_translationsTableName),
      $this->databaseGetTableName(Content\Tables::VIEWS),
      (int)$boxId
    ];
    return $this->_loadRecords($sql, $parameters, 'lng_id');
  }

  /**
   * Get a detail object for a single translation.
   *
   * @param int $boxId
   * @param int $languageId
   *
   * @return \Papaya\CMS\Content\Box\Translation
   */
  public function getTranslation($boxId, $languageId) {
    $result = new Translation();
    $result->setDatabaseAccess($this->getDatabaseAccess());
    $result->load([$boxId, $languageId]);
    return $result;
  }
}
