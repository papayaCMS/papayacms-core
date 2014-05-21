<?php
/**
* Zip funtions (abstraction)
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
* @package Papaya
* @subpackage Core
* @version $Id: sys_zip.php 39731 2014-04-08 10:08:07Z weinert $
*/

/**
* Zip funtions (abstraction)
*
* @package Papaya
* @subpackage Core
*/
class sys_zip {

  /**
   * pclzip object
   *
   * @var PclZip
   */
  var $archive = NULL;

  /**
   * PHP5 constructor
   *
   * @param string $fileName
   */
  function __construct($fileName) {
    $this->initialize($fileName);
  }

  /**
   * initialize
   *
   * @param string $fileName
   * @access private
   */
  function initialize($fileName) {
    include_once(PAPAYA_INCLUDE_PATH.'external/pclzip/pclzip.lib.php');
    $this->archive = new PclZip($fileName);
  }

  /**
   * extract archive to targetpath
   *
   * @param string $targetPath
   * @return boolean success
   */
  function extract($targetPath) {
    if ($this->archive->extract(PCLZIP_OPT_PATH, $targetPath) == 0) {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
   * create an archive with all sub elements of the given directory
   *
   * @param string $directory
   * @return integer|array  0 or array of added files
   */
  function createFromDirectory($directory) {
    return $this->archive->create(
      $directory,
      PCLZIP_OPT_REMOVE_PATH,
      $directory,
      PCLZIP_OPT_ADD_PATH,
      // otherwise, it will create a "blank" top directory
      'content'
    );
  }
}


