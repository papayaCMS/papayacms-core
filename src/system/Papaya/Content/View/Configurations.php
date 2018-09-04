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

namespace Papaya\Content\View;
/**
 * This object loads view records into a list.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Configurations extends \Papaya\Database\Records\Lazy {

  /**
   * Map field names to more convenient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    'id' => 'vl.view_id',
    'mode_id' => 'viewmode_id',
    'module_guid' => 'module_guid',
    'type' => 'm.module_type',
    'options' => 'viewlink_data'
  );

  /**
   * Table containing view informations
   *
   * @var string
   */
  protected $_tableName = \Papaya\Content\Tables::VIEW_CONFIGURATIONS;

  protected $_identifierProperties = ['id', 'mode_id', 'type'];

  /**
   * @param array|string|int $filter
   * @param int|null $limit
   * @param int|null $offset
   * @return bool
   */
  public function load($filter = array(), $limit = NULL, $offset = NULL) {
    $databaseAccess = $this->getDatabaseAccess();
    $prefix = " WHERE ";
    if (isset($filter['mode_id'])) {
      $conditionOutput = sprintf(' WHERE vl.viewmode_id = %d', $filter['mode_id']);
      $conditionData = sprintf(' WHERE vl.datafilter_id = %d', $filter['mode_id']);
      unset($filter['mode_id']);
      $prefix = '';
    } else {
      $conditionOutput = $conditionData = '';
    }
    $conditionOutput = \Papaya\Utility\Text::escapeForPrintf(
      $conditionOutput.$this->_compileCondition($filter, $prefix)
    );
    $conditionData = \Papaya\Utility\Text::escapeForPrintf(
      $conditionData.$this->_compileCondition($filter, $prefix)
    );
    $sql = "SELECT vl.view_id,
                    vl.viewmode_id,
                    vl.viewlink_data,
                    m.module_guid,
                    m.module_type
               FROM %s vl
               JOIN %s vm ON (vm.viewmode_id = vl.viewmode_id)
               JOIN %s m ON (m.module_guid = vm.module_guid)
               $conditionOutput
            UNION
            SELECT vl.view_id,
                    vl.datafilter_id viewmode_id,
                    vl.datafilter_data viewlink_data,
                    m.module_guid,
                    m.module_type
               FROM %s vl
               JOIN %s vm ON (vm.datafilter_id = vl.datafilter_id)
               JOIN %s m ON (m.module_guid = vm.module_guid)
               $conditionData";
    $parameters = array(
      $databaseAccess->getTableName(\Papaya\Content\Tables::VIEW_CONFIGURATIONS),
      $databaseAccess->getTableName(\Papaya\Content\Tables::VIEW_MODES),
      $databaseAccess->getTableName(\Papaya\Content\Tables::MODULES),
      $databaseAccess->getTableName(\Papaya\Content\Tables::VIEW_DATAFILTER_CONFIGURATIONS),
      $databaseAccess->getTableName(\Papaya\Content\Tables::VIEW_DATAFILTERS),
      $databaseAccess->getTableName(\Papaya\Content\Tables::MODULES)
    );
    return parent::_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }
}
