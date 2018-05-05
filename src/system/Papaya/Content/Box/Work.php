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
* Provide data encapsulation for the content box working copy.
*
* Allows to load/save/publish the working copy of a box.
* It contains no validation, only the database access encapsulation.
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
* @property integer $unpublishedTranslations internal counter for unpublished translations
*/
class PapayaContentBoxWork extends \PapayaContentBox {

  /**
  * Save box to database
  *
  * @return integer|boolean
  */
  public function save() {
    if (empty($this['id'])) {
      $this['created'] = $this['modified'] = time();
    } else {
      $this['modified'] = time();
    }
    return parent::save();
  }

  /**
  * Get a publication encapsulation object
  *
  * @return \PapayaContentBoxPublication
  */
  protected function _createPublicationObject() {
    $publication = new \PapayaContentBoxPublication();
    $publication->setDatabaseAccess($this->getDatabaseAccess());
    return $publication;
  }

  /**
  * Publish the box and it's translations (depending on the $languageIds
  *
  * @param array $languageIds
  * @param integer $publishedFrom
  * @param integer $publishedTo
  * @return boolean
  */
  public function publish(array $languageIds = NULL, $publishedFrom = 0, $publishedTo = 0) {
    if ($this->id > 0) {
      $publication = $this->_createPublicationObject();
      $publication->assign($this);
      $publication->publishedFrom = $publishedFrom;
      $publication->publishedTo = $publishedTo;
      if ($publication->save()) {
        return $this->_publishTranslations($publication, $languageIds);
      }
    }
    return FALSE;
  }

  /**
  * Publish the translations of the given languages.
  *
  * @param \PapayaContentBoxPublication $publication
  * @param array $languageIds
  * @return boolean
  */
  private function _publishTranslations(
    PapayaContentBoxPublication $publication, array $languageIds = NULL
  ) {
    if (!empty($languageIds)) {
      $deleted = $this->databaseDeleteRecord(
        $this->databaseGetTableName(\PapayaContentTables::BOX_PUBLICATION_TRANSLATIONS),
        array(
          'box_id' => $this->id,
          'lng_id' => $languageIds
        )
      );
      if (FALSE !== $deleted) {
        $filter = str_replace('%', '%%', $this->databaseGetSqlCondition('lng_id', $languageIds));
        $now = time();
        $sql = "INSERT INTO %s
                       (box_id, lng_id, box_title, box_data, view_id,
                        box_trans_created, box_trans_modified)
                SELECT t.box_id, t.lng_id, t.box_title, t.box_data, t.view_id,
                       t.box_trans_created, '%d'
                  FROM %s t
                 WHERE t.box_id = %d AND $filter";
        $parameters = array(
          $this->databaseGetTableName(\PapayaContentTables::BOX_PUBLICATION_TRANSLATIONS),
          $now,
          $this->databaseGetTableName(\PapayaContentTables::BOX_TRANSLATIONS),
          $this->id
        );
        if (FALSE !== $this->databaseQueryFmt($sql, $parameters)) {
          $publication->load($this->id);
          $this->unpublishedTranslations =
            count($this->translations()) - count($publication->translations());
          $data = array(
            'box_unpublished_languages' => $this->unpublishedTranslations
          );
          return FALSE !== $this->databaseUpdateRecord(
            $this->databaseGetTableName($this->_tableName), $data, array('box_id' => $this->id)
          );
        }
      }
      return FALSE;
    } else {
      return TRUE;
    }
  }
}
