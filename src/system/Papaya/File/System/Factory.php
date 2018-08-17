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
 * A factory object that creates file and directory wrapper objects
 *
 * @package Papaya-Library
 * @subpackage FileSystem
 */
class Factory {

  /**
   * Return an object wrapping a file in the file system
   *
   * @param string $filename
   * @return \Papaya\File\System\File
   */
  public function getFile($filename) {
    return new \Papaya\File\System\File($filename);
  }

  /**
   * Return an object wrapping a directory in the file system
   *
   * @param string $directory
   * @return Directory
   */
  public function getDirectory($directory) {
    return new Directory($directory);
  }
}
