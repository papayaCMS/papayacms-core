<?php
/**
* Data encapsulation for a liust of domain groups
*
* @copyright 2014 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Groups.php 39695 2014-03-26 14:21:02Z weinert $
*/

/**
* Data encapsulation for a liust of domain groups
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentDomainGroups extends PapayaDatabaseRecordsLazy {

  /**
  * Map field names to more convinient property names
  *
  * @var array:string
  */
  protected $_fields = array(
    'id' => 'domaingroup_id',
    'title' => 'domaingroup_title'
  );

  /**
  * @var string
  */
  protected $_tableName = PapayaContentTables::DOMAIN_GROUPS;

  protected $_identifierProperties = array('id');

  /**
   * @param int $id
   * @return PapayaContentDomainGroup
   */
  public function getItem($id) {
    $result = new PapayaContentDomainGroup();
    $result->activateLazyLoad(array('id' => $id));
    return $result;
  }
}