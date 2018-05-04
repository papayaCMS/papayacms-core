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
* An file system iterator encapsulating the glob() function
*
* @package Papaya-Library
* @subpackage Iterator
*/
class PapayaIteratorGlob implements IteratorAggregate, Countable {

  private $_path = '';
  private $_flags = 0;
  private $_files = NULL;

  /**
  * Create object and store path and flags.
  *
  * Valid flags:
  * GLOB_MARK - Adds a slash to each directory returned
  * GLOB_NOSORT - Return files as they appear in the directory (no sorting)
  * GLOB_NOCHECK - Return the search pattern if no files matching it were found
  * GLOB_NOESCAPE - Backslashes do not quote metacharacters
  * GLOB_BRACE - Expands {a,b,c} to match 'a', 'b', or 'c'
  * GLOB_ONLYDIR - Return only directory entries which match the pattern
  * GLOB_ERR - Stop on read errors (like unreadable directories), by default errors are ignored.
  *
  * @param string $path
  * @param integer $flags
  */
  public function __construct($path, $flags = 0) {
    $this->_path = $path;
    $this->setFlags($flags);
  }

  /**
  * Set the flags
  *
  * @param integer $flags
  */
  public function setFlags($flags) {
    $this->_flags = $flags;
    $this->rewind();
  }

  /**
  * Clear the internal file list.
  */
  public function rewind() {
    $this->_files = NULL;
  }

  /**
  * Get the currently set flags
  *
  * @return integer
  */
  public function getFlags() {
    return $this->_flags;
  }

  /**
  * The files are loaded if needed. e.g. the $_files member variable is set to NULL. After
  * loaded it returns the loaded values until reset() is called.
  *
  * @return array
  */
  private function getFilesLazy() {
    if (is_null($this->_files)) {
      $this->_files = array();
      foreach (glob($this->_path, $this->_flags) as $file) {
        $this->_files[] = $file;
      }
    }
    return $this->_files;
  }

  /**
  * Return an iterator on the file list.
  *
  * @return \Iterator
  */
  public function getIterator() {
    return new \ArrayIterator($this->getFilesLazy());
  }

  /**
  * Return the file count.
  */
  public function count() {
    return count($this->getFilesLazy());
  }
}
