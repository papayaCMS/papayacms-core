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

/**
* Basic class for media db file handling - creating, modifying
*
* @package Papaya
* @subpackage Media-Database
*/
class base_mediadb_edit extends base_mediadb {

  /**
  * @var integer $maxUploadSize maximum upload size, set by getMaxUploadSize()
  */
  var $maxUploadSize = 0;

  /**
   * @var string $lastError
   */
  public $lastError = '';

  /**
  * add a local or uploaded file to the mediaDB
  *
  * use this method to add a local or uploaded file to the mediadb (mind the mode)
  *
  * @param string $fileLocation location to file on disk (also uploaded tempname)
  * @param string $fileName original name of file
  * @param integer $folderId mediaDB folderId
  * @param string $surferId id of uploader (also for backend)
  * @param string $fileType filetype mimetype sent by browser, just to check it
  * @param string $mode 'local_file' or 'uploaded_file'
  * @param array $meta metadata defaults
  * @return mixed $fileId on success, otherwise FALSE, check $instance->lastError
  */
  function addFile(
    $fileLocation,
    $fileName,
    $folderId,
    $surferId,
    $fileType = '',
    $mode = 'local_file',
    array $meta = array()
  ) {
    if (!is_file($fileLocation)) {
      $this->lastError = 'no_temp_file';
      return FALSE;
    }
    $fileId = $this->getUniqueFileId();
    if ($path = $this->getFilePath($fileId, TRUE)) {
      $fileNameOnDisk = $this->getFileName($fileId, 1);
      if ($mode == 'uploaded_file') {
        $moved = move_uploaded_file($fileLocation, $fileNameOnDisk);
      } elseif ($mode == 'local_file') {
        $moved = FALSE;
        // following lines will try to move the local file to papaya's data
        // if moving is not permitted, it will try to copy it
        if (is_file($fileLocation)) {
          if (is_readable($fileLocation)) {
            $moved = copy($fileLocation, $fileNameOnDisk);
            if (is_writable(dirname($fileLocation))) {
              unlink($fileLocation);
            } else {
              $this->lastError = 'file_not_moved';
            }
          }
        } else {
          $this->lastError = 'nonexisting_file';
        }
      } else {
        $this->lastError = 'mode_not_set';
        return FALSE;
      }
      if ($moved) {
        @chmod($fileNameOnDisk, 0666);
        if ($fileData = $this->getFileProperties($fileNameOnDisk, $fileName, $meta)) {
          if (isset($fileData['width']) && isset($fileData['height'])) {
            $width = $fileData['width'];
            $height = $fileData['height'];
          }

          $data = array(
            'file_id' => $fileId,
            'folder_id' => empty($folderId) ? 0 : (int)$folderId,
            'surfer_id' => isset($surferId) ? $surferId : '',
            'file_name' => $fileName,
            'file_date' => time(),
            'file_created' => date('Y-m-d H:i:s'),
            'file_source' => $fileData['file_source'],
            'file_source_url' => $fileData['file_source_url'],
            'file_keywords' => $fileData['file_keywords'],
            'file_size' => filesize($fileNameOnDisk),
            'file_sort' => 'zzz',
            'mimetype_id' => $fileData['mimetype_id'],
            'current_version_id' => 1,
            'width' => empty($width) ? 0 : (int)$width,
            'height' => empty($height) ? 0 : (int)$height,
            'metadata' => $fileData['metadata']
          );
          if (!empty($fileData['file_created'])) {
            $data['file_created'] = $fileData['file_created'];
          }
          if ($this->databaseInsertRecord($this->tableFiles, NULL, $data)) {
            $this->lastError = 'no_error';
            return $fileId;
          } else {
            $this->lastError = 'db_add_file_failed';
          }
        } elseif (filesize($fileNameOnDisk) == 0) {
          $this->lastError = 'empty_file';
        } else {
          $this->lastError = 'no_properties';
        }
      } else {
        $this->lastError = 'file_not_moved';
      }
    } else {
      $this->lastError = 'path_not_found';
    }
    return FALSE;
  }

