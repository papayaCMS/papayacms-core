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

namespace Papaya\CMS\Content\Media {

  use Papaya\CMS\CMSConfiguration as CMSSettings;
  use Papaya\CMS\Content\Tables as ContentTables;
  use Papaya\Database\Record\Lazy as LazyDatabaseRecord;
  use Papaya\File\Reference;
  use Papaya\Utility\File\Path;

  /**
   * @property int $id
   * @property int $revision
   * @property int $folderId
   * @property string $surferId
   * @property string $name
   * @property int $modified
   * @property int $created
   * @property int $size
   * @property int $width
   * @property int $height
   * @property string $source
   * @property string $sourceUrl
   * @property int $typeId
   */
  class MediaFile extends LazyDatabaseRecord implements Reference {

    protected $_fields = [
      'id' => 'file_id',
      'folder_id' => 'folder_id',
      'surfer_id' => 'surfer_id',
      'name' => 'file_name',
      'modified' => 'file_date',
      'created' => 'file_created',
      'size' => 'file_size',
      'width' => 'width',
      'height' => 'height',
      'source' => 'file_source',
      'source_url' => 'file_source_url',
      'type_id' => 'mimetype_id',
      'revision' => 'current_version_id'
    ];

    protected $_tableName = ContentTables::MEDIA_FILES;

    public function getName() {
      return $this->name;
    }

    public function getType() {
      // TODO: Implement getType() method.
    }

    public function getSize() {
      return $this->size;
    }

    public function getURL() {
      // TODO: Implement getURL() method.
    }

    public function getFileName() {
      $basePath = $this->papaya()->options->get(CMSSettings::PATH_MEDIAFILES, '');
      $fileName = sprintf('%s_%d', $this->id, $this->revision);
      $subDirectories = $this->papaya()->options->get(CMSSettings::MEDIADB_SUBDIRECTORIES, 0);
      $relativePath = '';
      for ($i = 0; $i < $subDirectories; $i++) {
        $relativePath .= '/'.substr($fileName, $i, 1);
      }
      return Path::cleanup($basePath.$relativePath).$fileName;
    }
  }
}
