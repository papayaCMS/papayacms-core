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

namespace Papaya\Content\Media {

  use Papaya\Content\Tables;
  use Papaya\Database;
  use Papaya\Utility;

  class Folders extends Database\Records\Tree {
    protected $_fields = [
      'id' => 'folders.folder_id',
      'parent_id' => 'folders.parent_id',
      'ancestors' => 'folders.parent_path',
      'permission_mode' => 'folders.permission_mode',
      'language_id' => 'translations.lng_id',
      'title' => 'translations.folder_name'
    ];

    protected $_orderByProperties = [
      'title' => Database\Interfaces\Order::ASCENDING,
      'id' => Database\Interfaces\Order::ASCENDING
    ];

    /**
     * @return Database\Record\Mapping
     */
    public function _createMapping() {
      $mapping = parent::_createMapping();
      $mapping->callbacks()->onMapValueFromFieldToProperty = static function (
        /** @noinspection PhpUnusedParameterInspection */
        $context, $property, $field, $value
      ) {
        if ('ancestors' === $property) {
          return Utility\Arrays::decodeIdList($value);
        }
        return $value;
      };
      return $mapping;
    }

    /**
     * @param array|null $filter
     * @param int|null $limit
     * @param int|null $offset
     * @return bool
     */
    public function load($filter = NULL, $limit = NULL, $offset = NULL) {
      if (isset($filter['language_id'])) {
        $languageId = (int)$filter['language_id'];
        unset($filter['language_id']);
      } else {
        $languageId = 0;
      }
      $statement = $this->getDatabaseAccess()->prepare(
        "SELECT 
          folders.folder_id, folders.parent_id, folders.parent_path, folders.permission_mode,
          translations.lng_id, translations.folder_name
        FROM :folders AS folders
        LEFT JOIN :translations AS translations ON (
          translations.folder_id = folders.folder_id AND translations.lng_id = :language_id
        ) " .
        $this->_compileCondition($filter) . $this->_compileOrderBy()
      );
      $statement->addTableName('folders', Tables::MEDIA_FOLDERS);
      $statement->addTableName('translations', Tables::MEDIA_FOLDER_TRANSLATIONS);
      $statement->addInt('language_id', $languageId);
      return $this->_loadRecords($statement, [], $limit, $offset, $this->_identifierProperties);
    }
  }
}
