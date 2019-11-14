<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Content\Media\MimeType {

  use Papaya\Content\Tables as ContentTables;
  use Papaya\Database\Record\Lazy as LazyDatabaseRecord;

  /**
   * @property int $id
   * @property string $icon
   * @property string $title
   * @property int $languageId
   */
  class Group extends LazyDatabaseRecord {

    protected $_fields = [
      'id' => 'groups.mimegroup_id',
      'icon' => 'groups.mimegroup_icon',
      'title' => 'translations.mimegroup_title',
      'language_id' => 'translations.lng_id',
    ];

    protected $_tableAlias = 'groups';
    protected $_tableName = ContentTables::MEDIA_MIMETYPE_GROUPS;
    private $_tableNameTranslations = ContentTables::MEDIA_MIMETYPE_GROUP_TRANSLATIONS;

    public function load($filter) {
      $languageId = 0;
      if (isset($filter['language_id'])) {
        $languageId = $filter['language_id'];
        unset($filter['language_id']);
      }
      $statement = $this->getDatabaseAccess()->prepare(
        'SELECT 
          groups.mimegroup_id, groups.mimegroup_icon,
          translations.lng_id, translations.mimegroup_title
          FROM :table_groups AS groups
          LEFT JOIN :table_translations AS translations ON (
            translations.mimegroup_id = groups.mimegroup_id AND translations.lng_id = :language
          )
          '.$this->_compileCondition($filter)
      );
      $statement->addTableName('table_groups', $this->_tableName);
      $statement->addTableName('table_translations', $this->_tableNameTranslations);
      $statement->addInt('language', $languageId);
      return $this->_loadRecord($statement, []);
    }

    public function save() {
      if (FALSE !== parent::save()) {
        $translation = $this->getTranslation();
        var_dump($this->toArray());
        $translation->assign($this);
        return $translation->save();
      }
      return FALSE;
    }

    public function getTranslation() {
      $translation = new GroupTranslation();
      $translation->activateLazyLoad(
        [
          'id' => $this->id,
          'language_id' => $this->languageId
        ]
      );
      return $translation;
    }
  }
}