  /**
   * update the properties of an existing file
   *
   * @param array $values
   * @param integer $lngId
   * @return boolean TRUE if the update succeeded, otherwise FALSE
   */
  function updateFile($values, $lngId) {
    $data = array(
      'file_name' => $values['file_name'],
      'file_keywords' => $values['file_keywords'],
      'file_source' => $values['file_source'],
      'file_source_url' => $values['file_source_url'],
      'file_sort' => $values['file_sort'],
      'file_created' => $values['file_created']
    );
    $condition = array('file_id' => $values['file_id']);
    $this->databaseUpdateRecord($this->tableFiles, $data, $condition);
    if ($this->fileTranslationExists($values['file_id'], $lngId)) {
      $dataTrans = array(
        'file_title' => $values['file_title'],
        'file_description' => $values['file_description'],
      );
      $conditionTrans = array(
        'lng_id' => $lngId,
        'file_id' => $values['file_id'],
      );
      return FALSE !== $this->databaseUpdateRecord(
        $this->tableFilesTrans, $dataTrans, $conditionTrans
      );
    } else {
      $dataTrans = array(
        'file_id' => $values['file_id'],
        'lng_id' => $lngId,
        'file_title' => $values['file_title'],
        'file_description' => $values['file_description'],
      );
      return (FALSE !== $this->databaseInsertRecord($this->tableFilesTrans, NULL, $dataTrans));
    }
  }

  /**
  * Update the translation data of a media db file.
  *
  * @param string $fileId media db file id to update
  * @param integer $lngId
  * @param string $fileTitle
  * @param string $fileDescription
  * @return boolean TRUE if the update succeeded, otherwise FALSE
  */
  function updateFileTrans($fileId, $lngId, $fileTitle = '', $fileDescription = '') {
    $result = TRUE;

    if (!empty($fileId) &&
        !empty($lngId)) {
      if ($this->fileTranslationExists($fileId, $lngId)) {
        $dataTrans = array();
        if (!empty($fileTitle)) {
          $dataTrans['file_title'] = $fileTitle;
        }
        if (!empty($fileDescription)) {
          $dataTrans['file_description'] = $fileDescription;
        }
        $conditionTrans = array(
          'file_id' => $fileId,
          'lng_id' => $lngId
        );
        $result &= FALSE !== $this->databaseUpdateRecord(
          $this->tableFilesTrans, $dataTrans, $conditionTrans
        );
      } else {
        $dataTrans = array(
          'file_id' => $fileId,
          'lng_id' => $lngId,
          'file_title' => $fileTitle,
          'file_description' => $fileDescription
        );
        $result &= FALSE !== $this->databaseInsertRecord(
          $this->tableFilesTrans, NULL, $dataTrans
        );
      }

    } else {
      $result = FALSE;
    }

    return $result;
  }

