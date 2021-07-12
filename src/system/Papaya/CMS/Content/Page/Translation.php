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
 * Provide data encapsulation for the content page translation details.
 *
 * Allows to load/save the page translation.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property int $id
 * @property int $languageId
 * @property string $title
 * @property array $content
 * @property-read int $created
 * @property-read int $modified
 * @property string $metaTitle
 * @property string $metaKeywords
 * @property string $metaDescription
 * @property int $viewId
 * @property-read string $viewTitle
 * @property-read string $viewName
 * @property-read string $moduleGuid
 * @property-read string $moduleTitle
 */
class Translation extends Database\Record\Lazy {
  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = [
    'id' => 'tt.topic_id',
    'language_id' => 'tt.lng_id',
    'title' => 'tt.topic_title',
    'content' => 'tt.topic_content',
    'created' => 'tt.topic_trans_created',
    'modified' => 'tt.topic_trans_modified',
    'meta_title' => 'tt.meta_title',
    'meta_keywords' => 'tt.meta_keywords',
    'meta_description' => 'tt.meta_descr',
    'view_id' => 'tt.view_id',
    'view_name' => 'v.view_name',
    'module_guid' => 'v.module_guid'
  ];

  protected $_tableName = Content\Tables::PAGE_TRANSLATIONS;

  protected $_tableAlias = 'tt';

  protected $_tableNameViews = Content\Tables::VIEWS;

  public function load($filter) {
    $fields = \implode(', ', $this->mapping()->getFields());
    $sql = "SELECT $fields FROM %s AS tt, %s AS v WHERE v.view_id = tt.view_id ";
    $sql .= Utility\Text::escapeForPrintf($this->_compileCondition($filter, 'AND'));
    $parameters = [
      $this->getDatabaseAccess()->getTableName($this->_tableName),
      $this->getDatabaseAccess()->getTableName($this->_tableNameViews)
    ];
    return $this->_loadRecord($sql, $parameters);
  }

  /**
   * Attach callbacks for serialized field values
   *
   * @return Database\Record\Mapping
   */
  public function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValueFromFieldToProperty = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $property, $field, $value
    ) {
      switch ($property) {
        case 'content' :
          return Utility\Text\XML::unserializeArray($value);
      }
      return $value;
    };
    $mapping->callbacks()->onMapValueFromPropertyToField = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $property, $field, $value
    ) {
      switch ($property) {
        case 'content' :
          return Utility\Text\XML::serializeArray(empty($value) ? [] : $value);
      }
      return $value;
    };
    return $mapping;
  }

  /**
   * @return Database\Record\Key\Fields
   */
  public function _createKey() {
    return new Database\Record\Key\Fields(
      $this,
      $this->_tableName,
      ['id', 'language_id']
    );
  }

  /**
   * @return bool|Database\Interfaces\Key
   */
  public function save() {
    if (empty($this['id']) || empty($this['language_id'])) {
      return FALSE;
    }
    return parent::save();
  }

  /**
   * @return false|Database\Interfaces\Key
   */
  public function _insertRecord() {
    $this['created'] = $this['modified'] = \time();
    return parent::_insertRecord();
  }

  /**
   * @return bool
   */
  public function _updateRecord() {
    $this['modified'] = \time();
    return parent::_updateRecord();
  }
}
