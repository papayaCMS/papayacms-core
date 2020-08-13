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

use Papaya\Media;
use Papaya\Utility\Arrays;

/**
 * File based storage service for papaya
 *
 * @package Papaya-Library
 * @subpackage Media-Storage
 */
class File extends Media\Storage\Service {
  /**
   * base storage directory - will contain subdirectories for each storage group
   *
   * @var string $_storageDirectory
   */
  private $_storageDirectory = '';

  /**
   * subdirectory levels to avoid to many files in one directory
   *
   * @var int $_storageDirectoryDepth
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
   * @var string $_publicURL
   */
  private $_publicURL = '';

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
  private $_mimeTypeToExtension = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg',
    'image/gif' => 'gif',
    'application/x-shockwave-flash' => 'swf'
  ];

  /**
   * set the base storage directory and other configuration values
   *
   * @param \Papaya\Configuration $configuration
   */
  public function setConfiguration(\Papaya\Configuration $configuration) {
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
      !\is_dir($this->_publicDirectory)) {
      $this->_publicDirectory = '';
    }
    $this->_publicURL = $configuration->get(
      'PAPAYA_MEDIA_PUBLIC_URL', $this->_publicURL
    );
    if ('/' === \substr($this->_publicDirectory, -1)) {
      $this->_publicDirectory = \substr($this->_publicDirectory, 0, -1);
    }
  }

  /**
   * check that the base storage directory exists
   *
   * @return bool
   */
  protected function _verifyConfiguration() {
    if (
      !empty($this->_storageDirectory) &&
      \is_dir($this->_storageDirectory) &&
      \is_readable($this->_storageDirectory) &&
      \is_writable($this->_storageDirectory)
    ) {
      $lastChar = \substr($this->_storageDirectory, -1);
      if (
        '/' === $lastChar ||
        DIRECTORY_SEPARATOR === $lastChar
      ) {
        $this->_storageDirectory = \substr($this->_storageDirectory, 0, -1);
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
   * @param bool $createPath
   *
   * @return string|null
   */
  private function _getFileLocation($path, $storageId, $createPath) {
    $result = NULL;
    if (\strlen($storageId) > $this->_storageDirectoryDepth) {
      $oldMask = NULL;
      $result = $path;
      if (!$this->_ensureLocalDirectory($result, $createPath, $oldMask)) {
        return NULL;
      }
      for ($i = $this->_storageDirectoryDepth, $offset = 0; $i > 0; $i--, $offset++) {
        $result .= DIRECTORY_SEPARATOR.\substr($storageId, $offset, 1);
        if (!$this->_ensureLocalDirectory($result, $createPath, $oldMask)) {
          return NULL;
        }
      }
      if (NULL !== $oldMask) {
        \umask($oldMask);
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
   * @param int $oldMask
   *
   * @return false
   */
  private function _ensureLocalDirectory($directory, $createDirectory, &$oldMask) {
    if (\file_exists($directory) && \is_dir($directory)) {
      return TRUE;
    }
    if ($createDirectory) {
      if (NULL === $oldMask) {
        $oldMask = \umask(0);
      }
      return @\mkdir($directory, 0777);
    }
    return FALSE;
  }

  /**
   * get the absolute file location for a storage id
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param bool $createPath
   *
   * @return string|null
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
   * An appropriate file extension is added, so that the webserver
   * sets a proper mime-type when delivering the file.
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $mimeType
   * @param bool $createPath
   *
   * @return string|null
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
    if (NULL !== $result) {
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
   *
   * @return string
   */
  private function _getPublicExtension($storageId, $mimeType) {
    if (!isset($this->_mimeTypeToExtension[$mimeType])) {
      return '';
    }
    $extension = '.'.$this->_mimeTypeToExtension[$mimeType];
    $extensionLength = \strlen($extension);
    if (\substr($storageId, -1 * $extensionLength, $extensionLength) !== $extension) {
      return $extension;
    }
    return '';
  }

  /**
   * check if a storage file exists and is read-/writeable
   *
   * @param string $storageFilename
   *
   * @return bool
   */
  private function _existLocalFile($storageFilename) {
    return (
      !empty($storageFilename) &&
      \file_exists($storageFilename) &&
      \is_file($storageFilename) &&
      \is_readable($storageFilename) &&
      \is_writable($storageFilename)
    );
  }

  /**
   * Get files in the directory that start with a specified string.
   *
   * @param string $path
   * @param string $startsWith
   *
   * @return array(string) files
   */
  private function _browseDirectory($path, $startsWith = '') {
    $result = [];
    $directories = \glob($path.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
    if (\is_array($directories)) {
      foreach ($directories as $directory) {
        \Papaya\Utility\Arrays::push(
          $result,
          ...$this->_browseDirectory($directory, $startsWith)
        );
      }
    }
    $files = \glob($path.DIRECTORY_SEPARATOR.$startsWith.'*');
    if (\is_array($files)) {
      foreach ($files as $file) {
        if (\is_file($file)) {
          $result[] = \substr($file, \strlen($path) + 1);
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
   *
   * @return array
   */
  public function browse($storageGroup, $startsWith = '') {
    $storagePath = $this->_storageDirectory.DIRECTORY_SEPARATOR.$storageGroup;
    if (
      \file_exists($storagePath) &&
      \is_dir($storagePath) &&
      \is_readable($storagePath)
    ) {
      return $this->_browseDirectory($storagePath, $startsWith);
    }
    return [];
  }

  /**
   * save a resource into the storage
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param resource|string $content
   * @param string $mimeType
   * @param bool $isPublic
   *
   * @return bool
   */
  public function store(
    $storageGroup, $storageId, $content, $mimeType = 'application/octet-stream', $isPublic = FALSE
  ) {
    if ($this->_verifyConfiguration()) {
      $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, TRUE);
      if ($storageFilename && $fh = \fopen($storageFilename, 'wb')) {
        if (\is_resource($content)) {
          while (!\feof($content)) {
            \fwrite($fh, \fread($content, 512000));
          }
        } else {
          \fwrite($fh, $content);
        }
        \fclose($fh);
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
   * @param bool $isPublic
   *
   * @return bool
   */
  public function storeLocalFile(
    $storageGroup, $storageId, $filename, $mimeType = 'application/octet-stream', $isPublic = FALSE
  ) {
    if ($this->_verifyConfiguration()) {
      $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, TRUE);
      if ($storageFilename && \copy($filename, $storageFilename)) {
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
   *
   * @return bool
   */
  public function remove($storageGroup, $storageId) {
    $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
    if ($this->_existLocalFile($storageFilename)) {
      return \unlink($storageFilename);
    }
    return FALSE;
  }

  /**
   * check if resource exists in storage
   *
   * @param string $storageGroup
   * @param string $storageId
   *
   * @return bool
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
   *
   * @return string|null
   */
  public function get($storageGroup, $storageId) {
    $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
    if ($this->_existLocalFile($storageFilename)) {
      return \file_get_contents($storageFilename);
    }
    return NULL;
  }

  /**
   * output resource content
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param int $rangeFrom
   * @param int $rangeTo
   * @param int $bufferSize
   *
   * @return bool
   */
  public function output(
    $storageGroup, $storageId, $rangeFrom = 0, $rangeTo = 0, $bufferSize = 1024
  ) {
    $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
    if ($this->_existLocalFile($storageFilename)) {
      if ($rangeFrom > 0 && $rangeTo > 0) {
        $length = $rangeTo - $rangeFrom + 1;
      } elseif ($rangeFrom) {
        $length = \filesize($storageFilename) - $rangeFrom;
      } else {
        $length = \filesize($storageFilename);
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
   * @param string $fileName
   * @param int $rangeFrom
   * @param int $length
   * @param int $bufferSize
   *
   * @return bool
   */
  protected function _outputLocalFile($fileName, $rangeFrom, $length, $bufferSize) {
    if ($fh = \fopen($fileName, 'rb')) {
      if ($rangeFrom > 0) {
        \fseek($fh, $rangeFrom);
      }
      for ($i = $length; $i > $bufferSize; $i -= $bufferSize) {
        echo \fread($fh, $bufferSize);
      }
      if ($i > 0) {
        echo \fread($fh, $i);
      }
      \fclose($fh);
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
   *
   * @return bool $isPublic
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
   * @param bool $isPublic
   * @param string $mimeType
   *
   * @return bool file is now in target status
   */
  public function setPublic($storageGroup, $storageId, $isPublic, $mimeType) {
    $publicFilename = $this->_getPublicFilename(
      $storageGroup, $storageId, $mimeType, $isPublic
    );
    if ($isPublic) {
      if (NULL === $publicFilename || '' === \trim($publicFilename)) {
        return FALSE;
      }
      if (!isset($this->_mimeTypeToExtension[$mimeType])) {
        return FALSE;
      }
      if ($this->_existLocalFile($publicFilename)) {
        return TRUE;
      }
      $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
      if (
        $this->_existLocalFile($storageFilename) &&
        !\file_exists($publicFilename)
      ) {
        return @\symlink($storageFilename, $publicFilename);
      }
    } else {
      if ($this->_existLocalFile($publicFilename)) {
        return \unlink($publicFilename);
      }
      return TRUE;
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
   *
   * @return array array('filename' => string, 'is_temporary' => FALSE)
   */
  public function getLocalFile($storageGroup, $storageId) {
    $storageFilename = $this->_getStorageFilename($storageGroup, $storageId, FALSE);
    if ($this->_existLocalFile($storageFilename)) {
      return [
        'filename' => $storageFilename,
        'is_temporary' => FALSE
      ];
    }
    return NULL;
  }

  /**
   * get public url for a storage id if file is linked
   *
   * @param string $storageGroup
   * @param string $storageId
   * @param string $mimeType
   *
   * @return string|null
   */
  public function getURL($storageGroup, $storageId, $mimeType) {
    if (\strlen($storageId) > $this->_storageDirectoryDepth) {
      $publicFilename = $this->_getPublicFilename(
        $storageGroup, $storageId, $mimeType, FALSE
      );
      if ($this->_existLocalFile($publicFilename)) {
        $result = $this->_publicURL.$storageGroup;
        for ($i = $this->_storageDirectoryDepth, $offset = 0; $i > 0; $i--, $offset++) {
          $result .= '/'.\substr($storageId, $offset, 1);
        }
        return $result.'/'.$storageId.$this->_getPublicExtension($storageId, $mimeType);
      }
    }
    return NULL;
  }
}