  /**
   * replace an existing file in the mediaDB by a local or uploaded file
   *
   * use this method to replace an existing file by a new one (uploaded or local)
   * creating a version of the file with the old data
   *
   * @param string $fileId id of file to be replaced
   * @param string $fileLocation location to file on disk (also uploaded tempname)
   * @param string $fileName original name of file
   * @param string $surferId id of uploader (also for backend)
   * @param string $fileType filetype mimetype sent by browser, just to check it
   * @param string $mode 'local_file' or 'uploaded_file'
   * @return boolean TRUE on success, otherwise FALSE
   */
  function replaceFile(
    $fileId, $fileLocation, $fileName, $surferId, $fileType = '', $mode = 'local_file'
  ) {
    if ($file = $this->getFile($fileId)) {
      $time = time();
      $data = array(
        'file_id' => $fileId,
        'file_name' => $file['file_name'],
        'surfer_id' => (string)$file['surfer_id'],
        'version_time' => $time,
        'version_id' => $file['current_version_id'],
        'file_size' => $file['file_size'],
        'file_date' => $file['file_date'],
        'file_created' => date('Y-m-d H:i:s'),
        'file_source' => $file['file_source'],
        'file_source_url' => $file['file_source_url'],
        'file_keywords' => $file['file_keywords'],
        'mimetype_id' => $file['mimetype_id'],
        'width' => $file['width'],
        'height' => $file['height'],
        'metadata' => $file['metadata'],
      );
      if (!empty($file['file_created'])) {
        $data['file_created'] = $file['file_created'];
      }
      $newVersionId = $file['current_version_id'] + 1;
      // create a version of the old file
      if (FALSE !== $this->databaseInsertRecord($this->tableFilesVersions, NULL, $data)) {
        $moved = FALSE;
        if ($path = $this->getFilePath($fileId, TRUE)) {
          $fileNameOnDisk = $this->getFileName($fileId, $newVersionId);
          if ($mode == 'uploaded_file') {
            $moved = move_uploaded_file($fileLocation, $fileNameOnDisk);
          } elseif ($mode == 'local_file') {
            // following lines will try to move the local file to papaya's data
            // if moving is not permitted, it will try to copy it
            if (is_readable($fileLocation)) {
              if (is_writable($fileLocation)) {
                $moved = rename($fileLocation, $fileNameOnDisk);
              } else {
                $moved = copy($fileLocation, $fileNameOnDisk);
                $this->lastError = 'file_not_moved';
              }
            } else {
              $this->lastError = 'file_not_moved';
            }
          } else {
            $this->lastError = 'mode_not_set';
            return FALSE;
          }
          if ($moved) {
            @chmod($fileNameOnDisk, 0666);
            if ($fileData = $this->getFileProperties($fileNameOnDisk, $fileName, $data)) {
              if (isset($fileData['width']) && isset($fileData['height'])) {
                $width = $fileData['width'];
                $height = $fileData['height'];
              } else {
                $width = 0;
                $height = 0;
              }

              $condition = array('file_id' => $fileId);
              $data = array(
                'folder_id' => $file['folder_id'],
                'surfer_id' => (string)$surferId,
                'file_name' => $fileName,
                'file_date' => $time,
                'file_created' => date('Y-m-d H:i:s'),
                'file_source' => $fileData['file_source'],
                'file_source_url' => $fileData['file_source_url'],
                'file_keywords' => $fileData['file_keywords'],
                'file_size' => filesize($fileNameOnDisk),
                'file_sort' => 'zzz',
                'mimetype_id' => $fileData['mimetype_id'],
                'current_version_id' => $newVersionId,
                'width' => $width,
                'height' => $height,
                'metadata' => $fileData['metadata'],
              );
              if (!empty($fileData['file_created'])) {
                $data['file_created'] = $fileData['file_created'];
              }
              if (FALSE !== $this->databaseUpdateRecord($this->tableFiles, $data, $condition)) {
                return TRUE;
              } else {
                $this->lastError = 'db_update_file_failed';
              }
            } else {
              $this->lastError = 'no_properties';
            }
          } else {
            $this->lastError = 'file_not_moved';
          }
        } else {
          $this->lastError = 'path_not_found';
        }
        // if we are still here, we have a version, but no valid file, so we remove the version
        $this->deleteVersion($fileId, $file['current_version_id']);
      } else {
        $this->lastError = 'create_version_failed';
      }
    } else {
      $this->lastError = 'load_file_failed';
    }
    return FALSE;
  }


