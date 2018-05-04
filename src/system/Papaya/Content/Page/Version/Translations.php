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
* Provide data encapsulation for the content page version translations list.
*
* The list does not contain all detail data, it is for list outputs etc. To get the full data
* use {@see PapayaContentPageVersionTranslation}.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentPageVersionTranslations extends PapayaDatabaseObjectList {


  /**
  * Map field names to value identfiers
  *
  * @var array
  */
  protected $_fieldMapping = array(
    'topic_id' => 'id',
    'lng_id' => 'language_id',
    'topic_title' => 'title',
    'topic_trans_modified' => 'modified',
    'view_title' => 'view',
  );

  protected $_translationsTableName = \PapayaContentTables::PAGE_VERSION_TRANSLATIONS;

  /**
  * Load translation list informations
  *
  * @param integer $pageId
  * @return boolean
  */
  public function load($pageId) {
    $sql = "SELECT tt.topic_id, tt.lng_id, tt.topic_trans_modified,
                   tt.topic_title,
                   v.view_title
              FROM %s tt
              LEFT OUTER JOIN %s v ON (v.view_id = tt.view_id)
             WHERE tt.topic_id = %d";
    $parameters = array(
      $this->databaseGetTableName($this->_translationsTableName),
      $this->databaseGetTableName(\PapayaContentTables::VIEWS),
      (int)$pageId
    );
    return $this->_loadRecords($sql, $parameters, 'lng_id');
  }

  /**
  * Get a detail object for a single translation.
  *
  * @param integer $pageId
  * @param integer $languageId
  * @return \PapayaContentPageTranslation
  */
  public function getTranslation($pageId, $languageId) {
    $result = new \PapayaContentPageVersionTranslation();
    $result->setDatabaseAccess($this->getDatabaseAccess());
    $result->activateLazyLoad(array('id' => $pageId, 'language_id' => $languageId));
    return $result;
  }
}
