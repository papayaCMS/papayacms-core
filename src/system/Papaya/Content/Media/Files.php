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

namespace Papaya\Content\Media;

use Papaya\Content\Tables;
use Papaya\Database;

class Files extends Database\Records\Lazy {

  const SORT_BY_NAME = 'by_name';
  const SORT_BY_SIZE = 'by_size';
  const SORT_BY_DATE = 'by_date';

  protected $_fields = [
    'id' => 'files.file_id',
    'folder_id' => 'files.folder_id',
    'surfer_id' => 'files.surfer_id',
    'name' => 'files.file_name',
    'date' => 'files.file_date',
    'size' => 'files.file_size',
    'created' => 'files.file_created',
    'sort' => 'files.file_sort',
    'source' => 'files.file_source',
    'source_url' => 'files.file_source_url',
    'mimetype_id' => 'files.mimetype_id',
    'revision' => 'files.current_version_id',
    'image_width' => 'files.width',
    'image_height' => 'files.height',
    'extension' => 'mimetypes.mimetype_ext',
    'icon' => 'mimetypes.mimetype_icon',
    'title' => 'translations.file_title',
    'description' => 'translations.file_description'
  ];

  protected $_orderByProperties = [
    'sort' => Database\Interfaces\Order::ASCENDING,
    'name' => Database\Interfaces\Order::ASCENDING,
    'id' => Database\Interfaces\Order::ASCENDING
  ];

  /**
   * @param array|null $filter
   * @param int|null $limit
   * @param int|null $offset
   * @return bool
   */
  public function load($filter = NULL, $limit = NULL, $offset = NULL) {
    if (isset($filter['language_id'])) {
      $languageId = (int)$filter['language_id'];
      unset($filter['language_id']);
    } else {
      $languageId = 0;
    }
    $statement = $this->getDatabaseAccess()->prepare(
      "SELECT 
          files.file_id, files.folder_id, files.surfer_id, 
          files.file_name, files.file_date, files.file_size, 
          files.file_created, files.file_sort, files.file_source, 
          files.file_source_url, files.file_keywords, files.mimetype_id,
          files.current_version_id,
          files.width, files.height, files.metadata,
          mimetypes.mimetype_ext, mimetypes.mimetype_icon,
          translations.file_title, translations.file_description
        FROM :files AS files
        LEFT JOIN :mimetypes AS mimetypes ON (mimetypes.mimetype_id = files.mimetype_id) 
        LEFT JOIN :translations AS translations ON (translations.file_id = files.file_id AND translations.lng_id = :language_id) " .
      $this->_compileCondition($filter) . $this->_compileOrderBy()
    );
    $statement->addTableName('files', Tables::MEDIA_FILES);
    $statement->addTableName('mimetypes', Tables::MEDIA_MIMETYPES);
    $statement->addTableName('translations', Tables::MEDIA_FILE_TRANSLATIONS);
    $statement->addInt('language_id', $languageId);
    return $this->_loadRecords($statement, [], $limit, $offset, $this->_identifierProperties);
  }

  public function setSorting($sortBy, $sortDirection = Database\Interfaces\Order::ASCENDING) {
    switch ($sortBy) {
      case self::SORT_BY_SIZE:
        $this->_orderByProperties = [
          'size' => $sortDirection,
          'name' => Database\Interfaces\Order::ASCENDING,
          'id' => Database\Interfaces\Order::ASCENDING
        ];
        break;
      case self::SORT_BY_DATE:
        $this->_orderByProperties = [
          'date' => $sortDirection,
          'name' => Database\Interfaces\Order::ASCENDING,
          'id' => Database\Interfaces\Order::ASCENDING
        ];
        break;
      case self::SORT_BY_NAME:
      default:
        $this->_orderByProperties = [
          'name' => $sortDirection,
          'id' => Database\Interfaces\Order::ASCENDING
        ];
        break;
    }
  }
}