  /**
  * restore a former version of a file
  */
  function restoreVersion($fileId, $versionId) {
    if ($fileVersion = $this->getFile($fileId, $versionId)) {
      if ($file = $this->getFile($fileId)) {
        $time = time();
        $data = array(
          'file_id' => $fileId,
          'file_name' => $file['file_name'],
          'version_time' => $time,
          'version_id' => $file['current_version_id'],
          'file_size' => $file['file_size'],
          'file_date' => $file['file_date'],
          'file_created' => $file['file_created'],
          'file_keywords' => $file['file_keywords'],
          'file_source' => $file['file_source'],
          'file_source_url' => $file['file_source_url'],
          'mimetype_id' => $file['mimetype_id'],
          'width' => $file['width'],
          'height' => $file['height'],
          'metadata' => $file['metadata'],
        );
        $newVersionId = $file['current_version_id'] + 1;
        if (FALSE !== $this->databaseInsertRecord($this->tableFilesVersions, NULL, $data)) {
          if ($path = $this->getFilePath($fileId, TRUE)) {
            $fileNameOnDisk = $this->getFileName($fileId, $newVersionId);
            if (copy($fileVersion['FILENAME'], $fileNameOnDisk)) {
              $condition = array('file_id' => $fileId);
              $data = array(
                'file_name' => $fileVersion['file_name'],
                'file_date' => $fileVersion['file_date'],
                'file_size' => $fileVersion['file_size'],
                'file_created' => $fileVersion['file_created'],
                'file_keywords' => $fileVersion['file_keywords'],
                'file_source' => $fileVersion['file_source'],
                'file_source_url' => $fileVersion['file_source_url'],
                'mimetype_id' => $fileVersion['mimetype_id'],
                'current_version_id' => $newVersionId,
                'width' => $fileVersion['width'],
                'height' => $fileVersion['height'],
                'metadata' => $fileVersion['metadata'],
              );
              if (FALSE !== $this->databaseUpdateRecord($this->tableFiles, $data, $condition)) {
                return TRUE;
              }
            } else {
              $this->lastError = 'copy_file_failed';
            }
          } else {
            $this->lastError = 'path_not_found';
          }
        } else {
          $this->lastError = 'create_version_failed';
        }
      } else {
        $this->lastError = 'file_not_found';
      }
    } else {
      $this->lastError = 'version_not_found';
    }
    return FALSE;
  }


  /**
   * get a file from the web and add it to the media db (or replace an existing file)
   *
   * use this method to get a file from the internet and store it in the mediadb
   *
   * @param string $fileURL url of the file to get
   * @param integer $folderId id of folder to add file to
   * @param string $surferId id of the surfer that added the file
   * @param boolean $replace whether to replace an existing file
   * @param integer $fileId id of file to replace (if any)
   * @param null $fileName
   * @return string $errorType error type
   */
  function getFileFromWeb(
    $fileURL, $folderId, $surferId, $replace = FALSE, $fileId = NULL, $fileName = NULL
  ) {
    if (\Papaya\Filter\Factory::isURL($fileURL)) {
      $fileName = !empty($fileName) ? $fileName : basename($fileURL);
      $tmpFileName = tempnam(PAPAYA_PATH_CACHE, '.mdb_web');
      if ($fpTemp = fopen($tmpFileName, 'w')) {
        if ($fpRemote = fopen($fileURL, 'r')) {
          while ($buffer = fgets($fpRemote, 4096)) {
            fwrite($fpTemp, $buffer);
          }
          fclose($fpTemp);
          fclose($fpRemote);
          if (isset($tmpFileName) && is_file($tmpFileName)) {
            $fileSize = filesize($tmpFileName);
            if ($fileSize > 0) {
              if ($replace && isset($fileId) && $fileId != '') {
                $replaced = $this->replaceFile(
                  $fileId,
                  $tmpFileName,
                  $fileName,
                  empty($folderId) ? 0 : (int)$folderId,
                  $surferId
                );
                if ($replaced) {
                  return TRUE;
                }
              } else {
                $fileId = $this->addFile(
                  $tmpFileName,
                  $fileName,
                  empty($folderId) ? 0 : (int)$folderId,
                  $surferId
                );
                if ($fileId) {
                  return $fileId;
                }
              }
            } else {
              $this->lastError = 'empty_file';
            }
          } else {
            $this->lastError = 'no_temp_file';
          }
        } else {
          $this->lastError = 'open_remote_file_failed';
        }
      } else {
        $this->lastError = 'open_temp_file_failed';
      }
      @unlink($tmpFileName);
    } else {
      $this->addMsg(MSG_ERROR, sprintf($this->_gt('Invalid URL "%s".'), $fileURL));
    }
    return FALSE;
  }

  /**
  * move a file to a different folder
  *
  * use this method to move a file to a different mediadb folder
  *
  * @param string $fileId id of the file to move
  * @param integer $folderId id of the folder the file shall be moved to
  * @return boolean TRUE if the update succeeded, otherwise FALSE
  */
  function moveFile($fileId, $folderId) {
    if ($fileId != '' && $folderId != '') {
      $data = array('folder_id' => (int)$folderId);
      $condition = array('file_id' => $fileId);
      return (FALSE !== $this->databaseUpdateRecord($this->tableFiles, $data, $condition));
    }
    return FALSE;
  }

