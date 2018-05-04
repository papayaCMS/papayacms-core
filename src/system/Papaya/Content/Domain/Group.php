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
* Data encapsulation for a liust of domain group record
*
* @package Papaya-Library
* @subpackage Content
*
* @property int $id
* @property string $title
*/
class PapayaContentDomainGroup extends PapayaDatabaseRecordLazy {

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
  protected $_tableName = \PapayaContentTables::DOMAIN_GROUPS;

  /**
   * Create callbacks subobject, override to assign callbacks
   * @return \PapayaDatabaseRecordCallbacks
   */
  protected function _createCallbacks() {
    $callbacks = parent::_createCallbacks();
    $callbacks->onBeforeDelete = array($this, 'moveDomainsToDefaultGroup');
    return $callbacks;
  }

  public function moveDomainsToDefaultGroup() {
    if ($this->id > 0) {
      $databaseAccess = $this->getDatabaseAccess();
      return FALSE !== $databaseAccess->updateRecord(
        $databaseAccess->getTableName(\PapayaContentTables::DOMAINS),
        array('domaingroup_id' => 0),
        array('domaingroup_id' => $this->id)
      );
    }
    return TRUE;
  }
}
