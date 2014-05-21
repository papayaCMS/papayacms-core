<?php
/**
* Image conversion using GraphicsMagick binaries
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: graphicsmagick.php 39601 2014-03-18 14:10:41Z weinert $
*/


/**
* base image conversion class
*/
require_once(dirname(__FILE__).'/common.php');

/**
* Image conversion using GraphicsMagick binaries. GraphicsMagick uses the same
* commands and parameters as ImageMagick.
*
* @package Papaya
* @subpackage Images-Convert
*/
class imgconv_graphicsmagick extends imgconv_common {

  /**
  * Supported import formats
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
  * Path to program binary. This variable needs to be set in the system settings
  * of papaya CMS.
  *
  * @var string
  */
  var $binaryPath = PAPAYA_IMAGE_CONVERTER_PATH;

  /**
  * Jpeg compression quality.
  * @var integer
  */
  var $jpegQuality = PAPAYA_THUMBS_JPEGQUALITY;

  /**
  * Checks if it is possible to convert the given source image format $formatSrc
  * into the desired target image format $formatDes.
  *
  * @param string $formatSrc Source format
  * @param string $formatDes Destination format
  * @access public
  * @return boolean TRUE if conversion into target format is possible,
  *         otherwise FALSE.
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
  * Converts the file in $fileSrc to the file in $fileDes. GraphicsMagick
  * can tell into which type to convert from the file ending of the target
  * file. Further specifications like dimensions are given by the third
  * parameter, $formatDes.
  *
  * @param string $fileSrc source filename
  * @param string $fileDes destination filename
  * @param string $formatDes destination file format
  * @access public
  * @return boolean FALSE if the source file does not exist, is not readable, or
  *         the desired target file format is not supported.
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
    $cmd = escapeshellcmd($binary).' convert '.escapeshellarg($fileSrc).' '.
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
   * @internal param string $binaryName
   * @access public
   * @return string
   */
  function getExecutable() {
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      $executable = $this->binaryPath.'gm.exe';
    } else {
      $executable = $this->binaryPath.'gm';
    }
    if (file_exists($executable) && is_file($executable)) {
      return $executable;
    }
    return NULL;
  }
}


