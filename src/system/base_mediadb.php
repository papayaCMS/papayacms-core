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

use Papaya\CMS\CMSConfiguration as CMSSettings;
use Papaya\CMS\Content\Media\Folder;
use Papaya\CMS\Content\Tables;

if (!defined('IMAGETYPE_SWC')) {
  /**
   * Fallback to ensure the existence of constant IMAGETYPE_SWC. It comes usually with PHP
   * but is missing in some sporadic versions.
   *
   * @var integer IMAGETYPE_SWC Contains integer type for shockwave formats
   */
  define('IMAGETYPE_SWC', 13);
}

/**
 * Basic class for media db file handling - access only, no modifying
 *
 * @package Papaya
 * @subpackage Media-Database
 */
class base_mediadb extends base_db
{

  var $tableFiles = PAPAYA_DB_TBL_MEDIADB_FILES;
  var $tableFilesDerivations = PAPAYA_DB_TBL_MEDIADB_FILES_DERIVATIONS;
  var $tableFilesTrans = PAPAYA_DB_TBL_MEDIADB_FILES_TRANS;
  var $tableFilesVersions = PAPAYA_DB_TBL_MEDIADB_FILES_VERSIONS;
  var $tableFolders = PAPAYA_DB_TBL_MEDIADB_FOLDERS;
  var $tableFoldersTrans = PAPAYA_DB_TBL_MEDIADB_FOLDERS_TRANS;
  var $tableFoldersPermissions = PAPAYA_DB_TBL_MEDIADB_FOLDERS_PERMISSIONS;
  var $tableMimeGroups = PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS;
  var $tableMimeGroupsTrans = PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS_TRANS;
  var $tableMimeTypes = PAPAYA_DB_TBL_MEDIADB_MIMETYPES;
  var $tableMimeTypesExtensions = PAPAYA_DB_TBL_MEDIADB_MIMETYPES_EXTENSIONS;
  var $tableTagLinks = PAPAYA_DB_TBL_TAG_LINKS;
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  var $dataDirectory = PAPAYA_PATH_MEDIAFILES;
  var $thumbnailDirectory = PAPAYA_PATH_THUMBFILES;

  /**
   * @var string $flashVersion minimum flash version the flv player needs
   */
  var $flashVersion = '9.0.26';

  /**
   * @var integer $absCount absolute count of results for the last query, if limit was set
   */
  var $absCount = 0;

  /**
   * @var string $fallbackMimeType mimetype to choose if no other can be identified
   */
  var $fallbackMimeType = 'application/octet-stream';
  /**
   * @var string $defaultTypeIcon default icon for mimetype
   */
  var $defaultTypeIcon = 'file-other.png';
  /**
   * @var string $defaultTypeIcon default icon for mimegroup
   */
  var $defaultGroupIcon = 'applications-other.png';

  /**
   * @var base_mediadb_mimetypes
   */
  var $mimeObj;

  var $getImageSizeWhiteList = array(
    'image/bmp',
    'image/gif',
    'image/iff',
    'image/jp2',
    'image/jpeg',
    'image/png',
    'image/psd',
    'image/tiff',
    'image/vnd.microsoft.icon',
    'image/vnd.wap.wbmp',
    'image/xbm',
    'application/octet-stream',
    'application/x-shockwave-flash',
  );

  var $imageMimeTypes = array(
    'image/gif',
    'image/png',
    'image/jpeg'
  );
  public $folderPermissions;

  // ---------------------------------- FILES ----------------------------------

  /**
   * returns an instance of base_mediadb and generates one if none exists yet
   *
   * use this if you need to get some data from the mediadb, in order to prevent
   * unnecessary instances of it
   *
   * @return object $mediaDB instance of base_mediadb
   */
  public static function getInstance()
  {
    static $mediaDB = NULL;
    if (!(isset($mediaDB))) {
      $mediaDB = new base_mediadb;
    }
    return $mediaDB;
  }

