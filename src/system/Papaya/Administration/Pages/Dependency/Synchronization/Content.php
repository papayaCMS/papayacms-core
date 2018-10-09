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
use Papaya\Content\Page;
use Papaya\Utility;

/**
 * Synchronize view and content of the page working copy
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Content
  implements Administration\Pages\Dependency\Synchronization {
  /**
   * Translation records object
   *
   * @var Page\Translations
   */
  private $_translations;

  /**
   * Synchronize a dependency
   *
   * @param array $targetIds
   * @param int $originId
   * @param array|null $languages
   *
   * @return bool
   */
  public function synchronize(array $targetIds, $originId, array $languages = NULL) {
    $this->translations()->load($originId);
    if (empty($languages)) {
      $languages = \array_keys(Utility\Arrays::ensure($this->translations()));
    }
    $existing = $this->getExistingTargetTranslations($targetIds, $languages);
    $missing = $this->getMissingTargetTranslations($targetIds, $languages, $existing);
    return $this->synchronizeTranslations($originId, $languages, $existing, $missing);
  }

  /**
   * Getter/Setter for the translation records list.
   *
   * @param Page\Translations $translations
   *
   * @return Page\Translations
   */
  public function translations(Page\Translations $translations = NULL) {
    if (NULL !== $translations) {
      $this->_translations = $translations;
    } elseif (NULL === $this->_translations) {
      $this->_translations = new Page\Translations();
    }
    return $this->_translations;
  }

  /**
   * Determine the existing target translations (to decide between updates and inserts)
   *
   * @param array $targetIds
   * @param array $languageIds
   *
   * @return array
   */
  protected function getExistingTargetTranslations(array $targetIds, array $languageIds) {
    $databaseAccess = $this->translations()->getDatabaseAccess();
    $filter = $databaseAccess->getSqlCondition(
      [
        'topic_id' => $targetIds,
        'lng_id' => $languageIds
      ]
    );
    $sql = "SELECT topic_id, lng_id
              FROM %s
             WHERE $filter";
    $parameters = [
      $databaseAccess->getTableName(\Papaya\Content\Tables::PAGE_TRANSLATIONS)
    ];
    $result = [];
    if ($databaseResult = $databaseAccess->queryFmt($sql, $parameters)) {
      while ($row = $databaseResult->fetchRow(\Papaya\Database\Result::FETCH_ASSOC)) {
        $result[$row['lng_id']][] = $row['topic_id'];
      }
    }
    return $result;
  }

  /**
   * Get the missing target translations using the already found existing ones.
   *
   * @param array $targetIds
   * @param array $languageIds
   * @param array $existing
   *
   * @return array
   */
  protected function getMissingTargetTranslations(
    array $targetIds, array $languageIds, array $existing
  ) {
    $result = [];
    foreach ($languageIds as $languageId) {
      foreach ($targetIds as $targetId) {
        if (
          !(
            isset($existing[$languageId]) &&
            \is_array($existing[$languageId]) &&
            \in_array($targetId, $existing[$languageId], FALSE)
          )
        ) {
          $result[$languageId][] = $targetId;
        }
      }
    }
    return $result;
  }

  /**
   * Load each translation of the current page and sync them with the target pages.
   *
   * @param int $originId
   * @param array $languages
   * @param array $existing
   * @param array $missing
   *
   * @return bool
   */
  public function synchronizeTranslations(
    $originId, array $languages, array $existing, array $missing
  ) {
    foreach ($languages as $languageId) {
      $translation = $this->translations()->getTranslation($originId, $languageId);
      $isExisting = isset($existing[$languageId]);
      $isMissing = isset($missing[$languageId]);
      if ($translation->id > 0) {
        if ($isExisting && !$this->updateTranslations($translation, $existing[$languageId])) {
          return FALSE;
        }
        if ($isMissing && !$this->insertTranslations($translation, $missing[$languageId])) {
          return FALSE;
        }
      } elseif ($isExisting) {
        $this->deleteTranslations($languageId, $existing[$languageId]);
      }
    }
    return TRUE;
  }

  /**
   * Update content data of existing translations
   *
   * @param Page\Translation $origin
   * @param array $targetIds
   *
   * @return bool
   */
  protected function updateTranslations(Page\Translation $origin, array $targetIds) {
    $databaseAccess = $origin->getDatabaseAccess();
    return FALSE !== $databaseAccess->updateRecord(
        $databaseAccess->getTableName(\Papaya\Content\Tables::PAGE_TRANSLATIONS),
        [
          'topic_content' => Utility\Text\XML::serializeArray($origin->content),
          'topic_trans_modified' => $origin->modified
        ],
        [
          'lng_id' => $origin->languageId,
          'topic_id' => $targetIds
        ]
      );
  }

  /**
   * Insert missing translations
   *
   * @param Page\Translation $origin
   * @param $targetIds
   *
   * @return bool
   */
  protected function insertTranslations(Page\Translation $origin, $targetIds) {
    foreach ($targetIds as $targetId) {
      $target = clone $origin;
      $target->key()->clear();
      $target->id = $targetId;
      if (!$target->save()) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Delete deprecated translations
   *
   * @param array|int $languageId
   * @param int $targetId
   *
   * @return bool
   */
  protected function deleteTranslations($languageId, $targetId) {
    $databaseAccess = $this->translations()->getDatabaseAccess();
    return FALSE !== $databaseAccess->deleteRecord(
        $databaseAccess->getTableName(\Papaya\Content\Tables::PAGE_TRANSLATIONS),
        [
          'lng_id' => $languageId,
          'topic_id' => $targetId
        ]
      );
  }
}
