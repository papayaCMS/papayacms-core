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
* Synchronize a publication to assigned target page. This is done duplicating the publish action.
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesDependencySynchronizationPublication
  implements PapayaAdministrationPagesDependencySynchronization {

  private $_publication = NULL;
  private $_page = NULL;
  private $_version = NULL;

  /**
   * Synchronize a dependency, publish target pages
   *
   * @param array $targetIds
   * @param integer $originId
   * @param array|NULL $languages
   * @return bool
   */
  public function synchronize(array $targetIds, $originId, array $languages = NULL) {
    $loaded = $this->publication()->load($originId);
    if ($loaded && ($information = $this->getVersionData($originId))) {
      $publishedFrom = $this->publication()->publishedFrom;
      $publishedTo = $this->publication()->publishedTo;
      foreach ($targetIds as $targetId) {
        if ($this->page()->load($targetId)) {
          $this->version()->id = NULL;
          $this->version()->pageId = $targetId;
          $this->version()->assign($information);
          $this->version()->save();
          $this->page()->publish($languages, $publishedFrom, $publishedTo);
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Getter/Setter for publication page object. This is used to validate the origin
  * and fetch the publication period limits
  *
  * @param PapayaContentPagePublication $publication
  * @return PapayaContentPagePublication
  */
  public function publication(PapayaContentPagePublication $publication = NULL) {
    if (isset($publication)) {
      $this->_publication = $publication;
    } elseif (is_null($this->_publication)) {
      $this->_publication = new \PapayaContentPagePublication();
    }
    return $this->_publication;
  }

  /**
  * Getter/Setter for working copy page object. This is used to publish the target pages.
  *
  * @param PapayaContentPageWork $page
  * @return PapayaContentPageWork
  */
  public function page(PapayaContentPageWork $page = NULL) {
    if (isset($page)) {
      $this->_page = $page;
    } elseif (is_null($this->_page)) {
      $this->_page = new \PapayaContentPageWork();
    }
    return $this->_page;
  }

  /**
   * Getter/Setter for a page version object. This is used to create version for the target pages.
   *
   * @param PapayaContentPageVersion $version
   * @return PapayaContentPageVersion
   */
  public function version(PapayaContentPageVersion $version = NULL) {
    if (isset($version)) {
      $this->_version = $version;
    } elseif (is_null($this->_version)) {
      $this->_version = new \PapayaContentPageVersion();
    }
    return $this->_version;
  }

  /**
  * Fetch the needed version data (owner, message, change level).
  *
  * @param integer $pageId
  * @return array
  */
  private function getVersionData($pageId) {
    $databaseAccess = $this->publication()->getDatabaseAccess();
    $sql = "SELECT version_author_id, version_message, topic_change_level
              FROM %s
             WHERE topic_id = '%d'
             ORDER BY version_time DESC";
    $parameters = array(
      $databaseAccess->getTableName(PapayaContentTables::PAGE_VERSIONS),
      $pageId
    );
    if (($databaseResult = $databaseAccess->queryFmt($sql, $parameters, 1)) &&
        ($row = $databaseResult->fetchRow(PapayaDatabaseResult::FETCH_ASSOC))) {
      return array(
        'owner' => $row['version_author_id'],
        'message' => $row['version_message'],
        'level' => $row['topic_change_level']
      );
    }
    return FALSE;
  }
}
