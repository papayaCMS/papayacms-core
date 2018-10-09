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

/**
 * This object loads view records into a list.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Views extends Database\Records\Lazy {
  /**
   * Map field names to more convenient property names
   *
   * @var string[]
   */
  protected $_fields = [
    'id' => 'v.view_id',
    'title' => 'v.view_title',
    'name' => 'v.view_name',
    'module_id' => 'v.module_guid',
    'module_type' => 'm.module_type',
    'checksum' => 'v.view_checksum'
  ];

  /**
   * Table containing view information
   *
   * @var string
   */
  protected $_tableName = Tables::VIEWS;

  /**
   * Table containing module information
   *
   * @var string
   */
  protected $_tableNameModules = Tables::MODULES;

  protected $_orderByProperties = [
    'title' => Database\Interfaces\Order::ASCENDING,
    'id' => Database\Interfaces\Order::ASCENDING
  ];

  /**
   * Load view records
   *
   * @param array $filter
   * @param null|int $limit
   * @param null|int $offset
   *
   * @return bool
   */
  public function load($filter = [], $limit = NULL, $offset = NULL) {
    $databaseAccess = $this->getDatabaseAccess();
    $fields = \implode(', ', $this->mapping()->getFields());
    $sql = "SELECT $fields
              FROM %s AS v
              JOIN %s AS m ON (m.module_guid = v.module_guid)
              ".$this->_compileCondition($filter).'
              '.$this->_compileOrderBy();
    $parameters = [
      $databaseAccess->getTableName($this->_tableName),
      $databaseAccess->getTableName($this->_tableNameModules)
    ];
    return $this->_loadRecords($sql, $parameters, $limit, $offset, ['id']);
  }
}
