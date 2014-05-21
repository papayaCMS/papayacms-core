<?php
/**
* Provide data encapsulation for the content box version list.
*
* The list does not contain all detail data, it is for list outputs etc. To get the full data
* use {@see PapayaContentBoxTranslation}.
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
* @version $Id: Versions.php 36028 2011-08-04 10:10:14Z weinert $
*/

/**
* Provide data encapsulation for the content box version list. The versions are created if
* a box is published. They are not changeable.
*
* The list does not contain all detail data, it is for list outputs etc. To get the full data
* use {@see PapayaContentBoxVersion}.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentBoxVersions extends PapayaDatabaseObjectList {

  /**
  * Map field names to value identfiers
  *
  * @var array
  */
  protected $_fieldMapping = array(
    'version_id' => 'id',
    'version_time' => 'created',
    'version_author_id' => 'owner',
    'version_message' => 'message',
    'box_id' => 'box_id'
  );

  /**
  * Version table name
  *
  * @var string
  */
  protected $_versionsTableName = PapayaContentTables::BOX_VERSIONS;

  /**
  * Load version list informations
  *
  * @param integer $boxId
  * @param NULL|integer $limit maximum records returned
  * @param NULL|integer $offset start offset for limited results
  * @return boolean
  */
  public function load($boxId, $limit = NULL, $offset = NULL) {
    $sql = "SELECT version_id, version_time, version_author_id, version_message,
                   box_id
              FROM %s
             WHERE box_id = %d
             ORDER BY version_time DESC";
    $parameters = array(
      $this->databaseGetTableName($this->_versionsTableName),
      (int)$boxId
    );
    return $this->_loadRecords($sql, $parameters, 'version_id', $limit, $offset);
  }

  /**
  * Create a new version record object and load the specified version data
  *
  * @param integer $versionId
  * @return PapayaContentBoxVersion|NULL
  */
  public function getVersion($versionId) {
    $result = new PapayaContentBoxVersion();
    $result->setDatabaseAccess($this->getDatabaseAccess());
    $result->load($versionId);
    return $result;
  }
}