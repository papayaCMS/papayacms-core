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
* Wrapping a file entry in the file system to call operation as methods
*
* @package Papaya-Library
* @subpackage FileSystem
*/
class PapayaFileSystemFile {

  private $_filename = '';

  /**
   * create the object and store the filename
   *
   * @param string $filename
   */
  public function __construct($filename) {
    \PapayaUtilConstraints::assertNotEmpty($filename);
    $this->_filename = $filename;
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
   * @return boolean
   */
  public function exists() {
    return file_exists($this->_filename) && is_file($this->_filename);
  }

  /**
   * Is the file readable?
   *
   * @return boolean
   */
  public function isReadable() {
    return $this->exists() && is_readable($this->_filename);
  }

  /**
   * Is the file writeable?
   *
   * @return boolean
   */
  public function isWriteable() {
    return $this->exists() && is_writeable($this->_filename);
  }

  /**
   * Was the file uploaded?
   *
   * @codeCoverageIgnore
   * @return boolean
   */
  public function isUploadedFile() {
    return is_uploaded_file($this->_filename);
  }

  /**
   * Read the file content
   *
   * @return string
   */
  public function getContents() {
    return file_get_contents($this->_filename);
  }

  /**
   * Write the content into the file
   * @param mixed $content
   */
  public function putContents($content) {
    file_put_contents($this->_filename, (string)$content);
  }

}