  /**
   * loads data for a file by its id and version (optional)
   *
   * use this method to get information on a file, if no version is given, the
   * latest will be used
   *
   * @param string $fileId a file id
   * @param integer $versionId an optional version id
   * @param boolean $loadDerivation load derivation data
   * @return mixed $result FALSE on no result, otherwise the file data:
   *  * file_id, folder_id, surfer_id, file_name, file_date, file_size,
   *  * width, height, metadata, file_sort, current_version_id, mimetype_id,
   *  * mimetype, mimetype_icon, FILENAME (location on disk), DERIVED (bool),
   *  * DERIVATIONS (number of derivations), WIDTH, HEIGHT
   */
  function getFile($fileId, $versionId = NULL, $loadDerivation = FALSE)
  {
    if (NULL !== $versionId && $versionId > 0) {
      $sql = "SELECT current_version_id FROM %s WHERE file_id = '%s'";
      $params = array($this->tableFiles, $fileId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($currentVersionId = $res->fetchField()) {
          if ($currentVersionId != $versionId) {
            return $this->getFileVersion($fileId, $versionId);
          }
        }
      }
    }
    if ($fileId != '') {
      $fileCondition = \Papaya\Utility\Text::escapeForPrintf($this->databaseGetSQLCondition('f.file_id', $fileId));
      $sql = "SELECT f.file_id, f.folder_id, f.surfer_id, f.file_name, f.file_date, f.file_created,
                     f.file_size, f.width, f.height, f.metadata, f.file_sort,
                     f.file_source, f.file_source_url, f.file_keywords,
                     f.current_version_id,
                     m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext, m.mimegroup_id,
                     m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
                FROM %s f
                LEFT OUTER JOIN %s m ON (f.mimetype_id = m.mimetype_id)
              WHERE $fileCondition
            ";
      $params = array($this->tableFiles, $this->tableMimeTypes);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $row['FILETYPE'] = $this->mimeToInteger($row['mimetype']);
          $row['FILENAME'] = $this->getFileName($row['file_id'], $row['current_version_id']);
          $row['WIDTH'] = $row['width'];
          $row['HEIGHT'] = $row['height'];
          if ($loadDerivation) {
            $row['DERIVED'] = $this->fileIsDerived($row['file_id']);
            $row['DERIVATIONS'] = $this->fileCountDerivations($row['file_id']);
          }
          return $row;
        }
      }
    }
    return FALSE;
  }

  /**
   * Check if file exists
   *
   * This Method checks, if a media file exist.
   *
   * @param string $fileId File id
   * @return bool TRUE if File Exists
   */
  function fileExists($fileId)
  {
    $sql = "SELECT COUNT(*)
              FROM %s
            WHERE file_id = '%s'";
    $params = array(
      $this->tableFiles,
      $fileId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
   * Check if file exists
   *
   * This Method checks, if a media file exist.
   *
   * @param string $fileId
   * @param integer $lngId
   * @return bool TRUE if File Exists
   */
  function fileTranslationExists($fileId, $lngId)
  {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE file_id = '%s'
               AND lng_id = '%d'";
    $params = array(
      $this->tableFilesTrans,
      $fileId,
      $lngId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
   * loads a specific version of a file
   *
   * use this method to get a specific (older) version of a file
   *
   * @param string $fileId a file id
   * @param integer $versionId version id
   * @return mixed $result file data {@see base_mediadb::getFile} plus version_id, otherwise FALSE
   */
  function getFileVersion($fileId, $versionId)
  {
    $fileCondition = $this->databaseGetSQLCondition('f.file_id', $fileId);
    $sql = "SELECT f.file_id, f.folder_id, f.file_sort, f.current_version_id,
                   fv.file_name, fv.file_date, fv.file_created, fv.file_size, fv.version_time,
                   fv.version_id, fv.width, fv.height, fv.metadata, fv.surfer_id,
                   fv.file_source, fv.file_source_url, fv.file_keywords,
                   m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext,
                   m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
              FROM %s f
              LEFT OUTER JOIN %s fv ON (f.file_id = fv.file_id)
              LEFT OUTER JOIN %s m ON (fv.mimetype_id = m.mimetype_id)
            WHERE $fileCondition
              AND fv.version_id = %d
          ";
    $params = array($this->tableFiles, $this->tableFilesVersions,
      $this->tableMimeTypes, $versionId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['FILENAME'] = $this->getFileName($row['file_id'], $versionId);
        $row['DERIVED'] = $this->fileIsDerived($row['file_id']);
        $row['DERIVATIONS'] = $this->fileCountDerivations($row['file_id']);
        $row['WIDTH'] = $row['width'];
        $row['HEIGHT'] = $row['height'];
        return $row;
      }
    }
    return FALSE;
  }

  /**
   * find out whether a file is derived
   *
   * use this method to find out whether a file is an original upload or a
   * derivation
   *
   * @param string $fileId a file id
   * @return boolean TRUE if the file is derived, otherwise FALSE
   */
  function fileIsDerived($fileId)
  {
    $sql = "SELECT COUNT(*) FROM %s WHERE child_file_id = '%s'";
    $params = array($this->tableFilesDerivations, $fileId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
   * find out which file is the topmost in derivation hierarchy
   *
   * use this method to get the original source file a file is derived of
   * (by whatever degree)
   *
   * @param string $fileId file id
   * @return mixed $headFileId if found, otherwise FALSE
   */
  function getDerivationHeadId($fileId)
  {
    $sql = "SELECT head_file_id FROM %s
             WHERE child_file_id = '%s'
                OR (head_file_id = parent_file_id AND parent_file_id = '%s')";
    $params = array($this->tableFilesDerivations, $fileId, $fileId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
   * tell how many files are derived from a specific file
   *
   * use this method to get the number of files directly derived from a given file
   *
   * @param string $fileId a file id
   * @return integer number of files derived from the specified file
   */
  function fileCountDerivations($fileId)
  {
    $sql = "SELECT COUNT(*) FROM %s WHERE parent_file_id = '%s'";
    $params = array($this->tableFilesDerivations, $fileId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return 0;
  }

  /**
   * fetch a translation of a given file
   *
   * use this method to get a different translation of the file properties
   *
   * @param string $fileId a file id
   * @param integer $lngId language id
   * @return array $result file data: file_id, lng_id, file_title, file_description
   */
  function getFileTrans($fileId, $lngId)
  {
    $result = array();
    if (isset($fileId)) {
      $fileCondition = $this->databaseGetSQLCondition('file_id', $fileId);
      $sql = "SELECT file_id, lng_id, file_title, file_description
                FROM %s
              WHERE $fileCondition
                AND lng_id = %d";
      $params = array($this->tableFilesTrans, $lngId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['file_id']] = $row;
        }
      }
    }
    return $result;
  }

  /**
   * get files by folder
   *
   * use this method to get files from a specific folder, using limit and offset
   * as well as sorting and order
   *
   * @param mixed $folderId a single folder id or an array thereof
   * @param integer $limit number of files to return
   * @param integer $offset start at this position in the result
   * @param string $sort sort results by 'name' OR 'size' of the file
   * @param string $order direction of ordering, 'ASC' OR 'DESC'
   * @return array $result file data
   *  * file_id, folder_id, file_name, file_date, file_size, file_sort,
   *  * current_version_id, surfer_id, mimetype_id, mimetype, mimetype_icon
   */
  function getFiles($folderId, $limit = NULL, $offset = 0, $sort = 'name', $order = 'ASC')
  {
    $result = array();
    $order = strtoupper($order);
    $order = ($order == 'ASC' || $order == 'DESC') ? $order : 'ASC';
    switch ($sort) {
      default:
      case 'name':
        $orderString = " ORDER BY f.file_name " . $order;
        break;
      case 'size':
        $orderString = " ORDER BY f.file_size " . $order;
        break;
      case 'date':
        $orderString = " ORDER BY f.file_date " . $order;
    }
    $folderId = (is_array($folderId)) ? $folderId : (int)$folderId;
    $folderCondition = $this->databaseGetSQLCondition('folder_id', $folderId);
    $sql = "SELECT f.file_id, f.folder_id, f.file_name, f.file_date, f.file_created, f.file_size,
                   f.file_sort, f.current_version_id, f.surfer_id,
                   f.file_source, f.file_source_url,
                   f.width, f.height,
                   m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext,
                   m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
              FROM %s f
              LEFT OUTER JOIN %s m ON (f.mimetype_id = m.mimetype_id)
             WHERE $folderCondition
                   $orderString
           ";
    $params = array($this->tableFiles, $this->tableMimeTypes);
    if (NULL !== $limit) {
      $res = $this->databaseQueryFmt($sql, $params, $limit, $offset);
    } else {
      $res = $this->databaseQueryFmt($sql, $params);
    }
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['file_id']] = $row;
      }
      $this->absCount = $res->absCount();
    }
    return $result;
  }

  /**
   * get files by folder including the translation data
   *
   * use this method to get files from a specific folder, using limit and offset
   * as well as sorting and order
   *
   * @param $lngId
   * @param $folderId
   * @param mixed $limit optional, default value NULL
   * @param integer $offset optional, default value 0
   * @param string $sort optional, default value 'name'
   * @param string $order direction of ordering, 'ASC' OR 'DESC'
   * @access public
   * @return array
   */
  function getFilesTranslated(
    $lngId, $folderId, $limit = NULL, $offset = NULL, $sort = 'name', $order = 'ASC'
  )
  {
    $result = array();
    $order = strtoupper($order);
    $order = ($order == 'ASC' || $order == 'DESC') ? $order : 'ASC';
    switch ($sort) {
      default:
      case 'sort':
        $orderString = " ORDER BY f.file_sort " . $order;
        break;
      case 'title':
        $orderString = " ORDER BY ft.file_title " . $order . ", f.file_name " . $order;
        break;
      case 'name':
        $orderString = " ORDER BY file_name " . $order;
        break;
      case 'size':
        $orderString = " ORDER BY f.file_size " . $order;
        break;
      case 'date':
        $orderString = " ORDER BY f.file_date " . $order;
    }
    $folderId = (is_array($folderId)) ? $folderId : (int)$folderId;
    $folderCondition = str_replace(
      '%',
      '%%',
      $this->databaseGetSQLCondition('folder_id', $folderId)
    );
    $sql = "SELECT f.file_id, f.folder_id, f.file_name, f.file_date, f.file_size,
                   f.file_sort, f.current_version_id, f.surfer_id,
                   f.file_source, f.file_source_url,
                   ft.file_title, ft.file_description,
                   m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext,
                   m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
              FROM %s f
              LEFT OUTER JOIN %s ft ON (ft.file_id = f.file_id AND ft.lng_id = '%d')
              LEFT OUTER JOIN %s m ON (f.mimetype_id = m.mimetype_id)
             WHERE $folderCondition
                   $orderString";
    $params = array($this->tableFiles, $this->tableFilesTrans, $lngId, $this->tableMimeTypes);
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['file_id']] = $row;
      }
      $this->absCount = $res->absCount();
    }
    return $result;
  }

  /**
   * fetch files by a list of file ids
   *
   * use this method to get files by their ids
   *
   * @param mixed $fileIds a single file id or a list thereof
   * @param int $lngId
   * @return array $result file data
   *   * file_id, folder_id, file_name, file_date, file_size, file_sort,
   *   * current_version_id, surfer_id, mimetype_id, mimetype, mimetype_icon
   */
  function getFilesById($fileIds, $lngId = 0)
  {
    $result = array();
    if (is_array($fileIds) && count($fileIds) > 0) {
      $fileCondition = $this->databaseGetSQLCondition('f.file_id', $fileIds);
      $sql = "SELECT f.file_id, f.folder_id, f.file_name,
                     f.width, f.height,
                     f.file_date, f.file_created, f.file_size,
                     f.file_sort, f.current_version_id, f.surfer_id,
                     f.file_source, f.file_source_url,
                     ft.file_title, ft.file_description,
                     m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext,
                     m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
                FROM %s f
                LEFT OUTER JOIN %s ft ON (ft.file_id = f.file_id AND ft.lng_id = '%d')
                LEFT OUTER JOIN %s m ON (f.mimetype_id = m.mimetype_id)
              WHERE $fileCondition";
      $params = array(
        $this->tableFiles,
        $this->tableFilesTrans,
        $lngId,
        $this->tableMimeTypes
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['file_id']] = $row;
        }
        $this->absCount = $res->count();
      }
    }
    return $result;
  }

  /**
   * fetch files by surfer id
   *
   * use this method to get only files owned by a specific surfer
   *
   * @param string $surferId surfer id
   * @return array $result file data
   *   * file_id, folder_id, file_name, file_date, file_size, file_sort,
   *   * current_version_id, surfer_id, mimetype_id, mimetype, mimetype_icon
   */
  function getFilesBySurferId($surferId)
  {
    $result = array();
    if (isset($surferId) && $surferId != '') {
      $surferCondition = $this->databaseGetSQLCondition('f.surfer_id', $surferId);
      $sql = "SELECT f.file_id, f.folder_id, f.file_name, f.file_date, f.file_created, f.file_size,
                    f.file_sort, f.current_version_id, f.surfer_id,
                    f.file_source, f.file_source_url,
                    m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext,
                    m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
                FROM %s f
                LEFT OUTER JOIN %s m ON (f.mimetype_id = m.mimetype_id)
              WHERE $surferCondition
              ORDER BY file_name ASC
            ";
      $params = array($this->tableFiles, $this->tableMimeTypes);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['file_id']] = $row;
        }
        $this->absCount = $res->count();
      }
    }
    return $result;
  }

  /**
   * Returns the number of files in the MediaDB associated with the mimetype.
   *
   * @param integer $mimeTypeId the id of the mimetype
   * @return array $res number of linked files | 0 iff query has failed
   */
  function countFilesOfMimetype($mimeTypeId)
  {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE mimetype_id = %d
           ";
    $params = array($this->tableFiles, $mimeTypeId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchRow();
    }
    return 0;
  }

  /**
   * fetch information on a file's owner
   *
   * use this method if you need information on the owner of a file
   *
   * @param string $fileId a file id
   * @param integer $versionId a version id (optional)
   * @return array $result surfer data
   *  * surfer_id, surfer_handle, surfer_givenname, surfer_surname
   */
  function getFileOwnerData($fileId, $versionId = NULL)
  {
    $result = array();
    if (NULL !== $versionId) {
      $sql = "SELECT s.surfer_id, s.surfer_handle, s.surfer_givenname, s.surfer_surname
                FROM %s fv, %s s
               WHERE fv.file_id = '%s' AND fv.version_id = %d AND fv.surfer_id = s.surfer_id";
      $params = array($this->tableFilesVersions, $this->tableSurfer, $fileId, $versionId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result = $row;
        } else {
          // version is most likely the current one
          return $this->getFileOwnerData($fileId);
        }
      }
    } else {
      $sql = "SELECT s.surfer_id, s.surfer_handle, s.surfer_givenname, s.surfer_surname
              FROM %s f, %s s
              WHERE f.file_id = '%s' AND f.surfer_id = s.surfer_id
             ";
      $params = array($this->tableFiles, $this->tableSurfer, $fileId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result = $row;
        }
      }
    }
    return $result;
  }

  /**
   * get files specified by a tag
   *
   * use this method to get files by tag
   *
   * @TODO FIXME DR: this method thrown an sql error unknown  column f.mimetype_id
   *             which exists, so it must be a problem due to the joining...
   *
   * @param mixed $tagId a single tag id or an array thereof
   * @param integer $limit number of files to return
   * @param integer $offset start at this position in the result
   * @param string $sort sort results by 'name' OR 'size' of the file
   * @param string $order direction of ordering, 'ASC' OR 'DESC'
   * @return array $result file data
   *  * file_id, folder_id, file_name, file_date, file_size, file_sort,
   *  * current_version_id, surfer_id, mimetype_id, mimetype, mimetype_icon
   */
  function getFilesByTag($tagId, $limit = NULL, $offset = 0, $sort = 'name', $order = 'ASC')
  {
    $result = array();
    if (isset($tagId) && ((is_array($tagId) && count($tagId) > 0) || $tagId != '')) {
      $tagCondition = $this->databaseGetSQLCondition('tl.tag_id', $tagId);
      $order = strtoupper($order);
      $order = ($order == 'ASC' || $order == 'DESC') ? $order : 'ASC';
      switch ($sort) {
        default:
        case 'name':
          $orderString = " ORDER BY file_name " . $order;
          break;
        case 'date':
          $orderString = " ORDER BY f.file_date " . $order;
          break;
        case 'size':
          $orderString = " ORDER BY f.file_size " . $order;
          break;
      }
      $sql = "SELECT f.file_id, f.folder_id, f.file_name, f.file_date, f.file_created, f.file_size,
                    f.file_sort, f.current_version_id, f.surfer_id,
                    f.file_source, f.file_source_url,
                    m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext,
                    m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
                FROM %s f
               INNER JOIN %s tl ON tl.link_type = 'media'
                               AND $tagCondition
                               AND tl.link_id = f.file_id
                LEFT OUTER JOIN %s m ON m.mimetype_id = f.mimetype_id
                    $orderString
            ";
      $params = array($this->tableFiles, $this->tableTagLinks, $this->tableMimeTypes);
      if (NULL !== $limit) {
        $res = $this->databaseQueryFmt($sql, $params, $limit, $offset);
      } else {
        $res = $this->databaseQueryFmt($sql, $params);
      }
      if ($res) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['file_id']] = $row;
        }
        $this->absCount = $res->absCount();
      }
    }
    return $result;
  }

  /**
   * find file by name, mimegroup or age
   *
   * use this method to find files by specific criteria, e.g. name, id, extension,
   * mimegroup, date
   *
   * @param array $searchParams cumulative, may contain the following entries:
   *   * q => searchstring, ext => list of extensions, mimegroup => mimegroup_id,
   *   * younger => timestamp, older => timestamp
   * @param integer $limit number of files to return
   * @param integer $offset start at this position in the result
   * @param string $sort sort results by 'name' OR 'size' of the file
   * @param string $order direction of ordering, 'ASC' OR 'DESC'
   * @return array $result file data
   *  * file_id, folder_id, file_name, file_date, file_size, file_sort,
   *  * current_version_id, surfer_id, mimetype_id, mimetype, mimetype_icon
   */
  function findFiles($searchParams, $limit = NULL, $offset = 0, $sort = 'name', $order = 'ASC')
  {
    $result = array();
    $order = strtoupper($order);
    $order = ($order == 'ASC' || $order == 'DESC') ? $order : 'ASC';
    switch ($sort) {
      default:
      case 'name':
        $orderString = " ORDER BY f.file_name " . $order;
        break;
      case 'date':
        $orderString = " ORDER BY f.file_date " . $order;
        break;
      case 'size':
        $orderString = " ORDER BY f.file_size " . $order;
        break;
    }
    if (!empty($searchParams['q'])) {
      $parser = new searchStringParser();
      $textFilter = $parser->getSQL(
        $searchParams['q'],
        array('f.file_name', 'f.file_id', 'f.file_keywords', 'f.file_source'),
        PAPAYA_SEARCH_BOOLEAN
      );
      if ($textFilter) {
        $condition = $textFilter;
        $translatedTextFilter = $parser->getSQL(
          $searchParams['q'],
          array('file_title', 'file_description'),
          PAPAYA_SEARCH_BOOLEAN
        );
        if ($translatedTextFilter) {
          $condition .= sprintf(
            ' OR file_id IN (SELECT file_id FROM %s WHERE %s)',
            $this->databaseGetTablename(Tables::MEDIA_FILE_TRANSLATIONS),
            $translatedTextFilter
          );
        }
        if (preg_match('(^\\s*(?P<id>[a-f\\d]{32})(?:v\d+)?\\s*$)', $searchParams['q'], $matches)) {
          $condition .= " OR " . $this->databaseGetSqlCondition('file_id', $matches['id']);
        }
        $conditions[] = str_replace('%', '%%', '(' . $condition . ')');
      }
    }
    if (isset($searchParams['ext'])) {
      if (is_array($searchParams['ext'])) {
        foreach ($searchParams['ext'] as $query) {
          if (preg_match('~[a-zA-Z0-9]+~', $query)) {
            $queries[] = sprintf(
              " f.file_name LIKE '%%%%.%s' OR f.file_id LIKE '%%%%.%s' ",
              $this->escapeStr($query),
              $this->escapeStr($query)
            );
          }
        }
        if (isset($queries) && is_array($queries) && count($queries) > 0) {
          $conditions[] = ' ( ' . implode(' OR ', $queries) . ' ) ';
        }
      }
    }
    if (isset($searchParams['mimegroup']) && $searchParams['mimegroup'] > 0) {
      $conditions[] = sprintf(" m.mimegroup_id = %d", $searchParams['mimegroup']);
    }
    if (!empty($searchParams['younger'])) {
      $conditions[] = sprintf(
        " (f.file_date >= %d OR f.file_created >= '%s' AND f.file_created <> '')",
        \Papaya\Utility\Date::stringToTimestamp($searchParams['younger']),
        \Papaya\Utility\Date::stringToIso($searchParams['younger'])
      );
    }
    if (!empty($searchParams['older'])) {
      $conditions[] = sprintf(
        " (f.file_date <= %d OR (f.file_created <= '%s' AND f.file_created <> ''))",
        \Papaya\Utility\Date::stringToTimestamp($searchParams['older']),
        \Papaya\Utility\Date::stringToIso($searchParams['older'])
      );
    }
    if (!empty($searchParams['smaller'])) {
      $conditions[] = sprintf(
        " (f.file_size > %s) ", $searchParams['smaller'] * 1024
      );
    }
    if (!empty($searchParams['bigger'])) {
      $conditions[] = sprintf(
        " (f.file_size < %s) ", $searchParams['bigger'] * 1024
      );
    }
    if (isset($searchParams['folders']) && is_array($searchParams['folders'])) {
      $conditions[] = $this->databaseGetSQLCondition('f.folder_id', $searchParams['folders']);
    }
    if (isset($searchParams['owner']) && $searchParams['owner'] != '') {
      if ($surfers = $this->findSurfers($searchParams['owner'])) {
        $conditions[] = $this->databaseGetSQLCondition('f.surfer_id', array_keys($surfers));
      }
    }

    if (isset($conditions) && count($conditions) > 0) {
      // if you want to allow "match any" instead of "match all", change this to OR
      $conditionString = ' AND ' . implode(' AND ', $conditions);
    } else {
      $conditionString = '';
    }
    $sql = "SELECT f.file_id, f.folder_id, f.file_name,
                   f.file_date, f.file_created, f.file_size,
                   f.width, f.height,
                   f.file_sort, f.current_version_id, f.surfer_id, f.file_source, f.file_source_url,
                   m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext,
                   m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
              FROM %s f, %s m
             WHERE f.mimetype_id = m.mimetype_id
                   $conditionString
                   $orderString
           ";
    $params = array($this->tableFiles, $this->tableMimeTypes);
    if (NULL !== $limit) {
      $res = $this->databaseQueryFmt($sql, $params, $limit, $offset);
    } else {
      $res = $this->databaseQueryFmt($sql, $params);
    }
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['file_id']] = $row;
      }
      $this->absCount = $res->absCount();
    }
    return $result;
  }

  /**
   * fetch random images from a folder of the mediaDB
   *
   * use this method to get (int)n random files from a folder
   *
   * @param integer $folderId a folder id
   * @param integer $lngId language id for file properties
   * @param integer $number number of random images to fetch
   * @return array|bool
   */
  function getRandomImages($folderId, $lngId, $number = 1)
  {
    $randFunc = $this->databaseGetSQLSource('RANDOM');
    $sql = "SELECT f.file_id, f.folder_id, f.file_name, f.file_date, f.file_created, f.file_size,
                   f.file_sort, f.current_version_id, f.surfer_id, f.file_source, f.file_source_url,
                   ft.file_title, ft.file_description,
                   m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext,
                   m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
              FROM %s f
             INNER JOIN %s m ON f.mimetype_id = m.mimetype_id
              LEFT OUTER JOIN %s ft ON (f.file_id = ft.file_id AND ft.lng_id = %d)
             WHERE f.folder_id = %d
               AND (f.file_name LIKE '%%.jpg' OR
                    f.file_name LIKE '%%.png' OR
                    f.file_name LIKE '%%.gif')
             ORDER BY $randFunc";
    $params = array(
      $this->tableFiles, $this->tableMimeTypes, $this->tableFilesTrans, $lngId, $folderId
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $number)) {
      $result = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($number == 1) {
          return $row;
        }
        $result[] = $row;
      }
      return $result;
    }
    return FALSE;
  }

  /**
   * get list of file versions for a given file id
   *
   * use this method to get all file versions for a file
   *
   * @param string $fileId a file id
   * @return array $result list of file versions data
   *   * file_id, version_id, file_name, surfer_id, file_size, version_time
   */
  function getFileVersions($fileId)
  {
    $result = array();
    if (!is_array($fileId) && $fileId != '') {
      $sql = "SELECT fv.file_id, fv.version_id, fv.file_name, fv.file_created, fv.surfer_id,
                     fv.file_size, fv.version_time, fv.file_date,
                     fv.file_source, fv.file_source_url,
                     f.file_sort, f.current_version_id, f.surfer_id,
                     m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext,
                     m.range_support, m.shaping, m.shaping_limit, m.shaping_offset,
                     m.download_octet_stream
                FROM %s fv, %s m, %s f
               WHERE fv.file_id = '%s'
                 AND fv.mimetype_id = m.mimetype_id
                 AND f.file_id = fv.file_id
               ORDER BY version_id DESC
            ";
      $params = array($this->tableFilesVersions, $this->tableMimeTypes, $this->tableFiles, $fileId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['version_id']] = $row;
        }
      }
    }
    return $result;
  }

  /**
   * get list of file derivations for a given file id
   *
   * use this function to get all files associated by derivation (up and down)
   *
   * @param string $fileId a file id
   * @return array $result list of parent_file_id => array(child_file_id => 1) mappings
   */
  function getFileDerivations($fileId)
  {
    $result = array();
    $sql = "SELECT DISTINCT
                   d2.head_file_id, d2.parent_file_id,
                   d2.parent_file_version_id, d2.child_file_id
              FROM %s d1, %s d2
             WHERE (d1.child_file_id = '%s' OR d2.head_file_id = '%s')
               AND d1.head_file_id = d2.head_file_id
           ";
    $params = array($this->tableFilesDerivations, $this->tableFilesDerivations, $fileId, $fileId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['parent_file_id']][$row['child_file_id']] = 1;
      }
    }
    return $result;
  }

  // --------------------------------- FOLDERS ---------------------------------

  /**
   * get a list of folders
   *
   * use this method to get a list of folders
   *
   * @param integer $lngId language id
   * @param array $folderIds restrict result to these folders
   * @return array $result list of folder data
   *   * folder_id, parent_id, parent_path, permission_mode, folder_name, lng_id
   */
  function getFolders($lngId = NULL, $folderIds = NULL)
  {
    $result = array();
    $folderCondition = (NULL !== $folderIds)
      ? $this->databaseGetSQLCondition('ft.folder_id', $folderIds) : ' 1=1';
    $languageCondition = (NULL !== $lngId)
      ? $this->databaseGetSQLCondition('lng_id', $lngId) : ' 1=1';
    $sql = "SELECT DISTINCT f.folder_id, f.parent_id, f.parent_path, f.permission_mode,
                   ft.folder_name, ft.lng_id
              FROM %s f
              LEFT OUTER JOIN %s ft ON (ft.folder_id = f.folder_id AND $languageCondition)
             WHERE $folderCondition
             ORDER BY ft.folder_name ASC
           ";
    $params = array($this->tableFolders, $this->tableFoldersTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['folder_id']] = $row;
      }
    }
    return $result;
  }

  /**
   * get subfolders for a given folder id
   *
   * use this method to get a branch of the folder tree
   *
   * @param integer $lngId language id
   * @param integer $parentId folder id to get subfolders for
   * @return array $result list of subfolders
   *   * folder_id, parent_id, parent_path, permission_mode, folder_name, lng_id
   */
  function getSubFolders($lngId, $parentId)
  {
    $result = array();
    $folderCondition = (NULL !== $parentId)
      ? $this->databaseGetSQLCondition('f.parent_id', $parentId) : ' 1=1';
    $languageCondition = $this->databaseGetSQLCondition('lng_id', $lngId);
    $sql = "SELECT f.folder_id, f.parent_id, f.parent_path, f.permission_mode,
                   ft.folder_name, ft.lng_id
              FROM %s f
              LEFT OUTER JOIN %s ft ON (ft.folder_id = f.folder_id AND $languageCondition)
             WHERE $folderCondition
           ";
    $params = array($this->tableFolders, $this->tableFoldersTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['folder_id']] = $row;
      }
    }
    return $result;
  }

  /**
   * count subfolders for a given list of folders (result of getFolders())
   *
   * use this method to extend the given folders array by a file count
   *
   * @param array $folders list of folders; $folders[$folderId]['COUNT'] will be added
   */
  function countSubFolders(&$folders)
  {
    if (is_array($folders) && count($folders) > 0) {
      $folderCondition = $this->databaseGetSQLCondition('parent_id', array_keys($folders));
      $sql = "SELECT COUNT(*) AS count, parent_id
                FROM %s
               WHERE $folderCondition
               GROUP BY parent_id
             ";
      $params = array($this->tableFolders);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $folders[$row['parent_id']]['COUNT'] = $row['count'];
        }
      }
    }
  }

  /**
   * fetch data for a given folder id
   *
   * use this method if you need information on a folder
   *
   * @param integer $folderId a folder id
   * @return array $result list of folder data by language id lng_id => array(
   *   * folder_id, parent_id, parent_path, permission_mode, folder_name, lng_id)
   */
  function getFolder($folderId)
  {
    $result = array();
    $folderCondition = $this->databaseGetSQLCondition('f.folder_id', $folderId);

    $sql = "SELECT f.folder_id, f.parent_id, f.parent_path, f.permission_mode,
                   ft.folder_name, ft.lng_id
              FROM %s f
              LEFT OUTER JOIN %s ft ON (ft.folder_id = f.folder_id)
             WHERE $folderCondition
           ";
    $params = array($this->tableFolders, $this->tableFoldersTrans);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['lng_id']] = $row;
      }
    }
    return $result;
  }

  /**
   * get folder permissions for a given folder
   *
   * @param integer $folderId a folder id
   * @param boolean $forceUpdate whether to use cached data if possible or not
   * @return array folder permissions
   */
  function getFolderPermissions($folderId, $forceUpdate = FALSE)
  {
    if (NULL !== $folderId) {
      if ($forceUpdate || !isset($this->folderPermissions[$folderId])) {
        $this->loadFolderPermissions($folderId, $forceUpdate);
      }
      if (isset($this->folderPermissions[$folderId])) {
        return $this->folderPermissions[$folderId];
      }
    }
    return FALSE;
  }

  /**
   * get folder permissions for a list of folders
   *
   * @param array $folderIds an array of folder ids
   * @param boolean $forceUpdate whether to use cached data if possible or not
   * @return array folders permissions
   */
  function getMultipleFolderPermissions($folderIds, $forceUpdate = FALSE)
  {
    $result = FALSE;
    if (is_array($folderIds) && count($folderIds) > 0) {
      $this->loadMultipleFolderPermissions($folderIds, $forceUpdate);
      foreach ($folderIds as $folderId) {
        if (isset($this->folderPermissions[$folderId])) {
          $result[$folderId] = $this->folderPermissions[$folderId];
        }
      }
    }
    return $result;
  }

  /**
   * load permissions for a given folder from the database
   *
   * @access private
   * @param integer $folderId a folder id
   * @param boolean $forceUpdate whether to use cached data if possible or not
   * @return array $result list of permissions permission_type => array(permission_value => 1)
   */
  function loadFolderPermissions($folderId, $forceUpdate = FALSE)
  {
    $this->loadMultipleFolderPermissions(array($folderId), $forceUpdate);
  }

  /**
   * load permissions for a list of folders from the database
   *
   * @access private
   * @param array $folderIds list of folder ids
   * @param boolean $forceUpdate whether to use cached data if possible or not
   * @return array $result list of folders permissions
   *   permission_type => array(permission_value => 1)
   */
  function loadMultipleFolderPermissions($folderIds, $forceUpdate = FALSE)
  {
    if ($forceUpdate) {
      $this->folderPermissions = array();
    }
    if (is_array($folderIds) && count($folderIds) > 0) {
      $foldersToCheck = array();
      foreach ($folderIds as $folderId) {
        if ($forceUpdate || !isset($this->folderPermissions[$folderId])) {
          $foldersToCheck[] = $folderId;
          if (isset($this->folderPermissions[$folderId])) {
            unset($this->folderPermissions[$folderId]);
          }
        }
      }
      if (is_array($foldersToCheck) && count($foldersToCheck) > 0) {
        $folderCondition = $this->databaseGetSQLCondition('folder_id', $foldersToCheck);
        $sql = "SELECT folder_id, permission_type, permission_value
                  FROM %s
                WHERE $folderCondition";
        $params = array($this->tableFoldersPermissions);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $folderId = $row['folder_id'];
            $permType = $row['permission_type'];
            $permValue = $row['permission_value'];
            $this->folderPermissions[$folderId][$permType][$permValue] = 1;
          }
        }
      }
    }
    return FALSE;
  }


  /**
   * calculate permissions for a folder
   *
   * @param integer $folderId a folder id
   * @param boolean $forceUpdate whether to use cached data if possible or not
   * @return array $result permissions
   *   folder_id => array(permission => array(user_id => permission_mode))
   */
  function calculateFolderPermissions($folderId, $forceUpdate = FALSE)
  {
    $folderPermissions = $this->calculateMultipleFolderPermissions(array($folderId), $forceUpdate);
    return $folderPermissions[(int)$folderId];
  }

  /**
   * calculate permissions for a list of folders
   *
   * @param array $folderIds list of folder ids
   * @param boolean $forceUpdate whether to use cached data if possible or not
   * @return array $result permissions
   *   folder_id => array(permission => array(user_id => permission_mode))
   */
  function calculateMultipleFolderPermissions($folderIds, $forceUpdate = FALSE)
  {
    $result = array();
    $folders = $this->getFolders();
    foreach ($folderIds as $folderId) {
      if ($folderId > 0 && isset($folders[$folderId])) {
        $folder = $folders[$folderId];
        if ($folder['permission_mode'] === Folder::PERMISSION_MODE_DEFINE ||
          $folder['permission_mode'] === Folder::PERMISSION_MODE_EXTEND) {
          $permissions[$folderId][Folder::PERMISSION_MODE_DEFINE][] = $this->getFolderPermissions($folderId, $forceUpdate);
        }
        if ($folder['permission_mode'] === Folder::PERMISSION_MODE_INHERIT ||
          $folder['permission_mode'] === Folder::PERMISSION_MODE_EXTEND) {
          $parents = explode(';', $folder['parent_path']);
          array_pop($parents);
          $folderPermissions = $this->getMultipleFolderPermissions($parents, $forceUpdate);
          while ($parentId = array_pop($parents)) {
            if ($parentId >= 0 && isset($folders[$parentId]) &&
              isset($folderPermissions[$parentId])) {
              switch ($folders[$parentId]['permission_mode']) {
                case Folder::PERMISSION_MODE_DEFINE:
                  $permissions[$folderId]['inherited'][] = $folderPermissions[$parentId];
                  $parents = array();
                  break;
                case Folder::PERMISSION_MODE_INHERIT:
                  break;
                case Folder::PERMISSION_MODE_EXTEND:
                  $permissions[$folderId]['inherited'][] = $folderPermissions[$parentId];
                  break;
              }
            }
          }
        }
      }

      // this is to pass the permission mode to the result
      if (isset($permissions) && is_array($permissions) && count($permissions) > 0) {
        foreach ($permissions as $permissionFolderId => $folderData) {
          foreach ($folderData as $mode => $modePerms) {
            foreach ($modePerms as $partPerms) {
              if (isset($partPerms) && is_array($partPerms) && count($partPerms) > 0) {
                foreach ($partPerms as $permType => $singlePermission) {
                  foreach ($singlePermission as $permissionId => $value) {
                    if (!isset($result[$permType][$permissionId]) ||
                      $result[$permissionFolderId][$permType][$permissionId] !== Folder::PERMISSION_MODE_DEFINE) {
                      $result[$permissionFolderId][$permType][$permissionId] = $mode;
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    // administrators may do everything, and this may not be changed
    foreach ($folderIds as $folderId) {
      $result[$folderId]['user_view'][-1] = 'inherited';
      $result[$folderId]['user_edit'][-1] = 'inherited';
    }
    return $result;
  }

  /**
   * get surfers by handle, givenname or surname
   *
   * use this function to search for surfers by handle or name
   *
   * @param string $queryString string to search for in handle or name
   * @return array|FALSE $result array id => (id, handle, givenname, surname, email)
   */
  function findSurfers($queryString)
  {
    $result = FALSE;
    $sql = "SELECT surfer_id, surfer_handle, surfer_givenname, surfer_surname,
                   surfer_email
              FROM %s
             WHERE surfer_handle LIKE '%%%s%%'
                OR surfer_givenname LIKE '%%%s%%'
                OR surfer_surname LIKE '%%%s%%'
           ";
    $params = array($this->tableSurfer, $queryString, $queryString, $queryString);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $result = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['surfer_id']] = $row;
      }
      return $result;
    }
    return $result;
  }

  // ---------------------------- AIDING FUNCTIONS -----------------------------

  /**
   * determine file properties for a given local file
   *
   * @param string $fileLocation location of file on local disk
   * @param string $originalFileName original name of the file (no path)
   * @param array $meta default for meta information
   * @return array $properties list of file properties
   *   * size, extension, mimetype_id, mimetype, metadata (yet to conceive)
   *   * for images:  width, height, imagetype, bits, channels
   */
  function getFileProperties($fileLocation, $originalFileName, array $meta = array())
  {
    $this->initializeMimeObject();

    $properties = iterator_to_array(new \Papaya\Media\File\Properties($fileLocation, $originalFileName));
    if (empty($properties['extension'])) {
      $properties['extension'] = $this->getFileExtension($originalFileName);
    }
    if (
      ($mimeType = $this->mimeObj->getMimeType($properties['mimetype'])) ||
      ($mimeType = $this->mimeObj->getMimeTypeByExtension($properties['extension']))
    ) {
      $properties['mimetype_id'] = $mimeType['mimetype_id'];
      $properties['mimetype'] = $mimeType['mimetype'];
    } else {
      $properties['mimetype_id'] = 0;
    }
    $properties['metadata'] = '';
    $properties['file_source'] = \Papaya\Utility\Arrays::get($meta, 'file_source', '');
    $properties['file_source_url'] = \Papaya\Utility\Arrays::get($meta, 'file_source_url', '');
    $properties['file_keywords'] = \Papaya\Utility\Arrays::get($meta, 'file_keywords', '');
    return $properties;
  }


  /**
   * initializes mime object, $this->mimeObj (base_mediadb_mimetypes)
   *
   * @access private
   */
  function initializeMimeObject()
  {
    $this->mimeObj = base_mediadb_mimetypes::getInstance();
  }

  /**
   * calculate full path to the file on disk for a given file id and version
   *
   * use this method to get the location of a file
   *
   * @param string $fileId a file id
   * @param integer $versionId version id
   * @param bool $createDirectories
   * @return bool|string
   */
  function getFileName($fileId, $versionId = NULL, $createDirectories = FALSE)
  {
    if (NULL === $versionId) {
      $file = $this->getFile($fileId);
      $versionId = $file['current_version_id'];
    }
    if ($fileId != '' &&
      $versionId > 0 &&
      ($path = $this->getFilePath($fileId, $createDirectories))) {
      return $path . $fileId . 'v' . $versionId;
    } else {
      return FALSE;
    }
  }

  /**
   * calculate path for a given file id, create missing directories if necessary
   *
   * @access private
   * @param string $fileId a file id
   * @param bool $createDirectories
   * @return mixed path for the given file id if found, otherwise FALSE
   */
  function getFilePath($fileId, $createDirectories = FALSE)
  {
    if (!preg_match('~^[0-9A-Fa-f]{32}~', $fileId)) {
      $this->logMsg(
        MSG_ERROR,
        PAPAYA_LOGTYPE_MODULES,
        sprintf('Invalid FileId "%s".', $fileId)
      );
      return FALSE;
    }
    $currentMask = umask(0);
    $path = str_replace('\\', '/', $this->dataDirectory);
    if (substr($path, -1) != '/') {
      $path .= '/';
    }
    if ($createDirectories) {
      $this->_ensureLocalDirectory($path, $createDirectories, $currentMask);
    }
    $subDirectories = $this->papaya()->options->get(
      CMSSettings::MEDIADB_SUBDIRECTORIES, 1, new \Papaya\Filter\IntegerValue(1, 10)
    );
    for ($i = 0; $i < $subDirectories; $i++) {
      $path .= $fileId[$i] . '/';
      if ($createDirectories) {
        if (!$this->_ensureLocalDirectory($path, $createDirectories, $currentMask)) {
          $this->logMsg(
            MSG_ERROR,
            PAPAYA_LOGTYPE_MODULES,
            sprintf('Could not create path "%s".', $path . $fileId[$i])
          );
          if (isset($currentMask)) {
            umask($currentMask);
          }
          return FALSE;
        }
      }
    }
    if (isset($currentMask)) {
      umask($currentMask);
    }
    return $path;
  }

  /**
   * Create local directory
   * @param string $directory
   * @param $createDirectory
   * @param integer $oldMask
   * @return FALSE
   */
  function _ensureLocalDirectory($directory, $createDirectory, &$oldMask)
  {
    if (file_exists($directory) && is_dir($directory)) {
      return TRUE;
    } elseif ($createDirectory) {
      if (is_null($oldMask)) {
        $oldMask = umask(0);
      }
      return mkdir($directory, 0777);
    } else {
      return FALSE;
    }
  }

  /**
   * get the file extension for a given filename
   *
   * @param string $fileName name of file
   * @return string file extension or empty string if none was found
   */
  function getFileExtension($fileName)
  {
    if ($fileName != '' && ($pos = papaya_strings::strrpos($fileName, '.'))) {
      return papaya_strings::substr($fileName, $pos + 1);
    } else {
      return '';
    }
  }

  /**
   * format file size as a human readable string
   *
   * @param integer $size files sizes
   * @return string $size size with suffix GB, MB, kB or B
   */
  function formatFileSize($size)
  {
    if ($size > 1073741824) {
      $size = round($size / 1073741824, 2) . ' GB';
    } elseif ($size > 1048576) {
      $size = round($size / 1048576, 2) . ' MB';
    } elseif ($size > 1024) {
      $size = round($size / 1024, 2) . ' kB';
    } else {
      $size = round($size) . ' B';
    }
    return $size;
  }

  /**
   * parses a user input date string to get a unix timestamp
   *
   * @param string $date a date string d.m.Y H:i:s OR m/d/Y H:i:s OR Y-m-d H:i:s
   * @return mixed timestamp if $date was matched, otherwise FALSE
   */
  function parseDate($date)
  {
    return \Papaya\Utility\Date::stringToTimestamp($date);
  }

  /**
   * generates an flv viewer to display flv files
   *
   * currently uses the flvplayer {@link http://www.jeroenwijering.com/}
   *
   * @param string $fileId file id
   * @param integer $width viewer width
   * @param integer $height viewer height
   * @param string $noFlash show this if flash is disabled
   * @param string $bgColor background color
   * @param array $configuration (additional player configuration)
   * @param string $scale scale attribute, defaults to 'noscale'
   * @param string $svalign
   * @param string $shalign
   * @param string $quality flash player quality, defaults to 'high'
   * @return string $result javascript section that loads the player
   */
  function getFlvViewer(
    $fileId,
    $width,
    $height,
    $noFlash = '',
    $bgColor = '#000000',
    $configuration = array(),
    $scale = 'noscale',
    $svalign = '',
    $shalign = '',
    $quality = 'high'
  )
  {
    // get the right mimetype extension if the file is not a flv file
    $fileData = $this->getFile($fileId);
    if (isset($fileData['mimetype_ext']) && strlen($fileData['mimetype_ext']) > 0
      && $fileData['mimetype_ext'] != 'flv') {
      $mimetypeExt = $fileData['mimetype_ext'];
    } else {
      $mimetypeExt = 'flv';
    }

    if ($this->papaya()->request->isAdministration) {
      $flvPlayerFile = 'flash/flvplayer.swf';
      $relativePath = '../';
    } else {
      $flvPlayerFile = 'papaya-themes/' . PAPAYA_LAYOUT_THEME . '/papaya/flvplayer.swf';
      $relativePath = '../../../';
    }
    $configuration['file'] = $relativePath . $this->getWebMediaLink(
        $fileId, 'media', 'video', $mimetypeExt
      );
    if (!empty($configuration['logo'])) {
      if ($logoFile = $this->getFile($configuration['logo'])) {
        $configuration['logo'] = $relativePath . $this->getWebMediaLink(
            $logoFile['file_id'], 'media', $logoFile['file_title'], $logoFile['mimetype_ext']
          );
      }
    } elseif (isset($configuration['logo'])) {
      unset($configuration['logo']);
    }
    if (!empty($configuration['image'])) {
      if ($previewFile = $this->getFile($configuration['image'])) {
        $configuration['image'] = $relativePath . $this->getWebMediaLink(
            $previewFile['file_id'],
            'media',
            isset($previewFile['file_title']) ? $previewFile['file_title'] : '',
            $previewFile['mimetype_ext']
          );
      }
    } elseif (isset($configuration['image'])) {
      unset($configuration['image']);
    }
    $configuration['streamscript'] = 'lighttpd';

    //movie alignment
    $salign = str_replace('m', '', $svalign . $shalign);

    $flvPlayer = new papaya_swfobject();
    $flvPlayer->setSWFParam('allowfullscreen', TRUE);
    $flvPlayer->setSWFParam('quality', $quality);
    $flvPlayer->setSWFParam('scale', $scale);
    $flvPlayer->setSWFParam('bgcolor', $bgColor);
    $flvPlayer->setSWFParam('salign', $salign);
    //set the flash variables
    $flvPlayer->setFlashVars($configuration);
    // tell the user he needs the flash player
    $flvPlayer->setNoFlashMessage($noFlash);
    return $flvPlayer->getXHTML($flvPlayerFile, $width, $height);
  }

  /**
   * escape a given string for javascript
   *
   * @param string $str string to escape
   * @param string $quoteChar
   * @return mixed $result escaped string
   */
  function escapeForJs($str, $quoteChar = "'")
  {
    return str_replace(
      array("\n", "'", "\r", "\\", '</'),
      array("\\n", "\\'", "", "\\\\", "<" . $quoteChar . "+" . $quoteChar . "/"),
      $str
    );
  }


  /**
   * generates a selectbox of mediadb folders
   *
   * Usage:<code>
   * function callbackFolders($name, $field, $data) {
   *   return base_mediadb::callbackFolders($name, $field, $data);
   * }
   * </code>
   */
  function callbackFolders($name, $field, $data)
  {
    if ($this->papaya()->request->isAdministration) {
      $mediaDB = base_mediadb::getInstance();
      $folders = $mediaDB->getFolderComboArray($this->papaya()->administrationLanguage->id);
      $result = sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">' . LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      foreach ($folders as $folderId => $folderName) {
        $selected = ($data == $folderId) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d"%s>%s</option>' . LF,
          (int)$folderId,
          $selected,
          papaya_strings::escapeHTMLChars($folderName)
        );
      }
      $result .= '</select>' . LF;

      return $result;
    }
    return FALSE;
  }

  /**
   * generate a list of folders to be used in a combobox
   *
   * use this method to get an array to use as data for a dialog combo
   * intended for backend use only
   *
   * @param integer $lngId a language id
   * @return array $result list of folderid => folder with structure for combobox
   * @see base_mediadb::getFolderComboSubArray()
   */
  function getFolderComboArray($lngId)
  {
    $folders = $this->getFolders($lngId);
    if (is_array($folders) && count($folders) > 0) {
      $folderRelations = array();
      foreach ($folders as $folderId => $folder) {
        $folderRelations[$folder['parent_id']][$folderId] = $folder['folder_name'];
      }
      $result = $this->getFolderComboSubArray($folderRelations, 0, 0);
    } else {
      $result = array(0 => $this->_gt('Desktop'));
    }
    return $result;
  }

  /**
   * generate list of subfolders for a given folderid
   *
   * @access private
   * @param array $folderRelations list of folder relations (parent_id => child_id)
   * @param integer $folderId a folder id
   * @param integer $indent current indent depth
   * @return array $result list of id => foldername
   */
  function getFolderComboSubArray($folderRelations, $folderId, $indent)
  {
    $result = array();
    if ($folderId == 0) {
      $result[0] = $this->_gt('Desktop');
    }
    if (isset($folderRelations[$folderId])) {
      foreach ($folderRelations[$folderId] as $subFolderId => $folderName) {
        $result[$subFolderId] = " '-- " . $folderName;
        $subFolders = $this->getFolderComboSubArray($folderRelations, $subFolderId, $indent + 1);
        foreach ($subFolders as $fid => $name) {
          $result[$fid] = '| ' . $name;
        }
      }
    }
    return $result;
  }

  /**
   * execute a shell command more secure; derived from php manual comments on shell_exec
   *
   * use this method to execute a shell command securely
   *
   * @see ./video/videoediting_ffmpeg.php
   */
  function execCmd($cmd, &$stdout, &$stderr)
  {
    $result = FALSE;
    if (FALSE === strpos(ini_get('disable_functions'), 'proc_open')) {
      $stdoutFile = tempnam(PAPAYA_PATH_CACHE, "exec");
      $stderrFile = tempnam(PAPAYA_PATH_CACHE, "exec");
      $descriptorSpec = array(
        0 => array("pipe", "r"),
        1 => array("file", $stdoutFile, "w"),
        2 => array("file", $stderrFile, "w")
      );
      $procRes = proc_open($cmd, $descriptorSpec, $pipes);

      if (is_resource($procRes)) {
        fclose($pipes[0]);

        $result = proc_close($procRes);
        $stdout = file_get_contents($stdoutFile);
        $stderr = file_get_contents($stderrFile);
      }

      unlink($stdoutFile);
      unlink($stderrFile);
    }
    return $result;
  }

  /**
   * Get integer content (PHP image type) for a given mimetype
   *
   * @param string $mimeType
   * @return int
   */
  function mimeToInteger($mimeType)
  {
    $mimeTypes = array(
      'image/gif' => IMAGETYPE_GIF,
      'image/jpeg' => IMAGETYPE_JPEG,
      'image/png' => IMAGETYPE_PNG,
      'application/x-shockwave-flash' => IMAGETYPE_SWF,
      'image/psd' => IMAGETYPE_PSD,
      'image/bmp' => IMAGETYPE_BMP,
      'image/tiff' => IMAGETYPE_TIFF_MM,
      //'application/octet-stream' => IMAGETYPE_JPC,
      'image/jp2' => IMAGETYPE_JP2,
      //'application/octet-stream' => IMAGETYPE_JPX,
      //'application/octet-stream' => IMAGETYPE_JB2,
      //'application/x-shockwave-flash' => IMAGETYPE_SWC,
      'image/iff' => IMAGETYPE_IFF,
      'image/vnd.wap.wbmp' => IMAGETYPE_WBMP,
      'image/xbm' => IMAGETYPE_XBM
    );
    if (isset($mimeTypes[$mimeType])) {
      return $mimeTypes[$mimeType];
    } else {
      return 0;
    }
  }
}

