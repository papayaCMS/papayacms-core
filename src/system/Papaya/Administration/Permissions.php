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

namespace Papaya\Administration;

use Papaya\Administration\Permission\Groups;

/**
 * Constant and structure definitions for administration interface permissions.
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Permissions
  extends \Papaya\Database\Records\Lazy {

  const SYSTEM_SETTINGS = 25;
  const SYSTEM_PROTOCOL = 31;
  const SYSTEM_CACHE_CLEAR = 37;

  const SYSTEM_CRONJOBS = 32;
  const SYSTEM_TRANSLATE = 36;

  const SYSTEM_LINKTYPES_MANAGE = 41;

  const SYSTEM_MIMETYPES_MANAGE = 45;
  const SYSTEM_MIMETYPES_EDIT = 46;

  const SYSTEM_THEMESET_MANAGE = 51;  // current maximum

  const USER_MANAGE = 4;
  const USER_GROUP_MANAGE = 5;

  const MESSAGES = 34;

  const PAGE_MANAGE = 35;
  const PAGE_CREATE = 1;
  const PAGE_MOVE = 21;
  const PAGE_COPY = 22;
  const PAGE_DELETE = 23;
  const PAGE_PUBLISH = 26;
  const PAGE_SEARCH = 42;
  const PAGE_METADATA_EDIT = 7;
  const PAGE_PERMISSION_MANAGE = 29;
  const PAGE_VERSION_MANAGE = 24;
  const PAGE_REPAIR_INDEX = 33;
  const PAGE_TRASH_MANAGE = 38;
  const PAGE_DEPENDENCY_MANAGE = 50;
  const PAGE_CACHE_CONFIGURE = 49;

  const BOX_MANAGE = 13;
  const BOX_LINK = 14;

  const VIEW_MANAGE = 6;
  const MODULE_MANAGE = 30;

  const FILE_MANAGE = 15;
  const FILE_BROWSE = 28;
  const FILE_UPLOAD = 8;
  const FILE_IMPORT = 47;
  const FILE_EDIT = 9;
  const FILE_DELETE = 10;
  const FILE_FOLDER_MANAGE = 27;

  const ALIAS_MANAGE = 16;

  const TAG_MANAGE = 40;
  const TAG_CATEGORY_MANAGE = 43;
  const TAG_EDIT = 44;
  const TAG_LINK = 48;

  const IMAGE_GENERATOR = 39;


  /**
   * Map field names to more convinient property names
   *
   * @var array(string=>string)
   */
  protected $_fields = array(
    'id' => 'perm_id',
    'is_active' => 'perm_active'
  );

  /**
   * Table containing permission status informations (active/inactive)
   *
   * @var string
   */
  protected $_tableName = \Papaya\Content\Tables::AUTHENTICATION_PERMISSIONS;

  protected $_identifierProperties = array('id');

  /**
   * This will be initialized in the constructor, a list of all defined constants of this class.
   *
   * @var array|NULL
   */
  private static $_permissions = NULL;

  /**
   * @var Groups
   */
  private $_groups = NULL;

  /**
   * Build an index of all permission constants using reflection
   */
  public function __construct() {
    // @codeCoverageIgnoreStart
    if (NULL == self::$_permissions) {
      $reflection = new \ReflectionClass(__CLASS__);
      self::$_permissions = array_flip($reflection->getConstants());
    }
    // @codeCoverageIgnoreEnd
    $this->reset();
  }

  /**
   * Reset the object to "unloaded" status
   */
  public function reset() {
    $this->_records = array();
    foreach (self::$_permissions as $id => $name) {
      $this->_records[$id] = array(
        'id' => $id,
        'is_active' => TRUE
      );
    }
  }

  /**
   * Validate if a permission exists
   *
   * If the groupId is provided, the method will only return TRUE if the permission exists in
   * that group.
   *
   * @param integer $permissionId
   * @param NULL|integer $groupId
   * @return boolean
   */
  public function exists($permissionId, $groupId = NULL) {
    if (isset(self::$_permissions[$permissionId])) {
      if ($groupId > 0) {
        return $this->inGroup($permissionId, $groupId);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Validate if the given permission is in the given group.
   *
   * @see \PapayaAdministrationPermissions::exists()
   * @param integer $permissionId
   * @param integer $groupId
   * @return boolean
   */
  public function inGroup($permissionId, $groupId) {
    return $this->groups()->getGroupId($permissionId) == $groupId;
  }


  /**
   * Return the current status of a permission
   *
   * @param integer $permissionId
   * @return boolean
   */
  public function isActive($permissionId) {
    if (isset($this[$permissionId])) {
      return (boolean)$this[$permissionId]['is_active'];
    }
    return FALSE;
  }

  /**
   *
   * @param \Papaya\Administration\Permission\Groups $groups
   * @return \Papaya\Administration\Permission\Groups
   */
  public function groups(\Papaya\Administration\Permission\Groups $groups = NULL) {
    if (isset($groups)) {
      $this->_groups = $groups;
    } elseif (NULL === $this->_groups) {
      $this->_groups = new \Papaya\Administration\Permission\Groups();
    }
    return $this->_groups;
  }
}
