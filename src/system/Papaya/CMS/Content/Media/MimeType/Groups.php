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

namespace Papaya\CMS\Content\Media\MimeType {

  use Papaya\CMS\Content\Tables as ContentTables;
  use Papaya\Database\Interfaces\Order as DatabaseOrder;

  class Groups extends \Papaya\Database\Records\Lazy {

    protected $_fields = [
      'id' => 'groups.mimegroup_id',
      'icon' => 'groups.mimegroup_icon',
      'title' => 'translations.mimegroup_title',
      'language_id' => 'translations.lng_id',
    ];

    private $_tableNameGroups = ContentTables::MEDIA_MIMETYPE_GROUPS;
    private $_tableNameTranslations = ContentTables::MEDIA_MIMETYPE_GROUP_TRANSLATIONS;

    protected $_identifierProperties = ['id'];
    protected $_orderByProperties = [
      'title' => DatabaseOrder::ASCENDING,
      'id' => DatabaseOrder::ASCENDING,
    ];

    public function load($filter = [], $limit = NULL, $offset = NULL) {
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
          '.$this->_compileCondition($filter).$this->_compileOrderBy()
      );
      $statement->addTableName('table_groups', $this->_tableNameGroups);
      $statement->addTableName('table_translations', $this->_tableNameTranslations);
      $statement->addInt('language', $languageId);
      return $this->_loadRecords($statement, [], $limit, $offset, $this->_identifierProperties);
    }
  }
}
