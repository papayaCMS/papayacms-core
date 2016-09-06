<?php
/**
* Synchronize view of the page working copy
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
* @version $Id: View.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Synchronize view of the page working copy
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPagesDependencySynchronizationView
  extends PapayaAdministrationPagesDependencySynchronizationContent {

  /**
   * Update content data of existing translations
   *
   * @param PapayaContentPageTranslation $origin
   * @param array $targetIds
   * @return boolean
   */
  protected function updateTranslations(PapayaContentPageTranslation $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
      $databaseAccess->getTableName(PapayaContentTables::PAGE_TRANSLATIONS),
      array(
        'view_id' => $origin->viewId,
        'topic_trans_modified' => $origin->modified
      ),
      array(
        'lng_id' => $origin->languageId,
        'topic_id' => $targetIds
      )
    );
  }
}