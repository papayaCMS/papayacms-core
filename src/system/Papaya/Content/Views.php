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
* This object loads view records into a list.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentViews extends PapayaDatabaseRecordsLazy {

  /**
  * Map field names to more convinient property names
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    'id' => 'v.view_id',
    'title' => 'v.view_title',
    'name' => 'v.view_name',
    'module_id' => 'v.module_guid',
    'module_type' => 'm.module_type',
    'checksum' => 'v.view_checksum'
  );

  /**
  * Table containing view informations
  *
  * @var string
  */
  protected $_tableName = PapayaContentTables::VIEWS;

  /**
  * Table containing module informations
  *
  * @var string
  */
  protected $_tableNameModules = PapayaContentTables::MODULES;

  protected $_orderByProperties = array(
    'title' => PapayaDatabaseInterfaceOrder::ASCENDING,
    'id' => PapayaDatabaseInterfaceOrder::ASCENDING
  );

  /**
  * Load view records
  *
  * @param array $filter
  * @param NULL|integer $limit
  * @param NULL|integer $offset
  * @return boolean
  */
  public function load($filter = array(), $limit = NULL, $offset = NULL) {
    $databaseAccess = $this->getDatabaseAccess();
    $fields = implode(', ', $this->mapping()->getFields());
    $sql = "SELECT $fields
              FROM %s AS v
              JOIN %s AS m ON (m.module_guid = v.module_guid)
              ".$this->_compileCondition($filter)."
              ".$this->_compileOrderBy();
    $parameters = array(
      $databaseAccess->getTableName($this->_tableName),
      $databaseAccess->getTableName($this->_tableNameModules)
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, 'id');
  }
}
