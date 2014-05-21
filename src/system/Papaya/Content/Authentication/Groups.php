<?php
/**
* Provide data encapsulation for the administration user group records.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Groups.php 37149 2012-06-22 14:36:00Z weinert $
*/

/**
* Provide data encapsulation for the administration user group records.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentAuthenticationGroups extends PapayaDatabaseRecordsLazy {

  protected $_fields = array(
    'id' => 'group_id',
    'title' => 'grouptitle'
  );

  protected $_orderByFields = array(
    'grouptitle' => PapayaDatabaseInterfaceOrder::ASCENDING,
    'group_id' => PapayaDatabaseInterfaceOrder::ASCENDING
  );

  protected $_identifierProperties = 'id';

  protected $_tableName = PapayaContentTables::AUTHENTICATION_GROUPS;
}