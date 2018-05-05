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
* This object loads the available theme sets into a list.
*
* Theme sets are a group of dynamic values that are replaces in the result CSS
* if the wrapper is used.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentThemeSets extends \PapayaDatabaseRecordsLazy {

  /**
  * Map field names to more convinient property names
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    'id' => 'themeset_id',
    'title' => 'themeset_title',
    'theme' => 'theme_name'
  );

  protected $_identifierProperties = array('id');

  protected $_orderByFields = array(
    'themeset_title' => \PapayaDatabaseInterfaceOrder::ASCENDING
  );

  /**
  * Table containing view informations
  *
  * @var string
  */
  protected $_tableName = \PapayaContentTables::THEME_SETS;

}
