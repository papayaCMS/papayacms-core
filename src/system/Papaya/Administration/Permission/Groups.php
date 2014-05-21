<?php
/**
* Constant and structure definitions for administration interface permission groups.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Groups.php 38353 2013-04-03 14:08:56Z weinert $
*/

/**
* Constant and structure definitions for administration interface permission groups.
*
* @see PapayaAdministrationPermissions
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPermissionGroups implements IteratorAggregate {

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
    PapayaAdministrationPermissionGroups::MISC => array(
      PapayaAdministrationPermissions::MESSAGES => 'Messages',
      PapayaAdministrationPermissions::IMAGE_GENERATOR => 'Dynamic Images',
      PapayaAdministrationPermissions::SYSTEM_THEMESET_MANAGE => 'Manage Theme Sets',
      PapayaAdministrationPermissions::SYSTEM_CACHE_CLEAR => 'Clear ouput cache'
    ),
    PapayaAdministrationPermissionGroups::SYSTEM => array(
      PapayaAdministrationPermissions::SYSTEM_SETTINGS => 'System configuration',
      PapayaAdministrationPermissions::SYSTEM_TRANSLATE => 'Translate',
      PapayaAdministrationPermissions::SYSTEM_LINKTYPES_MANAGE => 'Manage Linktypes',
      PapayaAdministrationPermissions::SYSTEM_MIMETYPES_MANAGE => 'Manage mimetypes',
      PapayaAdministrationPermissions::SYSTEM_MIMETYPES_EDIT => 'Edit mimetypes',
      PapayaAdministrationPermissions::SYSTEM_CRONJOBS => 'Cronjobs',
      PapayaAdministrationPermissions::SYSTEM_PROTOCOL => 'Event protocol',

      PapayaAdministrationPermissions::USER_MANAGE => 'User management',
      PapayaAdministrationPermissions::USER_GROUP_MANAGE => 'User group management',

      PapayaAdministrationPermissions::MODULE_MANAGE => 'Module management',
      PapayaAdministrationPermissions::VIEW_MANAGE => 'Configure views'
    ),
    PapayaAdministrationPermissionGroups::PAGES => array(
      PapayaAdministrationPermissions::PAGE_MANAGE => 'Manage pages',
      PapayaAdministrationPermissions::PAGE_CREATE => 'Create pages',
      PapayaAdministrationPermissions::PAGE_MOVE => 'Move pages',
      PapayaAdministrationPermissions::PAGE_COPY => 'Copy pages',
      PapayaAdministrationPermissions::PAGE_DELETE => 'Delete pages',
      PapayaAdministrationPermissions::PAGE_PUBLISH => 'Publish',
      PapayaAdministrationPermissions::PAGE_VERSION_MANAGE => 'Version management',
      PapayaAdministrationPermissions::PAGE_METADATA_EDIT => 'Define metatags',
      PapayaAdministrationPermissions::PAGE_DEPENDENCY_MANAGE => 'Manage Page Depedencies',
      PapayaAdministrationPermissions::PAGE_PERMISSION_MANAGE => 'Change edit permissions',
      PapayaAdministrationPermissions::PAGE_TRASH_MANAGE => 'View trash',
      PapayaAdministrationPermissions::PAGE_REPAIR_INDEX => 'Check and correct path index',
      PapayaAdministrationPermissions::PAGE_CACHE_CONFIGURE => 'Configure Caching',

      PapayaAdministrationPermissions::ALIAS_MANAGE => 'Define aliases',

      PapayaAdministrationPermissions::TAG_MANAGE => 'Manage Tags',
      PapayaAdministrationPermissions::TAG_CATEGORY_MANAGE => 'Edit Tag Categories',
      PapayaAdministrationPermissions::TAG_EDIT => 'Edit Tags',
      PapayaAdministrationPermissions::TAG_LINK => 'Link Tags'
    ),
    PapayaAdministrationPermissionGroups::BOXES => array(
      PapayaAdministrationPermissions::BOX_MANAGE => 'Edit boxes',
      PapayaAdministrationPermissions::BOX_LINK => 'Link boxes'
    ),
    PapayaAdministrationPermissionGroups::FILES => array(
      PapayaAdministrationPermissions::FILE_BROWSE => 'File browser',
      PapayaAdministrationPermissions::FILE_MANAGE => 'File management',
      PapayaAdministrationPermissions::FILE_FOLDER_MANAGE => 'Edit folders',
      PapayaAdministrationPermissions::FILE_UPLOAD => 'Upload files',
      PapayaAdministrationPermissions::FILE_EDIT => 'Edit files',
      PapayaAdministrationPermissions::FILE_DELETE => 'Delete files',
      PapayaAdministrationPermissions::FILE_IMPORT => 'Import files'
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
   * @see IteratorAggregate::getIterator()
   */
  public function getIterator() {
    return new PapayaIteratorTreeDetails(
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