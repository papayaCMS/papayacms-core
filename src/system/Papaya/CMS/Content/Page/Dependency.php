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
namespace Papaya\CMS\Content\Page;

use Papaya\CMS\Content;
use Papaya\Database;

/**
 * Provide data encapsulation for the dependency of content page.
 *
 * Allows to edit the pages. It contains no validation, only the database access
 * encapsulation.
 *
 * @property int $id page id - this page will be defined as a dependency
 * @property int $originId page id - this page will be the origin of the defined dependency
 * @property string $note - a small text describing the dependency
 * @property int $synchronization bitmask of the synchronization elements
 */
class Dependency extends Database\Record {
  /**
   * Sync page properties: title, meta information, ...
   *
   * @var int
   */
  const SYNC_PROPERTIES = 1;

  /**
   * Sync page content xml
   *
   * @var int
   */
  const SYNC_CONTENT = 2;

  /**
   * Sync page view
   *
   * @var int
   */
  const SYNC_VIEW = 64;

  /**
   * Sync boxes: mode and box links
   *
   * @var int
   */
  const SYNC_BOXES = 4;

  /**
   * Sync tags
   *
   * @var int
   */
  const SYNC_TAGS = 8;

  /**
   * Sync visitor access properties
   *
   * @var int
   */
  const SYNC_ACCESS = 16;

  /**
   * Sync publication: duplicate publication action on all dependencies if origin is published.
   *
   * @var int
   */
  const SYNC_PUBLICATION = 32;

  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = [
    // page id / clone id
    'id' => 'topic_id',
    // origin id
    'origin_id' => 'topic_origin_id',
    // synchronization bitmask
    'synchronization' => 'topic_synchronization',
    // some infos about the dependency (for editors)
    'note' => 'topic_note'
  ];

  protected $_tableName = Content\Tables::PAGE_DEPENDENCIES;

  /**
   * Create a multi field key object containg both page id properties
   *
   * @return \Papaya\Database\Interfaces\Key
   */
  protected function _createKey() {
    return new Database\Record\Key\Fields(
      $this,
      $this->_tableName,
      ['id']
    );
  }

  /**
   * Validate the defined dependency an save it into database.
   *
   * @throw UnexpectedValueException
   *
   * @throws \UnexpectedValueException
   *
   * @return bool|\Papaya\Database\Interfaces\Key
   */
  public function save() {
    if ($this->id < 1) {
      throw new \UnexpectedValueException('UnexpectedValueException: No target page defined.');
    }
    if ($this->originId < 1) {
      throw new \UnexpectedValueException('UnexpectedValueException: No origin page defined.');
    }
    if ((int)$this->id === (int)$this->originId) {
      throw new \UnexpectedValueException('UnexpectedValueException: Target equals origin.');
    }
    if ($this->isDependency($this->originId)) {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: Origin page is a dependency. Chaining is not possible.'
      );
    }
    return parent::save();
  }

  /**
   * Check if the given page id already has a dependency definition. This is used to avoid chaining.
   *
   * @param int $pageId
   *
   * @return bool
   */
  public function isDependency($pageId) {
    $sql = "SELECT COUNT(*) FROM %s WHERE topic_id = '%d'";
    $parameters = [
      $this->getDatabaseAccess()->getTableName($this->_tableName),
      $pageId
    ];
    if ($res = $this->getDatabaseAccess()->queryFmt($sql, $parameters)) {
      return $res->fetchField() > 0;
    }
    return FALSE;
  }

  /**
   * Check if the given page id is the origin of one or more dependencies.
   *
   * @param int $pageId
   *
   * @return bool
   */
  public function isOrigin($pageId) {
    $sql = "SELECT COUNT(*) FROM %s WHERE topic_origin_id = '%d'";
    $parameters = [
      $this->getDatabaseAccess()->getTableName($this->_tableName),
      $pageId
    ];
    if ($res = $this->getDatabaseAccess()->queryFmt($sql, $parameters)) {
      return $res->fetchField() > 0;
    }
    return FALSE;
  }
}
