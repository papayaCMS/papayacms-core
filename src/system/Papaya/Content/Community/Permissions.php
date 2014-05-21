<?php
/**
* Provide data encapsulation for the surfer permission records.
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
* @version $Id: Permissions.php 36697 2012-02-01 11:36:44Z weinert $
*/

/**
* Provide data encapsulation for the surfer permission records.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentCommunityPermissions extends PapayaDatabaseRecords {

  protected $_fields = array(
    'id' => 'surferperm_id',
    'title' => 'surferperm_title',
    'active' => 'surferperm_active'
  );

  protected $_orderByFields = array(
    'surferperm_title' => PapayaDatabaseInterfaceOrder::ASCENDING,
    'surferperm_id' => PapayaDatabaseInterfaceOrder::ASCENDING
  );

  protected $_identifierProperties = 'id';

  protected $_tableName = PapayaContentTables::COMMUNITY_PERMISSIONS;

}