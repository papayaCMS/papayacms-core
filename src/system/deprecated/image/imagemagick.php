<?php
/**
* image conversion using imagemagick binary
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
* @subpackage Images-Convert
* @version $Id: imagemagick.php 39601 2014-03-18 14:10:41Z weinert $
*/


/**
* base image conversion class
*/
require_once(dirname(__FILE__).'/common.php');

/**
* image conversion using imagemagick binary
*
* @package Papaya
* @subpackage Images-Convert
*/
class imgconv_imagemagick extends imgconv_common {

  /**
  * supported import formats
  * @var array
  */
  var $imageFormats = array(
    'bmp' => 1, 'gif' => 1, 'jpg' => 1, 'png' => 1, 'tiff' => 1,
    // tga files are identified as wbmp, but wbmp files can also be converted
    'tga' => 1, 'wbmp' => 1,
    // may need special treatment, e.g. flattening of image or page (pdf)
    // 'psd' => 1, 'xcf' => 1, 'pdf' => 1
  );

  /**
  * path to program binary
  * @var string
  */
  var $binaryPath = PAPAYA_IMAGE_CONVERTER_PATH;

  /**
  * jpeg compression quality
  * @var integer
  */
  var $jpegQuality = PAPAYA_THUMBS_JPEGQUALITY;

  /**
  * can we convert one format to another?
  *
  * @param string $formatSrc Source format
  * @param string $formatDes Destination format
  * @access public
  * @return boolean
  */
  function canConvert($formatSrc, $formatDes = NULL) {
    if (isset($this->imageFormats[$formatSrc]) &&
        ((!isset($formatDes)) || $this->imageFormats[$formatDes])) {
      return (bool)$this->getExecutable();
    } else {
      return FALSE;
    }
  }

  /**
  * convert fileSrc to fileDes
  *
  * @param string $fileSrc source filename
  * @param string $fileDes destination filename
  * @param string $formatDes destination file format
  * @access public
  * @return boolean
  */
  function convert($fileSrc, $fileDes, $formatDes) {
    if (is_file($fileSrc) && is_readable($fileSrc) &&
        isset($this->imageFormats[$formatDes])) {
      $binary = $this->getExecutable();
      if ($binary) {
        return $this->_convertImage($binary, $fileSrc, $fileDes, $formatDes);
      }
    }
    return FALSE;
  }

  /**
  * Execute binary
  * @param string $binary
  * @param string $fileSrc
  * @param string $fileDes
  * @param string $formatDes
  * @return boolean
  */
  function _convertImage($binary, $fileSrc, $fileDes, $formatDes) {
    $cmd = escapeshellcmd($binary).' '.escapeshellarg($fileSrc).' '.
      escapeshellcmd($formatDes).':'.escapeshellarg($fileDes);
    $outputLines = array();
    $returnValue = 0;
    exec($cmd, $outputLines, $returnValue);
    if ($returnValue == 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * get executable file name with path
   *
   * @access public
   * @return string
   */
  function getExecutable() {
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      $executable = $this->binaryPath.'convert.exe';
    } else {
      $executable = $this->binaryPath.'convert';
    }
    if (file_exists($executable) && is_file($executable)) {
      return $executable;
    }
    return NULL;
  }
}

