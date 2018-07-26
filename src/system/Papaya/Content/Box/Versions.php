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

namespace Papaya\Content\Box;
/**
 * Provide data encapsulation for the content box version list. The versions are created if
 * a box is published. They are not changeable.
 *
 * The list does not contain all detail data, it is for list outputs etc. To get the full data
 * use {@see \Papaya\Content\Box\PapayaContentBoxVersion}.
 *
 * @package Papaya-Library
 * @subpackage Content
 */
class Versions extends \Papaya\Database\BaseObject\Records {

  /**
   * Map field names to value identifiers
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
  protected $_versionsTableName = \Papaya\Content\Tables::BOX_VERSIONS;

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
   * @return \Papaya\Content\Box\Version|NULL
   */
  public function getVersion($versionId) {
    $result = new \Papaya\Content\Box\Version();
    $result->setDatabaseAccess($this->getDatabaseAccess());
    $result->load($versionId);
    return $result;
  }
}
