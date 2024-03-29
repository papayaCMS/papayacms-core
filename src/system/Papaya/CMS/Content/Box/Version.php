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
namespace Papaya\CMS\Content\Box;

use Papaya\CMS\Content;
use Papaya\Database;

/**
 * Provide data encapsulation for a single content box version and access to its translations.
 *
 * Allows to load/create the box version.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property-read int $versionId
 * @property-read int $created
 * @property string $owner
 * @property string $message
 * @property int $boxId box id
 * @property int $groupId box group id
 * @property string $name administration interface box name
 * @property int $modified last modification timestamp
 */
class Version extends Database\BaseObject\Record {
  /**
   * Map properties to database fields
   *
   * @var array(string=>string)
   */
  protected $_fields = [
    // auto increment version id
    'id' => 'version_id',
    // version timestamp
    'created' => 'version_time',
    // version owner
    'owner' => 'version_author_id',
    // version log message
    'message' => 'version_message',
    // box id
    'box_id' => 'box_id',
    // box modification timestamp
    'modified' => 'box_modified',
    // box group id
    'group_id' => 'boxgroup_id',
    // name for administration interface
    'name' => 'box_name',
  ];

  /**
   * version table name for default load() implementations
   *
   * @var string
   */
  protected $_tableName = Content\Tables::BOX_VERSIONS;

  /**
   * version translations list subobject
   *
   * @var Version\Translations
   */
  private $_translations;

  /**
   * Saving an existing version is not allowed. The creation of a new version will be directly from
   * the stored data using sql commands.
   *
   * @throws \LogicException
   * @throws \UnexpectedValueException
   *
   * @return bool
   */
  public function save() {
    if (isset($this->id)) {
      throw new \LogicException('LogicException: Box versions can not be changed.');
    }
    if (empty($this->boxId) || empty($this->owner) || empty($this->message)) {
      throw new \UnexpectedValueException(
        'UnexpectedValueException: box id, owner or message are missing.'
      );
    }
    return $this->create();
  }

  /**
   * Create and store a backup of the current box working copy and its translations
   *
   * @return int|false
   */
  private function create() {
    $sql = "INSERT INTO %s (
                   version_time, version_author_id, version_message,
                   box_id, boxgroup_id, box_name, box_modified
            )
            SELECT
                   '%d', '%s', '%s', '%d',
                   box_id, boxgroup_id, box_name, box_modified
              FROM %s
             WHERE box_id = '%d'";
    $parameters = [
      $this->databaseGetTableName($this->_tableName),
      isset($this->created) ? $this->created : \time(),
      $this->owner,
      $this->message,
      $this->databaseGetTableName(Content\Tables::BOXES),
      $this->boxId
    ];
    if ($this->databaseQueryFmtWrite($sql, $parameters)) {
      $newId = $this->databaseLastInsertId(
        $this->databaseGetTableName($this->_tableName), 'version_id'
      );
      $sql = "INSERT INTO %s (
                     version_id, lng_id,
                     box_id, box_title, box_data,
                     box_trans_created, box_trans_modified
                     view_id
              )
              SELECT '%d', bt.lng_id,
                     bt.box_id, bt.box_title, bt.box_data,
                     bt.box_trans_created, box_trans_modified,
                     bt.view_id,
                FROM %s bt
               WHERE bt.box_id = %d";
      $parameters = [
        $this->databaseGetTableName(Content\Tables::BOX_VERSION_TRANSLATIONS),
        $newId,
        $this->databaseGetTableName(Content\Tables::BOX_TRANSLATIONS),
        $this->boxId
      ];
      $this->databaseQueryFmtWrite($sql, $parameters);
      return $newId;
    }
    return FALSE;
  }

  /**
   * Access to the version translations
   *
   * @param Version\Translations $translations
   *
   * @return Version\Translations
   */
  public function translations(Version\Translations $translations = NULL) {
    if (NULL !== $translations) {
      $this->_translations = $translations;
    } elseif (NULL === $this->_translations) {
      $this->_translations = new Version\Translations();
      $this->_translations->setDatabaseAccess($this->getDatabaseAccess());
    }
    return $this->_translations;
  }
}
