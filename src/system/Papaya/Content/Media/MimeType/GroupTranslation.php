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

namespace Papaya\Content\Media\MimeType {

  use Papaya\Content\Tables as ContentTables;
  use Papaya\Database\Record\Key\Fields as FieldsKey;
  use Papaya\Database\Record\Lazy as LazyDatabaseRecord;

  /**
   * @property int $id
   * @property int $languageId
   * @property string $title
   */
  class GroupTranslation extends LazyDatabaseRecord {

    protected $_fields = [
      'id' => 'mimegroup_id',
      'language_id' => 'lng_id',
      'title' => 'mimegroup_title',
    ];

    protected $_tableName = ContentTables::MEDIA_MIMETYPE_GROUP_TRANSLATIONS;

    public function _createKey() {
      return new FieldsKey($this, $this->_tableName, ['id', 'language_id']);
    }
  }
}
