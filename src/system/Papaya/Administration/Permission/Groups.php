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

namespace Papaya\Administration\Permission;
use Papaya\Administration\Permissions;

/**
 * Constant and structure definitions for administration interface permission groups.
 *
 * @see Permissions
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Groups implements \IteratorAggregate {

  const UNKNOWN = 0;
  const MISC = 1;
  const PAGES = 2;
  const BOXES = 5;
  const SYSTEM = 3;
  const FILES = 4;
  const MODULES = 7;

  private $_groups = array(
    self::MISC => 'Misc',
    self::PAGES => 'Pages',
    self::BOXES => 'Boxes',
    self::SYSTEM => 'Administration',
    self::FILES => 'Media database',
    self::MODULES => 'Applications'
  );

  /**
   * Stores an index to identify the group of an permission
   *
   * @var array
   */
  private $_index = array();

  /**
   * Group the permissions into groups
   *
   * @var array
   */
  private $_groupedPermissions = array(
    self::MISC => array(
      \Papaya\Administration\Permissions::MESSAGES => 'Messages',
      \Papaya\Administration\Permissions::IMAGE_GENERATOR => 'Dynamic Images',
      \Papaya\Administration\Permissions::SYSTEM_THEMESET_MANAGE => 'Manage Theme Sets',
      \Papaya\Administration\Permissions::SYSTEM_CACHE_CLEAR => 'Clear ouput cache'
    ),
    self::SYSTEM => array(
      \Papaya\Administration\Permissions::SYSTEM_SETTINGS => 'System configuration',
      \Papaya\Administration\Permissions::SYSTEM_TRANSLATE => 'Translate',
      \Papaya\Administration\Permissions::SYSTEM_LINKTYPES_MANAGE => 'Manage Linktypes',
      \Papaya\Administration\Permissions::SYSTEM_MIMETYPES_MANAGE => 'Manage mimetypes',
      \Papaya\Administration\Permissions::SYSTEM_MIMETYPES_EDIT => 'Edit mimetypes',
      \Papaya\Administration\Permissions::SYSTEM_CRONJOBS => 'Cronjobs',
      \Papaya\Administration\Permissions::SYSTEM_PROTOCOL => 'Event protocol',

      \Papaya\Administration\Permissions::USER_MANAGE => 'User management',
      \Papaya\Administration\Permissions::USER_GROUP_MANAGE => 'User group management',

      \Papaya\Administration\Permissions::MODULE_MANAGE => 'Module management',
      \Papaya\Administration\Permissions::VIEW_MANAGE => 'Configure views'
    ),
    self::PAGES => array(
      \Papaya\Administration\Permissions::PAGE_MANAGE => 'Manage pages',
      \Papaya\Administration\Permissions::PAGE_CREATE => 'Create pages',
      \Papaya\Administration\Permissions::PAGE_MOVE => 'Move pages',
      \Papaya\Administration\Permissions::PAGE_COPY => 'Copy pages',
      \Papaya\Administration\Permissions::PAGE_DELETE => 'Delete pages',
      \Papaya\Administration\Permissions::PAGE_PUBLISH => 'Publish',
      \Papaya\Administration\Permissions::PAGE_VERSION_MANAGE => 'Version management',
      \Papaya\Administration\Permissions::PAGE_METADATA_EDIT => 'Define metatags',
      \Papaya\Administration\Permissions::PAGE_DEPENDENCY_MANAGE => 'Manage Page Depedencies',
      \Papaya\Administration\Permissions::PAGE_PERMISSION_MANAGE => 'Change edit permissions',
      \Papaya\Administration\Permissions::PAGE_TRASH_MANAGE => 'View trash',
      \Papaya\Administration\Permissions::PAGE_REPAIR_INDEX => 'Check and correct path index',
      \Papaya\Administration\Permissions::PAGE_CACHE_CONFIGURE => 'Configure Caching',

      \Papaya\Administration\Permissions::ALIAS_MANAGE => 'Define aliases',

      \Papaya\Administration\Permissions::TAG_MANAGE => 'Manage Tags',
      \Papaya\Administration\Permissions::TAG_CATEGORY_MANAGE => 'Edit Tag Categories',
      \Papaya\Administration\Permissions::TAG_EDIT => 'Edit Tags',
      \Papaya\Administration\Permissions::TAG_LINK => 'Link Tags'
    ),
    self::BOXES => array(
      \Papaya\Administration\Permissions::BOX_MANAGE => 'Edit boxes',
      \Papaya\Administration\Permissions::BOX_LINK => 'Link boxes'
    ),
    self::FILES => array(
      \Papaya\Administration\Permissions::FILE_BROWSE => 'File browser',
      \Papaya\Administration\Permissions::FILE_MANAGE => 'File management',
      \Papaya\Administration\Permissions::FILE_FOLDER_MANAGE => 'Edit folders',
      \Papaya\Administration\Permissions::FILE_UPLOAD => 'Upload files',
      \Papaya\Administration\Permissions::FILE_EDIT => 'Edit files',
      \Papaya\Administration\Permissions::FILE_DELETE => 'Delete files',
      \Papaya\Administration\Permissions::FILE_IMPORT => 'Import files'
    )
  );

  /**
   * Build an reverse index of the grouped permission to allow faster access.
   */
  public function __construct() {
    foreach ($this->_groupedPermissions as $groupId => $permissions) {
      foreach ($permissions as $id => $title) {
        $this->_index[$id] = $groupId;
      }
    }
  }

  /**
   * Return an recursive iterator for the group/permission definition
   *
   * First level are the group ids and titles. Second level are the permission ids and titles.
   *
   * @see \IteratorAggregate::getIterator()
   */
  public function getIterator() {
    return new \PapayaIteratorTreeDetails(
      $this->_groups,
      $this->_groupedPermissions
    );
  }

  /**
   * get the group id for an given permission
   *
   * @param integer $permissionId
   * @return integer
   */
  public function getGroupId($permissionId) {
    return isset($this->_index[$permissionId])
      ? $this->_index[$permissionId] : self::UNKNOWN;
  }
}
