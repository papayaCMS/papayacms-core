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

namespace Papaya\Media\Storage\Service;
/**
 * File based storage service for papaya
 *
 * @package Papaya-Library
 * @subpackage Media-Storage
 */
class File extends \Papaya\Media\Storage\Service {

  /**
   * base storage directory - will contain subdirectories for each storage group
   *
   * @var string $_storageDirectory
   */
  private $_storageDirectory = '';

  /**
   * subdirectory levels to avoid to many files in one directory
   *
   * @var integer $_storageDirectoryDepth
   */
  private $_storageDirectoryDepth = 1;

  /**
   * base public directory - public file resources can be linked here for better performance
   *
   * @var string $_publicDirectory
   */
  private $_publicDirectory = '';

  /**
   * base public directory url - absolute urls to public linked files
   *
   * @var string $_publicUrl
   */
  private $_publicUrl = '';

  /**
   * Maps mime-types that are acceptable for use with setPublic
   * to file extensions.
   *
   * For example application/x-httpd-php would be unacceptable because
   * we don't want to accidentally execute the file on the server
   * instead of delivering it.
   *
   * @var array $_mimeTypeToExtension
   */
  private $_mimeTypeToExtension = array(
    'image/png' => 'png',
    'image/jpeg' => 'jpg',
    'image/gif' => 'gif',
    'application/x-shockwave-flash' => 'swf'
  );

  /**
   * set the base storage directory and other configuration values
   *
   * @param \Papaya\Configuration $configuration
   */
  public function setConfiguration($configuration) {
    $this->_storageDirectory = $configuration->get(
      'PAPAYA_MEDIA_STORAGE_DIRECTORY', $this->_storageDirectory
    );
    $this->_storageDirectoryDepth = $configuration->get(
      'PAPAYA_MEDIA_STORAGE_DIRECTORY_DEPTH', $this->_storageDirectoryDepth
    );
    $this->_publicDirectory = $configuration->get(
      'PAPAYA_MEDIA_PUBLIC_DIRECTORY', $this->_publicDirectory
    );
    if (!empty($this->_publicDirectory) &&
      !is_dir($this->_publicDirectory)) {
      $this->_publicDirectory = '';
    }
    $this->_publicUrl = $configuration->get(
      'PAPAYA_MEDIA_PUBLIC_URL', $this->_publicUrl
    );
    if (substr($this->_publicDirectory, -1) == '/') {
      $this->_publicDirectory = substr($this->_publicDirectory, 0, -1);
    }
  }

