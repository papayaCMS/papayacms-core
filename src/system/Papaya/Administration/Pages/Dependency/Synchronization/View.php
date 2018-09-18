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

use Papaya\Content\Page;

/**
 * Synchronize view of the page working copy
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class View
  extends Content {
  /**
   * Update content data of existing translations
   *
   * @param Page\Translation $origin
   * @param array $targetIds
   * @return bool
   */
  protected function updateTranslations(Page\Translation $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
        $databaseAccess->getTableName(\Papaya\Content\Tables::PAGE_TRANSLATIONS),
        [
          'view_id' => $origin->viewId,
          'topic_trans_modified' => $origin->modified
        ],
        [
          'lng_id' => $origin->languageId,
          'topic_id' => $targetIds
        ]
      );
  }
}
