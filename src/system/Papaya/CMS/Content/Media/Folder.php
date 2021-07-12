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

namespace Papaya\CMS\Content\Media {

  use Papaya\CMS\Content;
  use Papaya\CMS\Content\Tables;
  use Papaya\Database;
  use Papaya\Utility;

  /**
   * @property int $id
   * @property int $parentId
   * @property int[] $ancestors
   * @property string $permissionMode
   * @property int $languageId
   * @property string $title
   */
  class Folder extends Database\Record\Lazy {
    const PERMISSION_MODE_DEFINE = 'own';
    const PERMISSION_MODE_EXTEND = 'additional';
    const PERMISSION_MODE_INHERIT = 'inherited';

    protected $_fields = [
      'id' => 'folders.folder_id',
      'parent_id' => 'folders.parent_id',
      'ancestors' => 'folders.parent_path',
      'permission_mode' => 'folders.permission_mode',
      'language_id' => 'translations.lng_id',
      'title' => 'translations.folder_name'
    ];

    protected $_tableName = Content\Tables::MEDIA_FOLDERS;
    protected $_tableAlias = 'folders';
    /**
     * @var mixed|Folder\FolderTranslations
     */
    private $_translations;

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
      $mapping->callbacks()->onMapValueFromPropertyToField = static function (
        /** @noinspection PhpUnusedParameterInspection */
        $context, $property, $field, $value
      ) {
        if ('ancestors' === $property) {
          return Utility\Arrays::encodeIdList($value);
        }
        return $value;
      };
      return $mapping;
    }

    /**
     * @param array|null $filter
     * @return bool
     */
    public function load($filter = NULL) {
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
        $this->_compileCondition($filter)
      );
      $statement->addTableName('folders', Tables::MEDIA_FOLDERS);
      $statement->addTableName('translations', Tables::MEDIA_FOLDER_TRANSLATIONS);
      $statement->addInt('language_id', $languageId);
      return $this->_loadRecord($statement);
    }

    public function save() {
      $result = parent::save();
      if (
        $result &&
        ($this->id > 0) &&
        ($this->languageId > 0)
      ) {
        $translation = $this->translations()->getItem(['id' => $this->id, 'language_id' => $this->languageId]);
        $translation->assign($this);
        return $translation->save();
      }
      return $result;
    }

    public function translations(Content\Media\Folder\FolderTranslations $translations = NULL) {
      if (NULL !== $translations) {
        $this->_translations = $translations;
      } elseif (NULL === $this->_translations) {
        $this->_translations = new Content\Media\Folder\FolderTranslations();
        $this->_translations->papaya($this->papaya());
      }
      return $this->_translations;
    }
  }
}
