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
use Papaya\Content\Page\Work;

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

class Access
  implements \Papaya\Administration\Pages\Dependency\Synchronization {

  /**
   * Page database record object
   *
   * @var Work
   */
  private $_page = NULL;

  /**
   * Synchronize a dependency
   *
   * @param array $targetIds
   * @param integer $originId
   * @param array|NULL $languages
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
   * @param \Papaya\Content\Page\Work $page
   * @return \Papaya\Content\Page\Work
   */
  public function page(\Papaya\Content\Page\Work $page = NULL) {
    if (isset($page)) {
      $this->_page = $page;
    } elseif (is_null($this->_page)) {
      $this->_page = new \Papaya\Content\Page\Work();
    }
    return $this->_page;
  }

  /**
   * Update target page permissions
   *
   * @param \Papaya\Content\Page\Work $origin
   * @param array $targetIds
   * @return boolean
   */
  protected function updatePages(\Papaya\Content\Page\Work $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
        $databaseAccess->getTableName(\PapayaContentTables::PAGES),
        array(
          'topic_modified' => $databaseAccess->getTimestamp(),
          'surfer_useparent' => $origin->inheritVisitorPermissions,
          'surfer_permids' => $origin->visitorPermissions
        ),
        array(
          'topic_id' => $targetIds
        )
      );
  }
}
