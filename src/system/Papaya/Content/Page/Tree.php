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

namespace Papaya\Content\Page {

  use Papaya\Content\Tables as ContentTables;
  use Papaya\Database\Interfaces\Order as DatabaseRecordOrder;
  use Papaya\Database\Records\Tree as RecordsTree;

  class Tree extends RecordsTree {

    protected $_fields = [
      'id' => 'pages.topic_id',
      'parent_id' => 'pages.prev',
      'ancestors' => 'pages.prev_path',
      'position' => 'pages.topic_weight',
      'language_id' => 'translations.lng_id',
      'created' => 'pages.topic_created',
      'modified' => 'pages.topic_modified',
      'title' => 'translations.topic_title',
      'view_name' => 'view.view_name',
      'module_guid'=> 'view.module_guid',
      'scheme' => 'pages.topic_protocol',
      'change_frequency' => 'pages.topic_changefreq',
      'priority' => 'pages.topic_priority',
      'link_type_id' => 'pages.linktype_id'
    ];

    protected $_orderByProperties = [
      'position' => DatabaseRecordOrder::ASCENDING,
      'created' => DatabaseRecordOrder::ASCENDING
    ];

    private $_isPreview;

    public function __construct($isPreview) {
      $this->_isPreview = $isPreview;
    }

    public function load($filter = [], $limit = NULL, $offset = NULL) {
      $syntax = $this->getDatabaseAccess()->syntax();
      $depthCondition = '';
      if (isset($filter['maximum-depth'])) {
        $depthCondition = ' AND ('.
          $syntax->substringCount($syntax->identifier('pages.prev_path'), ';').
          ') < '.($filter['maximum-depth'] + 2);
        unset($filter['maximum-depth']);
      }
      $statement = $this->getDatabaseAccess()->prepare(
        'SELECT 
          pages.topic_id, 
          translations.topic_title, 
          translations.view_id, 
          views.view_name, views.module_guid,
          pages.prev, pages.prev_path, 
          pages.topic_weight, pages.is_deleted,
          pages.topic_created, pages.topic_modified,
          pages.topic_protocol, 
          pages.topic_changefreq, pages.topic_priority,
          pages.linktype_id
        FROM :pages pages, :translations translations
        LEFT OUTER JOIN :views views ON (views.view_id = translations.view_id)
        WHERE translations.topic_id = pages.topic_id'.$depthCondition.
        $this->_compileCondition($filter, ' AND ').$this->_compileOrderBy()
      );
      $statement->addTableName(
        'pages',
        $this->_isPreview ? ContentTables::PAGES : ContentTables::PAGE_PUBLICATIONS
      );
      $statement->addTableName(
        'translations',
        $this->_isPreview ? ContentTables::PAGE_TRANSLATIONS : ContentTables::PAGE_PUBLICATION_TRANSLATIONS
      );
      $statement->addTableName('views', ContentTables::VIEWS);
      return $this->_loadRecords($statement, [], $limit, $offset, $this->_identifierProperties);
    }
  }
}