  /**
   * check that the base storage directory exists
   *
   * @return boolean
   */
  protected function _verifyConfiguration() {
    if (!empty($this->_storageDirectory) &&
      is_dir($this->_storageDirectory) &&
      is_readable($this->_storageDirectory) &&
      is_writeable($this->_storageDirectory)) {
      $lastChar = substr($this->_storageDirectory, -1);
      if ($lastChar == '/' ||
        $lastChar == DIRECTORY_SEPARATOR) {
        $this->_storageDirectory = substr($this->_storageDirectory, 0, -1);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * get absolute file location for depending on the used directory structure
   *
   * @param string $path
   * @param string $storageId
   * @param boolean $createPath
   * @return string|NULL
   */
  private function _getFileLocation($path, $storageId, $createPath) {
    $result = NULL;
    if (strlen($storageId) > $this->_storageDirectoryDepth) {
      $oldMask = NULL;
      $result = $path;
      if (!$this->_ensureLocalDirectory($result, $createPath, $oldMask)) {
        return NULL;
      }
      for ($i = $this->_storageDirectoryDepth, $offset = 0; $i > 0; $i--, $offset++) {
        $result .= DIRECTORY_SEPARATOR.substr($storageId, $offset, 1);
        if (!$this->_ensureLocalDirectory($result, $createPath, $oldMask)) {
          return NULL;
        }
      }
      if (isset($oldMask)) {
        umask($oldMask);
      }
    }
    if ($result) {
      return $result.DIRECTORY_SEPARATOR.$storageId;
    }
    return NULL;
  }

  /**
   * Create local directory
   *
   * @param string $directory
   * @param $createDirectory
   * @param integer $oldMask
   * @return FALSE
   */
  private function _ensureLocalDirectory($directory, $createDirectory, &$oldMask) {
    if (file_exists($directory) && is_dir($directory)) {
      return TRUE;
    } elseif ($createDirectory) {
      if (is_null($oldMask)) {
        $oldMask = umask(0);
      }
      return @mkdir($directory, 0777);
    } else {
      return FALSE;
    }
  }

  /**
   * get the absolute file location for a storage id
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param boolean $createPath
   * @return string|NULL
   */
  private function _getStorageFilename($storageGroup, $storageId, $createPath) {
    return $this->_getFileLocation(
      $this->_storageDirectory.DIRECTORY_SEPARATOR.$storageGroup,
      $storageId,
      $createPath
    );
  }

  /**
   * Get the absolute file location of the public file (link) for a storage id.
   *
   * An apropriate file extension is added, so that the webserver
   * sets a proper mime-type when delivering the file.
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $mimeType
   * @param boolean $createPath
   * @return string|NULL
   */
  private function _getPublicFilename($storageGroup, $storageId, $mimeType, $createPath) {
    if (empty($this->_publicDirectory)) {
      return NULL;
    }
    $result = $this->_getFileLocation(
      $this->_publicDirectory.DIRECTORY_SEPARATOR.$storageGroup,
      $storageId,
      $createPath
    );
    if (!empty($result)) {
      $result .= $this->_getPublicExtension($storageId, $mimeType);
    }
    return $result;
  }

  /**
   * Return an apropriate file extension
   * if it isn't already present in the $storageId.
   *
   * @param string $storageId
   * @param string $mimeType
   * @return string
   */
  private function _getPublicExtension($storageId, $mimeType) {
    if (!isset($this->_mimeTypeToExtension[$mimeType])) {
      return '';
    }
    $extension = '.'.$this->_mimeTypeToExtension[$mimeType];
    $extensionLength = strlen($extension);
    if (substr($storageId, -1 * $extensionLength, $extensionLength) !== $extension) {
      return $extension;
    }
    return '';
  }

  /**
   * check if a storage file exists and is read-/writeable
   *
   * @param string $storageFilename
   * @return boolean
   */
  private function _existLocalFile($storageFilename) {
    if (!empty($storageFilename) &&
      file_exists($storageFilename) &&
      is_file($storageFilename) &&
      is_readable($storageFilename) &&
      is_writeable($storageFilename)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get files in the directory that start with a specified string.
   *
   * @param string $path
   * @param string $startsWith
   * @return array(string) files
   */
  private function _browseDirectory($path, $startsWith = '') {
    $result = array();
    $directories = glob($path.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
    if (is_array($directories)) {
      foreach ($directories as $directory) {
        $result = array_merge(
          $result,
          $this->_browseDirectory($directory, $startsWith)
        );
      }
    }
    $files = glob($path.DIRECTORY_SEPARATOR.$startsWith.'*');
    if (is_array($files)) {
      foreach ($files as $file) {
        if (is_file($file)) {
          $result[] = substr($file, strlen($path) + 1);
        }
      }
    }
    return $result;
  }

  /**
   * Get a list of resource ids in a storage group
   *
   * @param string $storageGroup
   * @param string $startsWith
   * @return array
   */
  public function browse($storageGroup, $startsWith = '') {
    $storagePath = $this->_storageDirectory.DIRECTORY_SEPARATOR.$storageGroup;
    if (file_exists($storagePath) &&
      is_dir($storagePath) &&
      is_readable($storagePath)) {
      return $this->_browseDirectory($storagePath, $startsWith);
    }
    return array();
  }

  /**
   * save a resource into the storage
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param resource|string $content
   * @param string $mimeType
   * @param boolean $isPublic
   * @return boolean
   */
  public function store(
    $storageGroup, $storageId, $content, $mimeType = 'application/octet-stream', $isPublic = FALSE
  ) {
    if ($this->_verifyConfiguration()) {
      $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, TRUE);
      if ($storageFilename && $fh = fopen($storageFilename, 'w')) {
        if (is_resource($content)) {
          while (!feof($content)) {
            fwrite($fh, fread($content, 512000));
          }
        } else {
          fwrite($fh, $content);
        }
        fclose($fh);
        $this->setPublic($storageGroup, $storageId, $isPublic, $mimeType);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * save a file into the storage
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $filename
   * @param string $mimeType
   * @param boolean $isPublic
   * @return boolean
   */
  public function storeLocalFile(
    $storageGroup, $storageId, $filename, $mimeType = 'application/octet-stream', $isPublic = FALSE
  ) {
    if ($this->_verifyConfiguration()) {
      $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, TRUE);
      if ($storageFilename && copy($filename, $storageFilename)) {
        $this->setPublic($storageGroup, $storageId, $isPublic, $mimeType);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * remove a resource from storage
   *
   * @param string $storageGroup
   * @param string $storageId
   * @return boolean
   */
  public function remove($storageGroup, $storageId) {
    $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
    if ($this->_existLocalFile($storageFilename)) {
      return unlink($storageFilename);
    }
    return FALSE;
  }

  /**
   * check if resource exists in storage
   *
   * @param string $storageGroup
   * @param string $storageId
   * @return boolean
   */
  public function exists($storageGroup, $storageId) {
    $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
    if ($this->_existLocalFile($storageFilename)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * return resource content
   *
   * @param string $storageGroup
   * @param string $storageId
   * @return string|NULL
   */
  public function get($storageGroup, $storageId) {
    $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
    if ($this->_existLocalFile($storageFilename)) {
      return file_get_contents($storageFilename);
    }
    return NULL;
  }

  /**
   * output resource content
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param integer $rangeFrom
   * @param integer $rangeTo
   * @param integer $bufferSize
   * @return boolean
   */
  public function output(
    $storageGroup, $storageId, $rangeFrom = 0, $rangeTo = 0, $bufferSize = 1024
  ) {
    $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
    if ($this->_existLocalFile($storageFilename)) {
      if ($rangeFrom > 0 && $rangeTo > 0) {
        $length = $rangeTo - $rangeFrom + 1;
      } elseif ($rangeFrom) {
        $length = filesize($storageFilename) - $rangeFrom;
      } else {
        $length = filesize($storageFilename);
      }
      return $this->_outputLocalFile(
        $storageFilename, $rangeFrom, $length, $bufferSize
      );
    }
    return FALSE;
  }

  /**
   * Ouutput range of local file
   *
   * @access protected
   * @param string $fileName
   * @param integer $rangeFrom
   * @param integer $length
   * @param integer $bufferSize
   * @return boolean
   */
  protected function _outputLocalFile($fileName, $rangeFrom, $length, $bufferSize) {
    if ($fh = fopen($fileName, 'r')) {
      if ($rangeFrom > 0) {
        fseek($fh, $rangeFrom);
      }
      for ($i = $length; $i > $bufferSize; $i -= $bufferSize) {
        echo fread($fh, $bufferSize);
      }
      if ($i > 0) {
        echo fread($fh, $i);
      }
      fclose($fh);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * The file handler needs a public file directory (to link public files)
   *
   * @return bool
   */
  public function allowPublic() {
    return !empty($this->_publicDirectory);
  }

  /**
   * check if stored file is public (is linked in public directory)
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $mimeType
   * @return boolean $isPublic
   */
  public function isPublic($storageGroup, $storageId, $mimeType) {
    if ($this->allowPublic()) {
      $publicFilename = $this->_getPublicFilename(
        $storageGroup, $storageId, $mimeType, FALSE
      );
      if ($this->_existLocalFile($publicFilename)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * set public status for storage id
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param boolean $isPublic
   * @param string $mimeType
   * @return boolean file is now in target status
   */
  public function setPublic($storageGroup, $storageId, $isPublic, $mimeType) {
    $publicFilename = $this->_getPublicFilename(
      $storageGroup, $storageId, $mimeType, $isPublic
    );
    if ($isPublic) {
      if (empty($publicFilename)) {
        return FALSE;
      }
      if (!isset($this->_mimeTypeToExtension[$mimeType])) {
        return FALSE;
      }
      if ($this->_existLocalFile($publicFilename)) {
        return TRUE;
      } else {
        $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
        if ($this->_existLocalFile($storageFilename) &&
          !file_exists($publicFilename)) {
          return @symlink($storageFilename, $publicFilename);
        }
      }
    } else {
      if ($this->_existLocalFile($publicFilename)) {
        return unlink($publicFilename);
      } else {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * get local file name of storage file and temporary status.
   *
   * Temporary status is always FALSE in this service
   *
   * @param string $storageGroup
   * @param string $storageId
   * @return array array('filename' => string, 'is_temporary' => FALSE)
   */
  public function getLocalFile($storageGroup, $storageId) {
    $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
    if ($this->_existLocalFile($storageFilename)) {
      return array(
        'filename' => $storageFilename,
        'is_temporary' => FALSE
      );
    }
    return NULL;
  }

  /**
   * get public url for a storage id if file is linked
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $mimeType
   * @return string|NULL
   */
  public function getUrl($storageGroup, $storageId, $mimeType) {
    if (strlen($storageId) > $this->_storageDirectoryDepth) {
      $publicFilename = $this->_getPublicFilename(
        $storageGroup, $storageId, $mimeType, FALSE
      );
      if ($this->_existLocalFile($publicFilename)) {
        $result = $this->_publicUrl.$storageGroup;
        for ($i = $this->_storageDirectoryDepth, $offset = 0; $i > 0; $i--, $offset++) {
          $result .= '/'.substr($storageId, $offset, 1);
        }
        return $result.'/'.$storageId.$this->_getPublicExtension($storageId, $mimeType);
      }
    }
    return NULL;
  }
}
