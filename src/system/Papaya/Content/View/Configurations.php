<?php
/**
* This object loads view records into a list.
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
* @version $Id: Configurations.php 38488 2013-05-14 10:03:48Z weinert $
*/

/**
* This object loads view records into a list.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentViewConfigurations extends PapayaDatabaseRecordsLazy {

  /**
  * Map field names to more convinient property names
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
  protected $_tableName = PapayaContentTables::VIEW_CONFIGURATIONS;

  public function load($filter, $limit = NULL, $offset = NULL) {
    $databaseAccess = $this->getDatabaseAccess();
    $filter = PapayaUtilString::escapeForPrintf($this->_compileCondition($filter));
    $sql = "(SELECT vl.view_id,
                    vl.viewmode_id,
                    vl.viewlink_data,
                    m.module_guid,
                    m.module_type
               FROM %s vl
               JOIN %s vm ON (vm.viewmode_id = vl.viewmode_id)
               JOIN %s m ON (m.module_guid = vm.module_guid)
               $filter)
            UNION
            (SELECT vl.view_id,
                    vl.datafilter_id viewmode_id,
                    vl.datafilter_data viewlink_data,
                    m.module_guid,
                    m.module_type
               FROM %s vl
               JOIN %s vm ON (vm.datafilter_id = vl.datafilter_id)
               JOIN %s m ON (m.module_guid = vm.module_guid)
               $filter)";
    $parameters = array(
      $databaseAccess->getTableName(PapayaContentTables::VIEW_CONFIGURATIONS),
      $databaseAccess->getTableName(PapayaContentTables::VIEW_MODES),
      $databaseAccess->getTableName(PapayaContentTables::MODULES),
      $databaseAccess->getTableName(PapayaContentTables::VIEW_DATAFILTER_CONFIGURATIONS),
      $databaseAccess->getTableName(PapayaContentTables::VIEW_DATAFILTERS),
      $databaseAccess->getTableName(PapayaContentTables::MODULES)
    );
    return parent::_loadRecords($sql, $parameters, $limit, $offset);
  }
}
