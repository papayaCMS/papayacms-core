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
* List object to handle a collection of record objects, allows to save, delete all of them
* with one method call
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: List.php 39429 2014-02-27 20:14:26Z weinert $
*/
class PapayaDatabaseRecordList
  extends \PapayaObjectList {

  /**
   * Create list an set internal object type limitation
   */
  public function __construct() {
    parent::__construct(\PapayaDatabaseInterfaceRecord::class);
  }

  /**
   * Convert the list of records into an array, unlike in the iterator this will
   * not return an array of record objects, but an array of arrays.
   *
   * @return array
   */
  public function toArray() {
    $result = array();
    /** @var \PapayaDatabaseInterfaceRecord $record */
    foreach ($this as $record) {
      $result[] = $record->toArray();
    }
    return $result;
  }

  /**
   * Save all records, break on database error
   * @return bool
   */
  public function save() {
    /** @var \PapayaDatabaseInterfaceRecord $record */
    foreach ($this as $record) {
      if (FALSE === $record->save()) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Delete all records, break on database error
   * @return bool
   */
  public function delete() {
    /** @var \PapayaDatabaseInterfaceRecord $record */
    foreach ($this as $record) {
      if (FALSE === $record->delete()) {
        return FALSE;
      }
    }
    return TRUE;
  }
}
