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

namespace Papaya\Administration\Pages\Dependency\Synchronization;

use \Papaya\Administration;
use \Papaya\Content;

/**
 * Synchronize box inheritance on the page workling copy and the page links
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Boxes
  implements Administration\Pages\Dependency\Synchronization {

  /**
   * Page boxes list database object
   *
   * @var Boxes
   */
  private $_boxes;

  /**
   * Page working copy object
   *
   * @var Content\Page\Work
   */
  private $_page;

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
   * @return bool
   */
  private function setInheritanceStatus(array $targetIds, $status) {
    $databaseAccess = $this->page()->getDatabaseAccess();
    $filter = $databaseAccess->getSqlCondition(['topic_id' => $targetIds]);
    $sql = "UPDATE %s SET box_useparent = '%d' WHERE $filter";
    $parameters = [
      $databaseAccess->getTableName(Content\Tables::PAGES),
      $status
    ];
    return FALSE !== $databaseAccess->queryFmtWrite($sql, $parameters);
  }

  /**
   * Getter/Setter for the  page boxes list database object
   *
   * @param Content\Page\Boxes $boxes
   * @return Content\Page\Boxes
   */
  public function boxes(Content\Page\Boxes $boxes = NULL) {
    if (NULL !== $boxes) {
      $this->_boxes = $boxes;
    } elseif (NULL === $this->_boxes) {
      $this->_boxes = new Content\Page\Boxes();
    }
    return $this->_boxes;
  }

  /**
   * Getter/Setter for the page working copy
   *
   * @param Content\Page\Work $page
   * @return Content\Page\Work
   */
  public function page(Content\Page\Work $page = NULL) {
    if (NULL !== $page) {
      $this->_page = $page;
    } elseif (NULL === $this->_page) {
      $this->_page = new Content\Page\Work();
    }
    return $this->_page;
  }
}
