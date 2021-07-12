<?php
/**
* image conversion using netpbm binaries
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
* @version $Id: netpbm.php 39607 2014-03-18 15:38:11Z weinert $
*/


/**
* base image conversion class
*/
require_once(dirname(__FILE__).'/common.php');

/**
* image conversion using netpbm binaries
*
* @package Papaya
* @subpackage Images-Convert
*/
class imgconv_netpbm extends imgconv_common {

  /**
  * supported import formats
  * @var array
  */
  var $imageFormats = array(
    'bmp', 'gif', 'jpeg', 'png', 'tiff', 'tga'
  );

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
    if (isset($this->imageFormats[$formatSrc])) {
      $srcResult = (bool)$this->getExecutable($formatSrc);
    } else {
      $srcResult = FALSE;
    }
    if (!isset($formatDes)) {
      $desResult = TRUE;
    } elseif (isset($this->imageFormats[$formatDes])) {
      $desResult = (bool)$this->getExecutable($formatDes);
    } else {
      $desResult = FALSE;
    }
    return $srcResult && $desResult;
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
      if ($formatSrc = $this->getFileFormat($fileSrc)) {
        $binarySrc = $this->getExecutable($formatSrc);
        $binaryDes = $this->getExecutable($formatDes);
        if ($binarySrc && $binaryDes) {
          return $this->_convertImage($binarySrc, $binaryDes, $fileSrc, $fileDes);
        }
      }
    }
    return FALSE;
  }

  /**
   * Execute binary
   * @param string $binarySrc
   * @param string $binaryDes
   * @param string $fileSrc
   * @param string $fileDes
   * @return boolean
   */
  function _convertImage($binarySrc, $binaryDes, $fileSrc, $fileDes) {
    $cmd = escapeshellcmd($binarySrc).' '.escapeshellarg($fileSrc).' | '.
      escapeshellcmd($binaryDes).' >'.escapeshellarg($fileDes);
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
   * @param string $format
   * @access public
   * @return string
   */
  function getExecutable($format) {
    if (isset($this->imageFormats[$format])) {
      $internalFormats = array('pnm', 'ppm', 'pbm', 'pgm');
      foreach ($internalFormats as $intFormat) {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
          $executable = $this->binaryPath.$format.'to'.$intFormat.'.exe';
        } else {
          $executable = $this->binaryPath.$format.'to'.$intFormat;
        }
        if (file_exists($executable) && is_file($executable)) {
          return $executable;
        }
      }
    }
    return NULL;
  }
}

