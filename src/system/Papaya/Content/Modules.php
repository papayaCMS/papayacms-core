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
* This object loads module/plugin records into a list.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentModules extends PapayaDatabaseRecordsLazy {

  /**
  * Map field names to more convinient property names
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    'id' => 'module_guid',
    'type' => 'module_type',
    'group_id' => 'modulegroup_id',
    'title' => 'module_title',
    'image' => 'module_glyph',
    'description' => 'module_description',
    'path' => 'module_path',
    'file' => 'module_file',
    'class' => 'module_class',
    'use_filter' => 'module_useoutputfilter',
    'is_active' => 'module_active',
    'title_original' => 'module_title_org'
  );

  /**
  * Table containing module/plugin informations
  *
  * @var string
  */
  protected $_tableName = \PapayaContentTables::MODULES;

  protected $_orderByProperties = array(
    'title' => \PapayaDatabaseInterfaceOrder::ASCENDING,
    'title_original' => \PapayaDatabaseInterfaceOrder::ASCENDING,
    'id' => \PapayaDatabaseInterfaceOrder::ASCENDING
  );

  protected $_identifierProperties = array('id');
}
