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
 * Provide data encapsulation for the content box translation details.
 *
 * Allows to load/save the box translation.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property int $boxId
 * @property int $languageId
 * @property string $title
 * @property array $content
 * @property-read int $created
 * @property-read int $modified
 * @property int $viewId
 * @property-read string $viewTitle
 * @property-read string $viewName
 * @property-read string $moduleGuid
 * @property-read string $moduleTitle
 */
class Translation extends Database\BaseObject\Record {
  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = [
    'box_id' => 'box_id',
    'language_id' => 'lng_id',
    'title' => 'box_title',
    'content' => '',
    'created' => 'box_trans_created',
    'modified' => 'box_trans_modified',
    'view_id' => 'view_id',
    'view_title' => 'view_title',
    'view_name' => 'view_name',
    'module_guid' => 'module_guid',
    'module_title' => 'module_title'
  ];

  protected $_tableNameBoxTranslations = Content\Tables::BOX_TRANSLATIONS;

  /**
   * Load box translation details
   *
   * @param mixed $filter
   *
   * @return array|bool|null
   *
   * @internal param $array ($boxId, $languageId) $filter
   */
  public function load($filter) {
    $sql = 'SELECT t.box_id, t.box_title, t.box_data, t.lng_id,
                   t.box_trans_created, t.box_trans_modified,
                   t.view_id, v.view_title, v.view_name,
                   m.module_guid, m.module_title
              FROM %s t
              LEFT OUTER JOIN %s v ON v.view_id = t.view_id
              LEFT OUTER JOIN %s m ON m.module_guid = v.module_guid
             WHERE t.box_id = %d
               AND t.lng_id = %d';
    $parameters = [
      $this->databaseGetTableName($this->_tableNameBoxTranslations),
      $this->databaseGetTableName(Content\Tables::VIEWS),
      $this->databaseGetTableName(Content\Tables::MODULES),
      (int)$filter[0],
      (int)$filter[1]
    ];
    return $this->_loadRecord($sql, $parameters, [$this, 'convertBoxRecordToValues']);
  }

  /**
   * Convert the database record into the values property
   *
   * The method uses a basic function to map the fields. Content needs a special handling because it
   * contains a serialized array.
   *
   * It is used as a callback function after reading the record from the database result.
   *
   * @param array $record
   *
   * @return array
   */
  protected function convertBoxRecordToValues($record) {
    $values = $this->convertRecordToValues($record);
    $values['content'] = \Papaya\Utility\Text\XML::unserializeArray($record['box_data']);
    return $values;
  }

  /**
   * Save (insert or update) box translation record.
   *
   * This method check if the record exists and calls the nessesary private method.
   *
   * @return bool
   */
  public function save() {
    if ($this->boxId > 0 && $this->languageId > 0) {
      $sql = 'SELECT COUNT(*)
                FROM %s
               WHERE box_id = %d
                 AND lng_id = %d';
      $parameters = [
        $this->databaseGetTableName($this->_tableNameBoxTranslations),
        (int)$this->boxId,
        (int)$this->languageId
      ];
      if ($res = $this->databaseQueryFmt($sql, $parameters)) {
        if ($res->fetchField() > 0) {
          return $this->_update();
        }
        return $this->_insert();
      }
    }
    return FALSE;
  }

  /**
   * Insert a new translation record into database table
   *
   * @return bool
   */
  private function _insert() {
    $data = [
      'box_id' => (int)$this->boxId,
      'lng_id' => (int)$this->languageId,
      'box_title' => (string)$this->title,
      'box_data' => \is_array($this->content)
        ? \Papaya\Utility\Text\XML::serializeArray($this->content) : '',
      'box_trans_created' => \time(),
      'box_trans_modified' => \time(),
      'view_id' => (int)(string)$this->viewId
    ];
    return FALSE !== $this->databaseInsertRecord(
        $this->databaseGetTableName($this->_tableNameBoxTranslations),
        NULL,
        $data
      );
  }

  /**
   * Update an existing translation record in the database table
   *
   * @return bool
   */
  private function _update() {
    $filter = [
      'box_id' => (int)$this->boxId,
      'lng_id' => (int)$this->languageId
    ];
    $data = [
      'box_title' => (string)$this->title,
      'box_data' => \is_array($this->content)
        ? \Papaya\Utility\Text\XML::serializeArray($this->content) : '',
      'box_trans_modified' => \time(),
      'view_id' => (int)(string)$this->viewId
    ];
    return FALSE !== $this->databaseUpdateRecord(
        $this->databaseGetTableName($this->_tableNameBoxTranslations),
        $data,
        $filter
      );
  }
}
