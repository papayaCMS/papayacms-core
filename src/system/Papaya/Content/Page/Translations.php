<?php
/**
* Provide data encapsulation for the content page translations list.
*
* The list does not contain all detail data, it is for list outputs etc. To get the full data
* use {@see PapayaContentPageTranslation}.
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
* @version $Id: Translations.php 38481 2013-05-08 13:45:51Z weinert $
*/

/**
* Provide data encapsulation for the content page translations list.
*
* The list does not contain all detail data, it is for list outputs etc. To get the full data
* use {@see PapayaContentPageTranslation}.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentPageTranslations extends PapayaDatabaseObjectList {

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
    'topic_trans_published' => 'published',
    'view_title' => 'view',
  );

  protected $_translationsTableName = PapayaContentTables::PAGE_TRANSLATIONS;

  /**
  * Change the main page table name
  *
  * @param string $tableName
  */
  public function setTranslationsTableName($tableName) {
    PapayaUtilConstraints::assertString($tableName);
    PapayaUtilConstraints::assertNotEmpty($tableName);
    $this->_translationsTableName = $tableName;
  }

  /**
  * Load translation list informations
  *
  * @param integer $pageId
  * @return boolean
  */
  public function load($pageId) {
    $sql = "SELECT tt.topic_id, tt.lng_id, tt.topic_trans_modified,
                   tt.topic_title, ttp.topic_trans_modified as topic_trans_published,
                   v.view_title
              FROM %s tt
              LEFT OUTER JOIN %s ttp
                ON (ttp.topic_id = tt.topic_id AND ttp.lng_id = tt.lng_id)
              LEFT OUTER JOIN %s v ON (v.view_id = tt.view_id)
             WHERE tt.topic_id = %d";
    $parameters = array(
      $this->databaseGetTableName($this->_translationsTableName),
      $this->databaseGetTableName(PapayaContentTables::PAGE_PUBLICATION_TRANSLATIONS),
      $this->databaseGetTableName(PapayaContentTables::VIEWS),
      (int)$pageId
    );
    return $this->_loadRecords($sql, $parameters, 'lng_id');
  }

  /**
  * Get a detail object for a single translation to edit it.
  *
  * @param integer $pageId
  * @param integer $languageId
  * @return PapayaContentPageTranslation
  */
  public function getTranslation($pageId, $languageId) {
    $result = new PapayaContentPageTranslation();
    $result->setDatabaseAccess($this->getDatabaseAccess());
    $result->activateLazyLoad(array('id' => $pageId, 'language_id' => $languageId));
    return $result;
  }
}