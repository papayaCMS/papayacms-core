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

namespace Papaya\Content\Page;
/**
 * Provide data encapsulation for the content page translation details.
 *
 * Allows to load/save the page translation.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property integer $id
 * @property integer $languageId
 * @property string $title
 * @property array $content
 * @property-read integer $created
 * @property-read integer $modified
 * @property string $metaTitle
 * @property string $metaKeywords
 * @property string $metaDescription
 * @property integer $viewId
 * @property-read string $viewTitle
 * @property-read string $viewName
 * @property-read string $moduleGuid
 * @property-read string $moduleTitle
 */
class Translation extends \PapayaDatabaseRecordLazy {

  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
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
  );

  protected $_tableName = \PapayaContentTables::PAGE_TRANSLATIONS;
  protected $_tableAlias = 'tt';

  protected $_tableNameViews = \PapayaContentTables::VIEWS;

  public function load($filter) {
    $fields = implode(', ', $this->mapping()->getFields());
    $sql = "SELECT $fields FROM %s AS tt, %s AS v WHERE v.view_id = tt.view_id ";
    $sql .= \PapayaUtilString::escapeForPrintf($this->_compileCondition($filter, "AND"));
    $parameters = array(
      $this->getDatabaseAccess()->getTableName($this->_tableName),
      $this->getDatabaseAccess()->getTableName($this->_tableNameViews)
    );
    return $this->_loadRecord($sql, $parameters);
  }

  /**
   * Attach callbacks for serialized field values
   *
   * @see \PapayaDatabaseRecord::_createMapping()
   */
  public function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValueFromFieldToProperty = array(
      $this, 'callbackMapValueFromFieldToProperty'
    );
    $mapping->callbacks()->onMapValueFromPropertyToField = array(
      $this, 'callbackMapValueFromPropertyToField'
    );
    return $mapping;
  }

  /**
   * Unserialize path and permissions field values
   *
   * @param object $context
   * @param string $property
   * @param string $field
   * @param string $value
   * @return mixed
   */
  public function callbackMapValueFromFieldToProperty($context, $property, $field, $value) {
    switch ($property) {
      case 'content' :
        return \PapayaUtilStringXml::unserializeArray($value);
    }
    return $value;
  }

  /**
   * Serialize path and permissions field values
   *
   * @param object $context
   * @param string $property
   * @param string $field
   * @param string $value
   * @return mixed
   */
  public function callbackMapValueFromPropertyToField($context, $property, $field, $value) {
    switch ($property) {
      case 'content' :
        return \PapayaUtilStringXml::serializeArray(empty($value) ? array() : $value);
    }
    return $value;
  }

  public function _createKey() {
    return new \PapayaDatabaseRecordKeyFields(
      $this,
      $this->_tableName,
      array('id', 'language_id')
    );
  }

  public function save() {
    if (empty($this['id']) || empty($this['language_id'])) {
      return FALSE;
    }
    return parent::save();
  }

  public function _insertRecord() {
    $this['created'] = $this['modified'] = time();
    return parent::_insertRecord();
  }

  public function _updateRecord() {
    $this['modified'] = time();
    return parent::_updateRecord();
  }
}
