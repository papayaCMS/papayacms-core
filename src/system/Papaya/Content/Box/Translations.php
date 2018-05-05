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
* Provide data encapsulation for the content box translations list.
*
* The list does not contain all detail data, it is for list outputs etc. To get the full data
* use {@see PapayaContentBoxTranslation}.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentBoxTranslations extends \PapayaDatabaseObjectList {

  /**
  * Map field names to value identfiers
  *
  * @var array
  */
  protected $_fieldMapping = array(
    'box_id' => 'id',
    'lng_id' => 'language_id',
    'box_title' => 'title',
    'box_trans_modified' => 'modified',
    'box_trans_published' => 'published',
    'view_title' => 'view'
  );

  /**
   * Load translation list informations
   *
   * @param int $boxId
   * @return boolean
   */
  public function load($boxId) {
    $sql = "SELECT tt.box_id, tt.lng_id, tt.box_trans_modified,
                   tt.box_title, ttp.box_trans_modified as box_trans_published,
                   v.view_title
              FROM %s tt
              LEFT OUTER JOIN %s ttp
                ON (ttp.box_id = tt.box_id AND ttp.lng_id = tt.lng_id)
              LEFT OUTER JOIN %s v ON (v.view_id = tt.view_id)
             WHERE tt.box_id = %d";
    $parameters = array(
      $this->databaseGetTableName(\PapayaContentTables::BOX_TRANSLATIONS),
      $this->databaseGetTableName(\PapayaContentTables::BOX_PUBLICATION_TRANSLATIONS),
      $this->databaseGetTableName(\PapayaContentTables::VIEWS),
      (int)$boxId
    );
    return $this->_loadRecords($sql, $parameters, 'lng_id');
  }

  /**
   * Get a detail object for a single translation to edit it.
   *
   * @param int $boxId
   * @param integer $languageId
   * @internal param int $pageId
   * @return \PapayaContentBoxTranslation
   */
  public function getTranslation($boxId, $languageId) {
    $result = new \PapayaContentBoxTranslation();
    $result->setDatabaseAccess($this->getDatabaseAccess());
    $result->load(array($boxId, $languageId));
    return $result;
  }
}
