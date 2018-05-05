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
* Provide data encapsulation for the content box publication.
*
* @package Papaya-Library
* @subpackage Content
*
* @property integer $id box id
* @property integer $groupId box group id
* @property string $name administration interface box name
* @property integer $created box creation timestamp
* @property integer $modified last modification timestamp
* @property integer $cacheMode box content cache mode (system, none, own)
* @property integer $cacheTime box content cache time, if mode == own
* @property integer $publishedFrom publication time limit - start
* @property integer $publishedTo publication time limit - end
*/
class PapayaContentBoxPublication extends \PapayaContentBox {

  /**
  * Map properties to database fields
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    // page id
    'id' => 'box_id',
    // parent id
    'group_id' => 'boxgroup_id',
    // name for administration interface
    'name' => 'box_name',
    // creation / modification timestamps
    'created' => 'box_created',
    'modified' => 'box_modified',
    // delivery mode for box (static, esi, js)
    'delivery_mode' => 'box_deliverymode',
    // server side content caching
    'cache_mode' => 'box_cachemode',
    'cache_time' => 'box_cachetime',
    // browser/proxy caching
    'expires_mode' => 'box_expiresmode',
    'expires_time' => 'box_expirestime',
    //publication period
    'published_from' => 'box_public_from',
    'published_to' => 'box_public_to'
  );

  protected $_tableName = \PapayaContentTables::BOX_PUBLICATIONS;

  public function save() {
    if ($this->id > 0) {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE box_id = %d";
      $parameters = array(
        $this->databaseGetTableName($this->_tableName),
        $this->id
      );
      if ($res = $this->databaseQueryFmt($sql, $parameters)) {
        $this->modified = time();
        if ($res->fetchField() > 0) {
          return $this->_updateRecord(
            $this->databaseGetTableName($this->_tableName),
            array('box_id' => $this->id)
          );
        } else {
          $this->created = $this->modified;
          return $this->_insertRecord(
            $this->databaseGetTableName($this->_tableName)
          );
        }
      }
    }
    return FALSE;
  }
}
