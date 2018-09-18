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

use Papaya\Administration;
use Papaya\Iterator;

/**
 * Constant and structure definitions for administration interface permission groups.
 *
 * @see \Papaya\Administration\Permissions
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

  private $_groups = [
    self::MISC => 'Misc',
    self::PAGES => 'Pages',
    self::BOXES => 'Boxes',
    self::SYSTEM => 'Administration',
    self::FILES => 'Media database',
    self::MODULES => 'Applications'
  ];

  /**
   * Stores an index to identify the group of an permission
   *
   * @var array
   */
  private $_index = [];

  /**
   * Group the permissions into groups
   *
   * @var array
   */
  private $_groupedPermissions = [
    self::MISC => [
      Administration\Permissions::MESSAGES => 'Messages',
      Administration\Permissions::IMAGE_GENERATOR => 'Dynamic Images',
      Administration\Permissions::SYSTEM_THEME_SKIN_MANAGE => 'Manage Theme Sets',
      Administration\Permissions::SYSTEM_CACHE_CLEAR => 'Clear ouput cache'
    ],
    self::SYSTEM => [
      Administration\Permissions::SYSTEM_SETTINGS => 'System configuration',
      Administration\Permissions::SYSTEM_TRANSLATE => 'Translate',
      Administration\Permissions::SYSTEM_LINKTYPES_MANAGE => 'Manage Linktypes',
      Administration\Permissions::SYSTEM_MIMETYPES_MANAGE => 'Manage mimetypes',
      Administration\Permissions::SYSTEM_MIMETYPES_EDIT => 'Edit mimetypes',
      Administration\Permissions::SYSTEM_CRONJOBS => 'Cronjobs',
      Administration\Permissions::SYSTEM_PROTOCOL => 'Event protocol',

      Administration\Permissions::USER_MANAGE => 'User management',
      Administration\Permissions::USER_GROUP_MANAGE => 'User group management',

      Administration\Permissions::MODULE_MANAGE => 'Module management',
      Administration\Permissions::VIEW_MANAGE => 'Configure views'
    ],
    self::PAGES => [
      Administration\Permissions::PAGE_MANAGE => 'Manage pages',
      Administration\Permissions::PAGE_CREATE => 'Create pages',
      Administration\Permissions::PAGE_MOVE => 'Move pages',
      Administration\Permissions::PAGE_COPY => 'Copy pages',
      Administration\Permissions::PAGE_DELETE => 'Delete pages',
      Administration\Permissions::PAGE_PUBLISH => 'Publish',
      Administration\Permissions::PAGE_VERSION_MANAGE => 'Version management',
      Administration\Permissions::PAGE_METADATA_EDIT => 'Define metatags',
      Administration\Permissions::PAGE_DEPENDENCY_MANAGE => 'Manage Page Depedencies',
      Administration\Permissions::PAGE_PERMISSION_MANAGE => 'Change edit permissions',
      Administration\Permissions::PAGE_TRASH_MANAGE => 'View trash',
      Administration\Permissions::PAGE_REPAIR_INDEX => 'Check and correct path index',
      Administration\Permissions::PAGE_CACHE_CONFIGURE => 'Configure Caching',

      Administration\Permissions::ALIAS_MANAGE => 'Define aliases',

      Administration\Permissions::TAG_MANAGE => 'Manage Tags',
      Administration\Permissions::TAG_CATEGORY_MANAGE => 'Edit Tag Categories',
      Administration\Permissions::TAG_EDIT => 'Edit Tags',
      Administration\Permissions::TAG_LINK => 'Link Tags'
    ],
    self::BOXES => [
      Administration\Permissions::BOX_MANAGE => 'Edit boxes',
      Administration\Permissions::BOX_LINK => 'Link boxes'
    ],
    self::FILES => [
      Administration\Permissions::FILE_BROWSE => 'File browser',
      Administration\Permissions::FILE_MANAGE => 'File management',
      Administration\Permissions::FILE_FOLDER_MANAGE => 'Edit folders',
      Administration\Permissions::FILE_UPLOAD => 'Upload files',
      Administration\Permissions::FILE_EDIT => 'Edit files',
      Administration\Permissions::FILE_DELETE => 'Delete files',
      Administration\Permissions::FILE_IMPORT => 'Import files'
    ]
  ];

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
   * @return \Iterator
   */
  public function getIterator() {
    return new Iterator\Tree\Details(
      $this->_groups,
      $this->_groupedPermissions
    );
  }

  /**
   * get the group id for an given permission
   *
   * @param int $permissionId
   * @return int
   */
  public function getGroupId($permissionId) {
    return isset($this->_index[$permissionId])
      ? $this->_index[$permissionId] : self::UNKNOWN;
  }
}
