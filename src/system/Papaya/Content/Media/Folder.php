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
namespace Papaya\Content\Media {

  use Papaya\Content;
  use Papaya\Database;
  use Papaya\Utility;

  /**
   * @property int $id
   * @property int $parentId
   * @property int[] $ancestors
   * @property string $permissionMode
   */
  class Folder extends Database\Record\Lazy
  {
    const PERMISSION_MODE_DEFINE = 'own';
    const PERMISSION_MODE_EXTEND = 'additional';
    const PERMISSION_MODE_INHERIT = 'inherited';

    protected $_fields = [
      'id' => 'folder_id',
      'parent_id' => 'parent_id',
      'ancestors' => 'parent_path',
      'permission_mode' => 'permission_mode'
    ];

    protected $_tableName = Content\Tables::MEDIA_FOLDERS;

    /**
     * @return Database\Record\Mapping
     */
    public function _createMapping()
    {
      $mapping = parent::_createMapping();
      $mapping->callbacks()->onMapValueFromFieldToProperty = static function (
        /** @noinspection PhpUnusedParameterInspection */
        $context, $property, $field, $value
      ) {
        if ('ancestors' === $property) {
          return Utility\Arrays::decodeIdList($value);
        }
        return $value;
      };
      $mapping->callbacks()->onMapValueFromPropertyToField = static function (
        /** @noinspection PhpUnusedParameterInspection */
        $context, $property, $field, $value
      ) {
        if ('ancestors' === $property) {
          return Utility\Arrays::encodeIdList($value);
        }
        return $value;
      };
      return $mapping;
    }
  }
}
