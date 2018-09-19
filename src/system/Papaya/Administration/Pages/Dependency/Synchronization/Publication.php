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

/**
 * Synchronize a publication to assigned target page. This is done duplicating the publish action.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Publication
  implements Administration\Pages\Dependency\Synchronization {
  /**
   * @var Page\Publication
   */
  private $_publication;

  /**
   * @var Page\Work
   */
  private $_page;

  /**
   * @var Page\Version
   */
  private $_version;

  /**
   * Synchronize a dependency, publish target pages
   *
   * @param array $targetIds
   * @param int $originId
   * @param array|null $languages
   *
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
   * @param Page\Publication $publication
   *
   * @return Page\Publication
   */
  public function publication(Page\Publication $publication = NULL) {
    if (NULL !== $publication) {
      $this->_publication = $publication;
    } elseif (NULL === $this->_publication) {
      $this->_publication = new Page\Publication();
    }
    return $this->_publication;
  }

  /**
   * Getter/Setter for working copy page object. This is used to publish the target pages.
   *
   * @param Page\Work $page
   *
   * @return Page\Work
   */
  public function page(Page\Work $page = NULL) {
    if (NULL !== $page) {
      $this->_page = $page;
    } elseif (NULL === $this->_page) {
      $this->_page = new Page\Work();
    }
    return $this->_page;
  }

  /**
   * Getter/Setter for a page version object. This is used to create version for the target pages.
   *
   * @param Page\Version $version
   *
   * @return Page\Version
   */
  public function version(Page\Version $version = NULL) {
    if (NULL !== $version) {
      $this->_version = $version;
    } elseif (NULL === $this->_version) {
      $this->_version = new Page\Version();
    }
    return $this->_version;
  }

  /**
   * Fetch the needed version data (owner, message, change level).
   *
   * @param int $pageId
   *
   * @return array|false
   */
  private function getVersionData($pageId) {
    $databaseAccess = $this->publication()->getDatabaseAccess();
    $sql = "SELECT version_author_id, version_message, topic_change_level
              FROM %s
             WHERE topic_id = '%d'
             ORDER BY version_time DESC";
    $parameters = [
      $databaseAccess->getTableName(\Papaya\Content\Tables::PAGE_VERSIONS),
      $pageId
    ];
    if (($databaseResult = $databaseAccess->queryFmt($sql, $parameters, 1)) &&
      ($row = $databaseResult->fetchRow(\Papaya\Database\Result::FETCH_ASSOC))) {
      return [
        'owner' => $row['version_author_id'],
        'message' => $row['version_message'],
        'level' => $row['topic_change_level']
      ];
    }
    return FALSE;
  }
}
