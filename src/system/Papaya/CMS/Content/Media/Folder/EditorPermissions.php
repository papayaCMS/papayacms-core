<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\CMS\Content\Media\Folder {

  use Papaya\CMS\Content\Tables;
  use Papaya\Database\Records\Lazy as LazyRecord;

  class EditorPermissions extends LazyRecord {

    const EDITOR_READABLE = 'user_view';
    const EDITOR_WRITABLE = 'user_edit';

    protected $_fields = [
      'id' => 'folder_id',
      'type' => 'permission_type',
      'value' => 'permission_value'
    ];

    protected $_tableName = Tables::MEDIA_FOLDER_PERMISSIONS;

    protected $_identifierProperties = ['id', 'type', 'value'];

    public function hasPermission($folderId, $permission, $groupId) {
      return isset($this[$this->getIdentifier([$folderId, $permission, $groupId])]);
    }
  }
}