  /**
  * moves all files from a folder to a different folder
  *
  * Used for pasting the whole clipboard to a folder. May be used for moving
  * files e.g. on folder deletion.
  *
  * @param integer $folderId id of the folder containing the to be moved files
  * @param integer $targetFolderId id of the folder the files shall be moved to
  * @return boolean TRUE if the update succeeded, otherwise FALSE
  */
  function moveFiles($folderId, $targetFolderId) {
    if ($folderId !== '' && $targetFolderId !== '') {
      $data = array('folder_id' => (int)$targetFolderId);
      $condition = array('folder_id' => (int)$folderId);
      return (FALSE !== $this->databaseUpdateRecord($this->tableFiles, $data, $condition));
    }
    return FALSE;
  }

  /**
  * make a copy of a file
  *
  * use this method to copy a file to a different folder, default is the clipboard
  *
  * @param string $fileId id of the file to copy
  * @param integer $folderId id of the folder to copy the file to
  * @return boolean TRUE on success, otherwise FALSE
  */
  function copyFile($fileId, $folderId = -1) {
    $file = $this->getFile($fileId);
    $newFileId = $this->getUniqueFileId();
    if (is_array($file) && count($file) > 0) {
      if ($path = $this->getFilePath($newFileId, TRUE)) {
        $fileNameOnDisk = $this->getFileName($newFileId, 1);
        if (copy($file['FILENAME'], $fileNameOnDisk)) {
          $data = array(
              'file_id' => $newFileId,
              'folder_id' => $folderId,
              'file_name' => $file['file_name'],
              'file_date' => time(),
              'file_created' => $file['file_created'],
              'file_keywords' => $file['file_keywords'],
              'file_source' => $file['file_source'],
              'file_source_url' => $file['file_source_url'],
              'file_size' => $file['file_size'],
              'file_sort' => 'zzz',
              'mimetype_id' => $file['mimetype_id'],
              'current_version_id' => 1,
              'width' => empty($file['width']) ? 0 : (int)$file['width'],
              'height' => empty($file['height']) ? 0 : (int)$file['height'],
              'metadata' => $file['metadata'],
          );
          if (FALSE !== $this->databaseInsertRecord($this->tableFiles, NULL, $data)) {
            $this->copyTranslatedData($fileId, $newFileId);
            $this->addDerivation($newFileId, $fileId, $file['current_version_id']);
            return TRUE;
          }
        } else {
          $this->lastError = 'copy_file_failed';
        }
      } else {
        $this->lastError = 'create_path_failed';
      }
    } else {
      $this->lastError = 'file_not_found';
    }
    return FALSE;
  }

