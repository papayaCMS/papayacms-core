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
namespace Papaya\Content;

use Papaya\Database;
use Papaya\Utility;

/**
 * This object loads page data by different conditions.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Pages extends Database\Records\Lazy {
  /**
   * Map field names to more convenient property names
   *
   * @var string[]
   */
  protected $_fields = [
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
  ];

  protected $_orderByProperties = [
    'title' => Database\Interfaces\Order::ASCENDING,
    'created' => Database\Interfaces\Order::ASCENDING
  ];

  /**
   * Table containing page information
   *
   * @var string
   */
  protected $_tablePages = Tables::PAGES;

  /**
   * Table containing language specific page information
   *
   * @var string
   */
  protected $_tablePageTranslations = Tables::PAGE_TRANSLATIONS;

  /**
   * Table containing page publications
   *
   * @var string
   */
  protected $_tablePagePublications = Tables::PAGE_PUBLICATIONS;

  /**
   * Table containing user information
   *
   * @var string
   */
  protected $_tableAuthenticationUsers = Tables::AUTHENTICATION_USERS;

  /**
   * Table containing page views
   *
   * @var string
   */
  protected $_tableViews = Tables::VIEWS;

  /**
   * Table containing page view configurations for the output modes
   *
   * @var string
   */
  protected $_tableViewConfigurations = Tables::VIEW_CONFIGURATIONS;

  /**
   * This defines if pages are only loaded, if they have a translation in the given language.
   *
   * @var bool
   */
  private $_translationNeeded;

  /**
   * Define if a translation is needed, or pages without translations are loaded, too.
   *
   * @param bool $translationNeeded
   */
  public function __construct($translationNeeded = FALSE) {
    $this->_translationNeeded = (bool)$translationNeeded;
  }

  /**
   * Load pages defined by filter conditions.
   *
   * @param array $filter
   * @param null|int $limit
   * @param null|int $offset
   *
   * @return bool
   */
  public function load($filter = [], $limit = NULL, $offset = NULL) {
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
    $sql = /* @lang TEXT */
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
              ".Utility\Text::escapeForPrintf($this->_compileCondition($filter)).'
              '.Utility\Text::escapeForPrintf($this->_compileOrderBy());
    $parameters = [
      $databaseAccess->getTableName($this->_tablePages),
      $databaseAccess->getTableName($this->_tablePageTranslations),
      $languageId,
      $databaseAccess->getTableName($this->_tablePagePublications),
      $databaseAccess->getTableName($this->_tableViews),
      $databaseAccess->getTableName($this->_tableViewConfigurations),
      $viewModeId,
      $databaseAccess->getTableName($this->_tableAuthenticationUsers)
    ];
    return $this->_loadRecords($sql, $parameters, $limit, $offset, 'id');
  }

  protected function _compileCondition($filter, $prefix = ' WHERE ') {
    $statusConditions = [
      'modified' => 't.topic_modified > tp.topic_modified',
      'published' => 't.topic_modified <= tp.topic_modified',
      'created' => 'tp.topic_modified IS NULL'
    ];
    $conditions = '';
    if (isset($filter['status'], $statusConditions[$filter['status']])) {
      $conditions .= $prefix.' '.$statusConditions[$filter['status']];
      $prefix = ' AND ';
    }
    if (isset($filter['ancestor_id']) && $filter['ancestor_id'] > 0) {
      $ancestorFilter = new Database\Condition\Group($this);
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
   * @return Database\Interfaces\Mapping
   */
  protected function _createMapping() {
    $mapping = parent::_createMapping();
    $mapping->callbacks()->onMapValue = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $mode, $property, $field, $value
    ) {
      if ('path' === $property) {
        return Database\Record\Mapping::FIELD_TO_PROPERTY === $mode
          ? Utility\Arrays::decodeIdList($value)
          : ';'.Utility\Arrays::encodeIdList($value).';';
      }
      return $value;
    };
    return $mapping;
  }

  /**
   * @return bool
   */
  public function isPublic() {
    return FALSE;
  }
}
