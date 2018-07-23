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

/**
* This object loads page data by different conditions.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentPages extends \PapayaDatabaseRecordsLazy {

  /**
  * Map field names to more convinient property names
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    'id' => 't.topic_id',
    'parent' => 't.prev',
    'path' => 't.prev_path',
    'language_id' => 'tt.lng_id',
    'position' => 't.topic_weight',
    'title' => 'tt.topic_title',
    'view_id' => 'tt.view_id',
    'module_guid' => 'v.module_guid',
    'content' => 'tt.topic_content',
    'scheme' => 't.topic_protocol',
    'author_id' => 't.author_id',
    'author_givenname' => 'au.givenname',
    'author_surname' => 'au.surname',
    'created' => 't.topic_created',
    'modified' => 't.topic_modified',
    'published' => 'topic_published',
    'link_type_id' => 't.linktype_id',
    'viewmode_id' => 'vm.viewmode_id'
  );

  protected $_orderByProperties = array(
    'title' => \PapayaDatabaseInterfaceOrder::ASCENDING,
    'created' => \PapayaDatabaseInterfaceOrder::ASCENDING
  );

  /**
  * Table containing page informations
  *
  * @var string
  */
  protected $_tablePages = \PapayaContentTables::PAGES;

  /**
  * Table containing language specific page informations
  *
  * @var string
  */
  protected $_tablePageTranslations = \PapayaContentTables::PAGE_TRANSLATIONS;

  /**
  * Table containing page publications
  *
  * @var string
  */
  protected $_tablePagePublications = \PapayaContentTables::PAGE_PUBLICATIONS;

  /**
  * Table containing user informations
  *
  * @var string
  */
  protected $_tableAuthenticationUsers = \PapayaContentTables::AUTHENTICATION_USERS;

  /**
  * Table containing page views
  *
  * @var string
  */
  protected $_tableViews = \PapayaContentTables::VIEWS;

  /**
  * Table containing page view configurations for the output modes
  *
  * @var string
  */
  protected $_tableViewConfigurations = \PapayaContentTables::VIEW_CONFIGURATIONS;

  /**
  * This defines if pages are only loaded, if they have a translation in the given language.
  *
  * @var boolean
  */
  private $_translationNeeded = FALSE;

  /**
  * Define if a translation is needed, or pages without translations are loaded, too.
  *
  * @param boolean $translationNeeded
  */
  public function __construct($translationNeeded = FALSE) {
    $this->_translationNeeded = $translationNeeded;
  }

  /**
   * Load pages defined by filter conditions.
   *
   * @param array $filter
   * @param NULL|integer $limit
   * @param NULL|integer $offset
   * @return bool
   */
  public function load($filter = array(), $limit = NULL, $offset = NULL) {
    $databaseAccess = $this->getDatabaseAccess();
    $joinMode = $this->_translationNeeded ? 'INNER' : 'LEFT';
    if (isset($filter['language_id'])) {
      $languageId = (int)$filter['language_id'];
      unset($filter['language_id']);
    } else {
      $languageId = 0;
    }
    if (isset($filter['viewmode_id']) && $filter['viewmode_id'] > 0) {
      $viewModeId = (int)$filter['viewmode_id'];
    } else {
      $viewModeId = 0;
      unset($filter['viewmode_id']);
    }
    $sql = /** @lang TEXT*/
      "SELECT t.topic_id, t.prev, t.prev_path,
              t.linktype_id, t.topic_protocol,
              t.topic_weight,
              tt.lng_id, tt.topic_title, tt.topic_content,
              t.author_id, au.givenname, au.surname,
              t.topic_created, t.topic_modified, tp.topic_modified topic_published,
              tt.view_id, v.module_guid, vm.viewmode_id
         FROM %s AS t
         $joinMode JOIN %s AS tt ON (tt.topic_id = t.topic_id AND tt.lng_id = '%d')
         LEFT JOIN %s AS tp ON (tp.topic_id = t.topic_id)
         LEFT JOIN %s AS v ON (v.view_id = tt.view_id)
         LEFT JOIN %s AS vm ON (vm.view_id = tt.view_id AND vm.viewmode_id = '%d')
         LEFT JOIN %s AS au ON (t.author_id = au.user_id)
              ".PapayaUtilString::escapeForPrintf($this->_compileCondition($filter)).'
              '.PapayaUtilString::escapeForPrintf($this->_compileOrderBy());
    $parameters = array(
      $databaseAccess->getTableName($this->_tablePages),
      $databaseAccess->getTableName($this->_tablePageTranslations),
      $languageId,
      $databaseAccess->getTableName($this->_tablePagePublications),
      $databaseAccess->getTableName($this->_tableViews),
      $databaseAccess->getTableName($this->_tableViewConfigurations),
      $viewModeId,
      $databaseAccess->getTableName($this->_tableAuthenticationUsers)
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, 'id');
  }

  protected function _compileCondition($filter, $prefix = ' WHERE ') {
    $statusConditions = array(
      'modified' => 't.topic_modified > tp.topic_modified',
      'published' => 't.topic_modified <= tp.topic_modified',
      'created' => 'tp.topic_modified IS NULL'
    );
    $conditions = '';
    if (isset($filter['status'], $statusConditions[$filter['status']])) {
      $conditions .= $prefix.' '.$statusConditions[$filter['status']];
      $prefix = ' AND ';
    }
    if (isset($filter['ancestor_id']) && $filter['ancestor_id'] > 0) {
      $ancestorFilter = new PapayaDatabaseConditionGroup($this);
      $ancestorFilter
        ->isEqual('t.prev', $filter['ancestor_id'])
        ->logicalOr()
        ->like('t.prev_path', '*;'.$filter['ancestor_id'].';*');
      $conditions .= $prefix.' '.$ancestorFilter->getSql(TRUE);
      $prefix = ' AND ';
    }
    unset($filter['status'], $filter['ancestor_id']);
    if ($conditions) {
      return $conditions.parent::_compileCondition($filter, $prefix);
    }
    return parent::_compileCondition($filter, $prefix);
  }


  /**
  * Overload the mapping object instantiation, to attach an callback for the mapping process.
  *
  * @return \PapayaDatabaseInterfaceMapping
  */
  protected function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValue = array($this, 'mapValue');
    return $mapping;
  }

  /**
  * Mapping callback that (un)serialzes the parent path field
  *
  * @param object $context
  * @param integer $mode
  * @param string $property
  * @param string $field
  * @param mixed $value
   * @return array|mixed|string
   */
  public function mapValue($context, $mode, $property, $field, $value) {
    if ($property == 'path') {
      if ($mode == \PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY) {
        return \PapayaUtilArray::decodeIdList($value);
      } else {
        return ';'.\PapayaUtilArray::encodeIdList($value).';';
      }
    }
    return $value;
  }

  public function isPublic() {
    return FALSE;
  }
}