  /**
  * add a derivation to a file
  *
  * use this method to indicate a derivation relationship between two files
  *
  * @param string $newFileId id of the derived file
  * @param string $srcFileId id of the parent file
  * @param integer $versionId version of the parent file the file is derived from
  * @return boolean TRUE on success, otherwise FALSE
  */
  function addDerivation($newFileId, $srcFileId, $versionId) {
    if (!($headFileId = $this->getDerivationHeadId($srcFileId))) {
      $headFileId = $srcFileId;
    }
    $derivationData = array(
      'head_file_id' => $headFileId,
      'parent_file_id' => $srcFileId,
      'parent_file_version_id' => $versionId,
      'child_file_id' => $newFileId,
    );
    $inserted = (
      FALSE === $this->databaseInsertRecord($this->tableFilesDerivations, NULL, $derivationData)
    );
    if ($inserted) {
      $this->lastError = 'derivations_lost';
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Copy the language specific data from one file to another.
   *
   * @param string $sourceFileId
   * @param string $targetFileId
   * @return boolean
   */
  function copyTranslatedData($sourceFileId, $targetFileId) {
    $sql = "INSERT INTO %s (file_id, lng_id, file_title, file_description)
            SELECT '%s', src.lng_id, src.file_title, src.file_description
              FROM %s src
             WHERE src.file_id = '%s'";
    $parameters = array(
      $this->tableFilesTrans,
      $targetFileId,
      $this->tableFilesTrans,
      $sourceFileId
    );
    $inserted = (
      FALSE === $this->databaseQueryFmtWrite($sql, $parameters)
    );
    if ($inserted) {
      $this->lastError = 'translations_copy_failed';
      return FALSE;
    }
    return TRUE;
  }

  /**
   * delete a file
   *
   * use this method to delete a file and all its versions from the mediadb
   *
   * @param string $fileId id of file to delete
   * @return boolean TRUE on success, otherwise FALSE
   */
  function deleteFile($fileId) {
    $file = $this->getFile($fileId);
    if (is_array($file) && !empty($fileId)) {
      $this->getThumbnailGenerator($fileId, $file['current_version_id'])->delete(TRUE);

      if ($this->deleteFileVersions($fileId)) {
        $fileName = $this->getFileName($fileId, $file['current_version_id'], TRUE);
        $doDelete = TRUE;
        if (file_exists($fileName) && (!unlink($fileName))) {
          $doDelete = FALSE;
        }
        if ($doDelete) {
          $condition = array('child_file_id' => $fileId);
          $doDelete = (
            FALSE !== $this->databaseDeleteRecord($this->tableFilesDerivations, $condition)
          );
        }
        if ($doDelete) {
          $condition = array('file_id' => $fileId);
          $doDelete = (
            FALSE !== $this->databaseDeleteRecord($this->tableFilesTrans, $condition) &&
            FALSE !== $this->databaseDeleteRecord($this->tableFiles, $condition)
          );
        }
        return $doDelete;
      }
    }
    return FALSE;
  }

  /**
  * delete a version of a file
  *
  * use this method to delete a specific version of a file
  *
  * @param string $fileId file id
  * @param integer $versionId file version
  * @return boolean TRUE if deletion succeeded, otherwise FALSE
  */
  function deleteVersion($fileId, $versionId) {
    if ($fileId != '' && $versionId > 0) {
      $condition = array(
        'file_id' => $fileId,
        'version_id' => $versionId,
      );
      $this->getThumbnailGenerator($fileId, $versionId)->delete();
      return (FALSE !== $this->databaseDeleteRecord($this->tableFilesVersions, $condition));
    }
    return TRUE;
  }

  /**
  * delete all versions of a file (not the file itself) from the database
  *
  * use this method to remove all former versions for a file
  *
  * @param string $fileId id of file whose versions are to be deleted
  * @return bool TRUE on success or if no versions exist, otherwise FALSE
  */
  function deleteFileVersions($fileId) {
    if ($fileId != '') {
      $versions = $this->getFileVersions($fileId);
      if (is_array($versions) && count($versions) > 0) {
        $versionIds = array();
        foreach ($versions as $versionId => $version) {
          $fileName = $this->getFileName($fileId, $versionId, TRUE);
          if (!is_file($fileName) || unlink($fileName)) {
            $versionIds[] = $versionId;
          }
        }
        $condition = array(
          'file_id' => $fileId,
          'version_id' => $versionIds,
        );
        return (FALSE !== $this->databaseDeleteRecord($this->tableFilesVersions, $condition));
      } else {
        // file has got no versions
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * move multiple files to a different folder
  *
  * use this method to move multiple files to a different folder
  *
  * @param array $fileIds list of file ids to be moved
  * @param integer $targetFolderId id of folders to move files to
  * @return boolean TRUE if files could be moved, otherwise FALSE
  */
  function moveMultipleFiles($fileIds, $targetFolderId) {
    $data = array('folder_id' => $targetFolderId);
    $condition = array('file_id' => $fileIds);
    return (FALSE !== $this->databaseUpdateRecord($this->tableFiles, $data, $condition));
  }

  /**
  * Add folder record to database
  * @param integer $parentId
  * @param string $parentPath
  * @param string $permissionMode
  * @return integer $folderId new Id
  */
  public function addFolder($parentId, $parentPath, $permissionMode) {
    $data = array(
      'parent_id' => $parentId,
      'parent_path' => $parentPath,
      'permission_mode' => $parentId > 0 ? $permissionMode : 'own',
    );
    $folderId = $this->databaseInsertRecord($this->tableFolders, 'folder_id', $data);
    return $folderId;
  }
  /**
  * Add folder translation data record to database
  * @param integer $folderId
  * @param integer $languageId
  * @param string $folderName
  * @return integer $folderId
  */
  public function addFolderTranslation($folderId, $languageId, $folderName) {
    $dataTrans = array(
      'folder_id' => $folderId,
      'lng_id' => $languageId,
      'folder_name' => $folderName,
    );
    if ($this->databaseInsertRecord($this->tableFoldersTrans, NULL, $dataTrans)) {
      return $folderId;
    }
    return FALSE;
  }

  /**
  * Same folder translation data to database
  * @param integer $folderId
  * @param integer $languageId
  * @param string $folderName
  * @return boolean
  */
  public function setFolderTranslation($folderId, $languageId, $folderName) {
    $condition = array(
      'folder_id' => $folderId,
      'lng_id' => $languageId,
    );
    $dataTrans = array(
      'folder_name' => $folderName,
    );
    if ($this->databaseUpdateRecord($this->tableFoldersTrans, $dataTrans, $condition)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * delete a folder
  *
  * @param integer $folderId id of folder to be deleted
   * @return boolean
   */
  function deleteFolder($folderId) {
    if (isset($folderId) && $folderId > 0) {
      $condition = array('folder_id' => $folderId);
      $this->databaseDeleteRecord($this->tableFoldersTrans, $condition);
      return FALSE !== $this->databaseDeleteRecord($this->tableFolders, $condition);
    }
    return TRUE;
  }

  /**
  * move a folder to a different position in the folder tree
  *
  * @param integer $folderId id of the folder to be moved
  * @param integer $targetFolderId id of the new parent folder for the moved folder
  * @return mixed parent folder id on success (may be int 0!), otherwise FALSE
  */
  function moveFolder($folderId, $targetFolderId) {
    if ($folder = $this->getFolder($folderId)) {
      $folder = current($folder);
      switch ($targetFolderId) {
      case 0:
        $ancestors = array('0');
        break;
      case -1:
        $ancestors = array('-1');
        break;
      default:
        $targetFolder = $this->getFolder($targetFolderId);
        $targetFolder = current($targetFolder);
        $ancestors = \Papaya\Utility\Arrays::decodeIdList($targetFolder['parent_path']);
        $ancestors[] = $targetFolderId;
      }
      $data = array(
        'parent_id' => $targetFolderId,
        'parent_path' => \Papaya\Utility\Arrays::encodeAndQuoteIdList($ancestors)
      );
      $condition = array('folder_id' => $folderId);
      if (FALSE !== $this->databaseUpdateRecord($this->tableFolders, $data, $condition)) {
        $oldAncestors = \Papaya\Utility\Arrays::decodeIdList($folder['parent_path']);
        $oldAncestors[] = $folderId;
        $oldPath = \Papaya\Utility\Arrays::encodeAndQuoteIdList($oldAncestors);
        $newAncestors = $ancestors;
        $newAncestors[] = $folderId;
        $newPath = \Papaya\Utility\Arrays::encodeAndQuoteIdList($newAncestors);
        $sqlReplace = $this->databaseGetSQLSource(
          'CONCAT',
          $newPath,
          TRUE,
          $this->databaseGetSQLSource(
            'SUBSTRING', 'parent_path', FALSE, strlen($oldPath) + 1, TRUE
          ),
          FALSE
        );
        $sql = "UPDATE %s
                   SET parent_path = ".$sqlReplace."
                 WHERE parent_path LIKE '%s%%'";
        $parameters = array(
          $this->tableFolders,
          $oldPath
        );
        $this->databaseQueryFmtWrite($sql, $parameters);
        return empty($folder['parent_id']) ? 0 : (int)$folder['parent_id'];
      }
    }
    return FALSE;
  }

  /**
  * fetch existing surfer permissions
  *
  * this method is used to get a list of existing surfer permissions
  *
  * @return array $result list of permissions: perm_id => (perm_id, title, active)
  */
  function getSurferPermissions() {
    $result = array();
    $sql = "SELECT surferperm_id, surferperm_title, surferperm_active
              FROM %s";
    if ($res = $this->databaseQueryFmt($sql, array(PAPAYA_DB_TBL_SURFERPERM))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['surferperm_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * fetch existing edit user groups
  *
  * this method is used to get a list of usergroups
  *
  * @return array $result list of edit user groups group_id => grouptitle
  */
  function getGroups() {
    $result = array(-1 => 'Administrator');
    $sql = "SELECT group_id, grouptitle
              FROM %s";
    if ($res = $this->databaseQueryFmt($sql, array(PAPAYA_DB_TBL_AUTHGROUPS))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['group_id']] = $row['grouptitle'];
      }
    }
    return $result;
  }

  /**
  * add a permission to a folder
  *
  * @param integer $folderId a folder id
  * @param string $permissionType the type of the permission
  * @param integer $permissionId the id of the permission (group_id, surfer_permission_id)
  * @return bool TRUE on success, otherwise FALSE
  */
  function addPermission($folderId, $permissionType, $permissionId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE folder_id = %d
               AND permission_type = '%s'
               AND permission_value = '%d'
           ";
    $params = array($this->tableFoldersPermissions, $folderId,
      $permissionType, $permissionId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if (!$res->fetchField()) {
        $data = array(
          'folder_id' => $folderId,
          'permission_type' => $permissionType,
          'permission_value' => $permissionId,
        );
        return $this->databaseInsertRecord($this->tableFoldersPermissions, NULL, $data);
      }
    }
    return FALSE;
  }

  /**
  * remove a permission from a folder
  *
  * @param integer $folderId a folder id
  * @param string $permissionType the type of the permission
  * @param integer $permissionId the id of the permission (group_id, surfer_permission_id)
  * @return bool TRUE on success, otherwise FALSE
  */
  function delPermission($folderId, $permissionType, $permissionId) {
    $condition = array(
      'folder_id' => $folderId,
      'permission_type' => $permissionType,
      'permission_value' => $permissionId,
    );
    return $this->databaseDeleteRecord($this->tableFoldersPermissions, $condition);
  }

  // ---------------------------- AIDING FUNCTIONS -----------------------------

  /**
  * generate a unique file id using md5(uniqid(rand(), TRUE))
  */
  function getUniqueFileId() {
    $fileId = md5(uniqid(rand(), TRUE));
    if (!$this->getFile($fileId)) {
      return $fileId;
    } else {
      return $this->getUniqueFileId();
    }
  }

  /**
  * calculate how large an uploaded file may be at most
  *
  * use this method to get the value for MAX_UPLOAD_SIZE hidden form value
  *
  * @return integer maximum upload size in bytes
  */
  function getMaxUploadSize() {
    if (!isset($this->maxUploadSize) || $this->maxUploadSize <= 0) {
      if (PAPAYA_MAX_UPLOAD_SIZE > 0) {
        $confMax = PAPAYA_MAX_UPLOAD_SIZE;
      } else {
        $confMax = 10;
      }
      $iniMaxPost = $this->iniGetSize('post_max_size');
      $iniMaxFile = $this->iniGetSize('upload_max_filesize');
      if ($iniMaxPost < $iniMaxFile) {
        $iniMax = $iniMaxPost;
      } else {
        $iniMax = $iniMaxFile;
      }
      if ($iniMax > 0 && $iniMax < $confMax) {
        $this->maxUploadSize = ($iniMax * 1048576) - 2048;
      } else {
        $this->maxUploadSize = ($confMax * 1048576) - 2048;
      }
    }
    return $this->maxUploadSize;
  }

  /**
  * Get byte size configuration from php.ini and convert to bytes
  * @param string $ident
  * @return integer
  */
  function iniGetSize($ident) {
    return \Papaya\Utility\Bytes::fromString(ini_get($ident));
  }

  private function getThumbnailGenerator($fileId, $fileRevision) {
    return new Papaya\Media\Thumbnails($fileId, $fileRevision);
  }
}

