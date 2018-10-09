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
namespace Papaya\Content\Media;

use Papaya\Content;
use Papaya\Database;
use Papaya\Utility;

class Folders extends Database\Records\Tree {
  protected $_fields = [
    'id' => 'folder_id',
    'parent_id' => 'parent_id',
    'ancestors' => 'parent_path',
    'language_id' => 'lng_id',
    'title' => 'folder_name'
  ];

  /**
   * @return Database\Record\Mapping
   */
  public function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValueFromFieldToProperty = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $property, $field, $value
    ) {
      if ('ancestors' === $property) {
        return Utility\Arrays::decodeIdList($value);
      }
      return $value;
    };
    $mapping->callbacks()->onGetFieldForProperty = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $property
    ) {
      switch ($property) {
        case 'language_id' :
        case 'title' :
          return 'ft.'.$this->_fields[$property];
        default :
          if (isset($this->_fields[$property])) {
            return 'f.'.$this->_fields[$property];
          }
      }
      return NULL;
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
    $sql = "SELECT f.folder_id, f.parent_id, f.parent_path, ft.lng_id, ft.title
              FROM %s AS f
              LEFT JOIN %s AS ft ON (ft.folder_id = f.folder_id AND ft.lng_id = '%d')";
    if (isset($filter['language_id'])) {
      $languageId = (int)$filter['language_id'];
      unset($filter['language_id']);
    } else {
      $languageId = 0;
    }
    $sql .= Utility\Text::escapeForPrintf(
      $this->_compileCondition($filter).$this->_compileOrderBy()
    );
    $parameters = [
      $this->getDatabaseAccess()->getTableName(Content\Tables::MEDIA_FOLDERS),
      $this->getDatabaseAccess()->getTableName(Content\Tables::MEDIA_FOLDER_TRANSLATIONS),
      $languageId
    ];
    return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }
}
