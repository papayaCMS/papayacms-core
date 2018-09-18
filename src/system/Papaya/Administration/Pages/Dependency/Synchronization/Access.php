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

use Papaya\Administration;
use Papaya\Content;

class Access
  implements Administration\Pages\Dependency\Synchronization {
  /**
   * Page database record object
   *
   * @var Content\Page\Work
   */
  private $_page;

  /**
   * Synchronize a dependency
   *
   * @param array $targetIds
   * @param int $originId
   * @param array|null $languages
   * @return bool
   */
  public function synchronize(array $targetIds, $originId, array $languages = NULL) {
    if ($this->page()->load($originId)) {
      return $this->updatePages($this->page(), $targetIds);
    }
    return FALSE;
  }

  /**
   * Getter/Setter for the content page object
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

  /**
   * Update target page permissions
   *
   * @param Content\Page\Work $origin
   * @param array $targetIds
   * @return bool
   */
  protected function updatePages(Content\Page\Work $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
        $databaseAccess->getTableName(Content\Tables::PAGES),
        [
          'topic_modified' => $databaseAccess->getTimestamp(),
          'surfer_useparent' => $origin->inheritVisitorPermissions,
          'surfer_permids' => $origin->visitorPermissions
        ],
        [
          'topic_id' => $targetIds
        ]
      );
  }
}
