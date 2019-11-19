<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
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

  use Papaya\Content\Media\MimeType\Extensions;
  use Papaya\Content\Tables as ContentTables;
  use Papaya\Database\Record\Callbacks as RecordCallbacks;
  use Papaya\Database\Record\Lazy as LazyDatabaseRecord;

  /**
   * @property int $id
   * @property int $groupId
   * @property string $type
   * @property string $icon
   * @property string $extension
   * @property int $supportsRanges
   * @property int $enableShaping
   * @property int $shapingLimit
   * @property int $shapingOffset
   */
  class MimeType extends LazyDatabaseRecord {

    protected $_fields = [
      'id' => 'mimetype_id',
      'group_id' => 'mimegroup_id',
      'type' => 'mimetype',
      'icon' => 'mimetype_icon',
      'extension' => 'mimetype_ext',
      'supports_ranges' => 'range_support',
      'enable_shaping' => 'shaping',
      'shaping_limit' => 'shaping_limit',
      'shaping_offset' => 'shaping_offset'
    ];

    protected $_tableName = ContentTables::MEDIA_MIMETYPES;
    /**
     * @var Extensions
     */
    private $_extensions;

    /**
     * Create callbacks subobject, override to assign callbacks
     *
     * @return RecordCallbacks
     */
    protected function _createCallbacks() {
      $callbacks = parent::_createCallbacks();
      $callbacks->onBeforeDelete = function() {
        $this->extensions()->delete($this->id);
      };
      return $callbacks;
    }

    public function extensions(Extensions $extensions = NULL) {
      if (NULL !== $extensions) {
        $this->_extensions = $extensions;
      } elseif (NULL === $this->_extensions) {
        $this->_extensions = new MimeType\Extensions();
        $this->_extensions->papaya($this->papaya());
        $this->_extensions->activateLazyLoad(
          ['id' => $this->id]
        );
      }
      return $this->_extensions;
    }
  }
}
