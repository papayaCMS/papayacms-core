<?php
/**
* This object loads page data by different conditions.
*
* Allows to load pages and provides basic function for the working copy and publication.
*
* This is an abstract superclass, please use {@see PapayaContentPageWork} to modify the
* working copy of a page or {@see PapayaContentPagePublication} to use the published page.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Content
* @version $Id: Pages.php 38815 2013-09-19 09:45:44Z weinert $
*/

/**
* This object loads page data by different conditions.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentPages extends PapayaDatabaseRecordsLazy {

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
    'title' => PapayaDatabaseInterfaceOrder::ASCENDING,
    'created' => PapayaDatabaseInterfaceOrder::ASCENDING
  );

  /**
  * Table containing page informations
  *
  * @var string
  */
  protected $_tablePages = PapayaContentTables::PAGES;

  /**
  * Table containing language specific page informations
  *
  * @var string
  */
  protected $_tablePageTranslations = PapayaContentTables::PAGE_TRANSLATIONS;

  /**
  * Table containing page publications
  *
  * @var string
  */
  protected $_tablePagePublications = PapayaContentTables::PAGE_PUBLICATIONS;

  /**
  * Table containing user informations
  *
  * @var string
  */
  protected $_tableAuthenticationUsers = PapayaContentTables::AUTHENTICATION_USERS;

  /**
  * Table containing page views
  *
  * @var string
  */
  protected $_tableViews = PapayaContentTables::VIEWS;

  /**
  * Table containing page view configurations for the output modes
  *
  * @var string
  */
  protected $_tableViewConfigurations = PapayaContentTables::VIEW_CONFIGURATIONS;

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
  public function load(array $filter = array(), $limit = NULL, $offset = NULL) {
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
    $sql = "SELECT t.topic_id, t.prev, t.prev_path,
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
                   ".$this->_compileCondition($filter)."
                   ".$this->_compileOrderBy();
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
    if (isset($filter['status']) && isset($statusConditions[$filter['status']])) {
      $result = $prefix.' '.$statusConditions[$filter['status']];
      unset($filter['status']);
      return $result.parent::_compileCondition($filter, $prefix, ' AND ');
    }
    return parent::_compileCondition($filter, $prefix);
  }


  /**
  * Overload the mapping object instanzation, to attach an callback for the mapping process.
  *
  * @return PapayaDatabaseInterfaceMapping
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
      if ($mode == PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY) {
        return PapayaUtilArray::decodeIdList($value);
      } else {
        return ';'.PapayaUtilArray::encodeIdList($value).';';
      }
    }
    return $value;
  }

  public function isPublic() {
    return FALSE;
  }
}
