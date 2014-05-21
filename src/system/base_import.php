<?php
/**
* content import handling
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @subpackage Controls
* @version $Id: base_import.php 39818 2014-05-13 13:15:13Z weinert $
*/

/**
* content import handling
*
* @package Papaya-Library
* @subpackage Controls
*/
class base_import extends base_db {
  /**
  * Papaya database table import filter
  * @var string $tableImportFilter
  */
  var $tableImportFilter = PAPAYA_DB_TBL_IMPORTFILTER;
  /**
  * Papaya database table import filter links
  * @var string $tableImportFilterLinks
  */
  var $tableImportFilterLinks = PAPAYA_DB_TBL_IMPORTFILTER_LINKS;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;

  /**
  * Table media links
  * @var string
  */
  var $tableMediaLinks = PAPAYA_DB_TBL_MEDIA_LINKS;

  /**
  * Single filter
  * @var array $filter
  */
  var $filter = NULL;

  /**
   * @var base_plugin
   */
  public $filterObj;

  /**
   * @var PapayaTemplate
   */
  public $layout;

  /**
  * Imports a file
  *
  * @param string $tmpFile
  * @param string $fileName
  * @param integer $pageId
  * @param integer $lngId
  * @param integer $viewId
  * @return boolean TRUE, on success, else FALSE
  */
  function importFile($tmpFile, $fileName, $pageId, $lngId, $viewId) {
    if ($this->filterObj = $this->getFilterByFileName($fileName)) {
      $this->filterObj->pageId = $pageId;
      $this->filterObj->languageId = $lngId;
      if ($this->loadFilterConfiguration($viewId, $this->filter['importfilter_id'])) {
        return $this->filterObj->import($tmpFile);
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('No import view configured for this file extension.'));
      }
    }
    return FALSE;
  }

  /**
  * Determines which filter to be used to import a file
  *
  * @param string $fileName
  * @return object Instance of the filter to be used to import a file
  */
  function getFilterByFileName($fileName) {
    $result = NULL;
    if (preg_match('~\.(\w+)$~', $fileName, $regs)) {
      $extension = $regs[1];
      if ($this->loadFilterByExtension($extension)) {
        $result = $this->papaya()->plugins->get(
          $this->filter['module_guid'],
          $this
        );
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Unknown file extension.'));
      }
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Invalid file extension.'));
    }
    return $result;
  }

  /**
  * Reads information about the filter to be used to import a file
  *
  * @param $extension
  * @return boolean TRUE, on success, else FALSE
  */
  function loadFilterByExtension($extension) {
    unset($this->filter);
    $sql = "SELECT im.importfilter_id, im.importfilter_ext, m.module_guid,
                   m.module_guid, m.module_path, m.module_file,
                   m.module_class, m.module_title
              FROM %s im
              LEFT OUTER JOIN %s m ON (m.module_guid = im.module_guid)
             WHERE im.importfilter_ext = '%s'";
    if (
      $res = $this->databaseQueryFmt(
        $sql, array($this->tableImportFilter, $this->tableModules, $extension)
      )
    ) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->filter = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Extracts the file to the tmp dir
  *
  * @param string $fileName
  * @param boolean $isUploadedFile
  * @return mixed Identifier of the extracted file
  */
  function extractFile($fileName, $isUploadedFile = TRUE) {
    $result = '';
    $this->initializeTemporaryDirectory();
    if (is_dir(PAPAYA_PATH_CACHE.'tmp')) {
      if ($fileId = $this->copyFile($fileName, $isUploadedFile)) {
        $pkgName = $fileId.'.pkg';
        //extract package to directory
        $archive = new sys_zip($pkgName);
        if ($archive->extract($fileId)) {
          $result = $fileId;
        }
        unlink($pkgName);
      }
    }
    return $result;
  }

  /**
  * Copies the given file to the tmp dir
  *
  * @param string $fileName
  * @param boolean $isUploadedFile
  * @return boolean TRUE, onsuccess, else FALSE
  */
  function copyFile($fileName, $isUploadedFile = TRUE) {
    $result = FALSE;
    $this->initializeTemporaryDirectory();
    if (is_dir(PAPAYA_PATH_CACHE.'tmp')) {
      $fileId = $this->getTemporaryFileName(PAPAYA_PATH_CACHE.'tmp/');
      $pkgName = $fileId.'pkg';
      if (is_uploaded_file($fileName)) {
        $copied = move_uploaded_file($fileName, $pkgName);
      } elseif (is_file($fileName) && !($isUploadedFile)) {
        $copied = copy($fileName, $pkgName);
      } else {
        $copied = FALSE;
      }
      if ($copied) {
        chmod($pkgName, 0666);
        $result = $fileId;
      }
    }
    return $result;
  }

  /**
  * Generates a filename for temporary use
  *
  * @param string $path
  * @return string the given path etended by a randomized string
  */
  function getTemporaryFileName($path) {
    do {
      $randName = md5(uniqid(rand()));
    } while (file_exists($path.$randName) || file_exists($path.$randName.'.pkg'));
    return $path.$randName;
  }

  /**
  * Crreates the tmp dir if not existant
  *
  * @return boolean TRUE, on success, else FALSE
  */
  function initializeTemporaryDirectory() {
    if (!is_dir(PAPAYA_PATH_CACHE.'tmp')) {
      umask(011);
      chdir(PAPAYA_PATH_CACHE);
      mkdir('tmp', 0777);
    }
    if (!is_dir(PAPAYA_PATH_CACHE.'tmp')) {
      $this->addMsg(MSG_ERROR, $this->_gt('Cannot find/create temporary path.'));
    }
  }

  /**
  * Deletes the given file from tmp directory
  *
  * @param string $fileName
  * @return boolean TRUE, on success, else FALSE
  */
  function deleteTemporaryFile($fileName) {
    if (isset($fileName) && is_dir($fileName) &&
        0 === strpos($fileName, PAPAYA_PATH_CACHE.'tmp/')) {
      if (unlink($fileName)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Removes previous extracted archive files from tmp dir
  *
  * @param string $directoryName
  * @return boolean TRUE, on success, else FALSE
  */
  function deleteExtractedFiles($directoryName) {
    if (isset($directoryName) && is_dir($directoryName) &&
        0 === strpos($directoryName, PAPAYA_PATH_CACHE.'tmp/')) {
      if ($dh = opendir($directoryName)) {
        while (FALSE !== ($file = readdir($dh))) {
          if ($file != '.' && $file != '..') {
            if (is_dir($directoryName.'/'.$file)) {
              $this->deleteExtractedFiles($directoryName.'/'.$file);
            } else {
              unlink($directoryName.'/'.$file);
            }
          }
        }
      }
      chdir(dirname($directoryName));
      rmdir(basename($directoryName));
    }
  }

  /**
  * Retrieves configuration information about the given filter
  *
  * @param integer $viewId
  * @param integer $filterId
  * @return bool TRUE, on success, else FALSE
  */
  function loadFilterConfiguration($viewId, $filterId) {
    unset($this->filterLink);
    $sql = "SELECT fl.importfilter_data
              FROM %s fl
             WHERE fl.view_id = %d AND fl.importfilter_id = %d";
    $params = array($this->tableImportFilterLinks, $viewId, $filterId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        $this->papaya()->plugins->configure(
          $this->filterObj, $row[0]
        );
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Parses given xml string for errors
  *
  * @param string $xml
  * @param string $xslFile
  * @return boolean FALSE
  */
  function transformXML($xml, $xslFile) {
    if (file_exists($xslFile)) {
      $transformer = new PapayaTemplateXslt($xslFile);
      $transformer->setXml($xml);
      return $transformer->parse();
    }
    return FALSE;
  }

  /**
   * add file data string to media database
   *
   * @param integer $pageId
   * @param integer $lngId
   * @param string $fileName
   * @param string $data
   * @access public
   * @return boolean
   */
  function addMediaFileData($pageId, $lngId, $fileName, $data) {
    if ($tempFileName = $this->getTemporaryFileName(PAPAYA_PATH_CACHE.'tmp')) {
      if ($fh = fopen($tempFileName, 'w')) {
        fwrite($fh, $data);
        fclose($fh);
        $result = $this->addMediaFile($pageId, $lngId, $fileName, $tempFileName);
        $this->deleteTemporaryFile($tempFileName);
        return $result;
      }
    }
    return FALSE;
  }

  /**
   * add a file to the media database
   *
   * @param integer $pageId
   * @param integer $lngId
   * @param string $fileName
   * @param string $tempFileName
   * @access public
   * @return boolean
   */
  function addMediaFile($pageId, $lngId, $fileName, $tempFileName = NULL) {
    $mediaDB = base_mediadb_edit::getInstance();

    if (!isset($tempFileName)) {
      $tempFileName = $fileName;
    }
    $tempFileNameConv = NULL;
    $fileName = basename($fileName);
    if (@file_exists($tempFileName)) {
      $surferId = $this->papaya()->administrationUser->user['userId'];
      if (
        $fileId = $mediaDB->addFile(
          $tempFileName, $fileName, -2, $surferId, '', 'local_file'
        )
      ) {
        $this->addMediaLinks($pageId, $lngId, $fileId);
        if (isset($tempFileNameConv)) {
          $this->deleteTemporaryFile($tempFileNameConv);
        }
        return $fileId;
      }
    }
    return FALSE;
  }

  /**
  * Adds a link to a media file to the database
  *
  * @param array|int $pageIds
  * @param array|int $lngIds
  * @param array|string $fileIds
  * @return boolean
  */
  function addMediaLinks($pageIds, $lngIds, $fileIds) {
    if (!is_array($pageIds)) {
      $pageIds = array($pageIds);
    }
    if (!is_array($lngIds)) {
      $lngIds = array($lngIds);
    }
    if (!is_array($fileIds)) {
      $fileIds = array($fileIds);
    }
    $data = array();
    foreach ($pageIds as $pageId) {
      foreach ($lngIds as $lngId) {
        foreach ($fileIds as $fileId) {
          $data[] = array(
            'file_id' => $fileId,
            'language_id' => $lngId,
            'page_id' => $pageId
          );
        }
      }
    }
    if (count($data) > 0) {
      return (FALSE !== $this->databaseInsertRecords($this->tableMediaLinks, $data));
    }
    return FALSE;
  }

  /**
  * Removes a link to a media file of a specific page and language
  *
  * @param integer $pageId
  * @param integer $lngId
  * @return boolean
  */
  function dropMediaLinksForPageId($pageId, $lngId = NULL) {
    $filter = array(
      'page_id' => (int)$pageId
    );
    if (isset($lngId)) {
      $filter['language_id'] = (int)$lngId;
    }
    return (FALSE !== $this->databaseDeleteRecord($this->tableMediaLinks, $filter));
  }

  /**
  * Removes a link to a media file of a specific fiel and language
  *
  * @param integer $fileId
  * @param integer $lngId
  * @return boolean
  */
  function dropMediaLinksForFileId($fileId, $lngId = NULL) {
    $filter = array(
      'file_id' => $fileId
    );
    if (isset($lngId)) {
      $filter['language_id'] = (int)$lngId;
    }
    return (FALSE !== $this->databaseDeleteRecord($this->tableMediaLinks, $filter));
  }
}
