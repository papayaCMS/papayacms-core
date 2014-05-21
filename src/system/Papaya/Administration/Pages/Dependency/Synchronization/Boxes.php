<?php
/**
* Synchronize box inheritance on the page workling copy and the page links
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Administration
* @version $Id: Boxes.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Synchronize box inheritance on the page workling copy and the page links
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesDependencySynchronizationBoxes
  implements PapayaAdministrationPagesDependencySynchronization {

  /**
  * Page boxes list database object
  *
  * @var PapayaContentPageBoxes
  */
  private $_boxes = NULL;

  /**
  * Page working copy object
  *
  * @var PapayaContentPageWork
  */
  private $_page = NULL;

  /**
  * Synchronize a dependency
  *
  * @param array $targetIds
  * @param integer $originId
  * @param array|NULL $languages
   */
  public function synchronize(array $targetIds, $originId, array $languages = NULL) {
    if ($this->page()->load($originId)) {
      $this->boxes()->load($originId);
      $this->boxes()->copyTo($targetIds);
      $this->setInheritanceStatus($targetIds, $this->page()->inheritBoxes);
    }
  }

  /**
   * Sets the box inheritance status on the given target pages
   *
   * @param array $targetIds
   * @param int $status
   * @return PapayaContentPageWork
   */
  private function setInheritanceStatus(array $targetIds, $status) {
    $databaseAccess = $this->page()->getDatabaseAccess();
    $filter = $databaseAccess->getSqlCondition('topic_id', $targetIds);
    $sql = "UPDATE %s SET box_useparent = '%d' WHERE $filter";
    $parameters = array(
      $databaseAccess->getTableName(PapayaContentTables::PAGES),
      $status
    );
    return FALSE !== $databaseAccess->queryFmtWrite($sql, $parameters);
  }

  /**
  * Getter/Setter for the  page boxes list database object
  *
  * @param PapayaContentPageBoxes $boxes
  * @return PapayaContentPageBoxes
  */
  public function boxes(PapayaContentPageBoxes $boxes = NULL) {
    if (isset($boxes)) {
      $this->_boxes = $boxes;
    } elseif (is_null($this->_boxes)) {
      $this->_boxes = new PapayaContentPageBoxes();
    }
    return $this->_boxes;
  }

  /**
  * Getter/Setter for the page working copy
  *
  * @param PapayaContentPageWork $page
  * @return PapayaContentPageWork
  */
  public function page(PapayaContentPageWork $page = NULL) {
    if (isset($page)) {
      $this->_page = $page;
    } elseif (is_null($this->_page)) {
      $this->_page = new PapayaContentPageWork();
    }
    return $this->_page;
  }
}