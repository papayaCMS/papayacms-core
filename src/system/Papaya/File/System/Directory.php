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

namespace Papaya\File\System;

/**
 * Wrapping a file entry in the file system to call operation as methods
 *
 * @package Papaya-Library
 * @subpackage FileSystem
 */
class Directory {

  const FETCH_FILES = 1;
  const FETCH_DIRECTORIES = 2;
  const FETCH_FILES_AND_DIRECTORIES = 3;

  private $_path = '';

  /**
   * Create object an store the path after cleanup
   *
   * @param string $path
   */
  public function __construct($path) {
    \PapayaUtilConstraints::assertNotEmpty($path);
    $this->_path = \PapayaUtilFilePath::cleanup($path, FALSE);
  }

  /**
   * return the path stored in the object
   */
  public function __toString() {
    return $this->_path;
  }

  /**
   * Does the directory exists?
   *
   * @return boolean
   */
  public function exists() {
    return file_exists($this->_path) && is_dir($this->_path);
  }

  /**
   * Is the directory readable?
   *
   * @return boolean
   */
  public function isReadable() {
    return $this->exists() && is_readable($this->_path);
  }

  /**
   * Is the directory writeable?
   *
   * @return boolean
   */
  public function isWriteable() {
    return $this->exists() && is_writeable($this->_path);
  }

  /**
   * Get file list, ignorefiles starting with a dot, by default
   *
   * @param string $filter
   * @param integer $type
   * @return \Traversable
   */
  public function getEntries($filter = '(^[^.])', $type = self::FETCH_FILES_AND_DIRECTORIES) {
    $result = new \FilesystemIterator(
      $this->_path,
      \FilesystemIterator::SKIP_DOTS |
      \FilesystemIterator::UNIX_PATHS |
      \FilesystemIterator::KEY_AS_FILENAME |
      \FilesystemIterator::CURRENT_AS_FILEINFO
    );
    switch ($type) {
      case self::FETCH_FILES :
        $result = new \PapayaIteratorFilterCallback(
          $result, array($this, 'callbackFileInfoIsFile')
        );
      break;
      case self::FETCH_DIRECTORIES :
        $result = new \PapayaIteratorFilterCallback(
          $result, array($this, 'callbackFileInfoIsDirectory')
        );
      break;
    }
    if (!empty($filter)) {
      return new \PapayaIteratorFilterRegex(
        $result, $filter, 0, \PapayaIteratorFilterRegex::FILTER_KEYS
      );
    } else {
      return $result;
    }
  }

  public function callbackFileInfoIsFile(\splFileInfo $fileInfo) {
    return $fileInfo->isFile();
  }

  public function callbackFileInfoIsDirectory(\splFileInfo $fileInfo) {
    return $fileInfo->isDir();
  }
}
