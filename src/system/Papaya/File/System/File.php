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
namespace Papaya\File\System {

  use Papaya\File\System\Factory as FileSystemFactory;
  use Papaya\Utility;

  /**
   * Wrapping a file entry in the file system to call operation as methods
   *
   * @package Papaya-Library
   * @subpackage FileSystem
   */
  class File {
    private $_filename;
    /**
     * @var Factory
     */
    private $_fileSystem;

    /**
     * create the object and store the filename
     *
     * @param string $filename
     * @param FileSystemFactory|null $fileSystem
     */
    public function __construct($filename, FileSystemFactory $fileSystem = NULL) {
      Utility\Constraints::assertNotEmpty($filename);
      $this->_filename = $filename;
      $this->_fileSystem = $fileSystem;
    }

    /**
     * return the filename stored in the object
     *
     * @return string
     */
    public function __toString() {
      return $this->_filename;
    }

    /**
     * Does the file exists?
     *
     * @return bool
     */
    public function exists() {
      return \file_exists($this->_filename) && \is_file($this->_filename);
    }

    /**
     * Is the file readable?
     *
     * @return bool
     */
    public function isReadable() {
      return $this->exists() && \is_readable($this->_filename);
    }

    /**
     * Is the file writable?
     *
     * @return bool
     */
    public function isWritable() {
      return $this->exists() && \is_writable($this->_filename);
    }

    /** @noinspection SpellCheckingInspection */

    /**
     * Is the file writable?
     *
     * @return bool
     * @deprecated
     */
    public function isWritewable() {
      return $this->isWritable();
    }

    /**
     * Was the file uploaded?
     *
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function isUploadedFile() {
      return \is_uploaded_file($this->_filename);
    }

    /**
     * Read the file content
     *
     * @return string
     */
    public function getContents() {
      return \file_get_contents($this->_filename);
    }

    /**
     * Write the content into the file
     *
     * @param mixed $content
     */
    public function putContents($content) {
      \file_put_contents($this->_filename, (string)$content);
    }

    /**
     * @return bool
     */
    public function unlink() {
      return unlink($this->_filename);
    }

    /**
     * @return Directory
     */
    public function getDirectory() {
      return $this->fileSystem()->getDirectory(dirname($this->_filename));
    }

    public function fileSystem(FileSystemFactory $fileSystem = NULL) {
      if (NULL !== $fileSystem) {
        $this->_fileSystem = $fileSystem;
      } elseif (NULL === $this->_fileSystem) {
        $this->_fileSystem = new FileSystemFactory();
      }
      return $this->_fileSystem;
    }
  }
}
