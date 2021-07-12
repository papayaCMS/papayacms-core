<?php
/**
* image conversion using gd library
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
* @version $Id: gd.php 38664 2013-09-09 18:54:43Z weinert $
*/


/**
* base image conversion class
*/
require_once(dirname(__FILE__).'/common.php');

/**
* image conversion using gd library
*
* @package Papaya
* @subpackage Images-Convert
*/
class imgconv_gd extends imgconv_common {

  /**
  * supported formats
  * @var array
  */
  var $imageTypes = array();

  /**
  * jpeg compression quality
  * @var integer
  */
  var $jpegQuality = PAPAYA_THUMBS_JPEGQUALITY;

  /**
  * file format of the currently loaded source file
  * @var string
  */
  var $srcFormat = '';

  /**
  * initialize gd image converter, get support formats
  *
  * @access public
  */
  function initialize() {
    if (function_exists('gd_info')) {
      $gdInfo = gd_info();
      $this->imageTypes = array(
        'gif_read' => $gdInfo['GIF Read Support'],
        'gif_write' => $gdInfo['GIF Create Support'],
        'png' => $gdInfo['PNG Support'],
        'wbmp' => $gdInfo['WBMP Support'],
        'xbm' => $gdInfo['XBM Support']
      );
      if (isset($gdInfo['JPEG Support'])) {
        $this->imageTypes['jpg'] = $gdInfo['JPEG Support'];
      } elseif (isset($gdInfo['JPG Support'])) {
        $this->imageTypes['jpg'] = $gdInfo['JPG Support'];
      } else {
        $this->imageTypes['jpg'] = FALSE;
      }
    } elseif (function_exists('imagetypes')) {
      $imageTypes = imagetypes();
      $this->imageTypes = array(
        'gif_read' => $imageTypes & IMG_GIF,
        'gif_write' => $imageTypes & IMG_GIF,
        'png' => $imageTypes & IMG_PNG,
        'jpg' => $imageTypes & IMG_JPG,
        'wbmp' => $imageTypes & IMG_WBMP,
        'xpm' => $imageTypes & IMG_XPM
      );
    } else {
      $this->imageTypes = array(
        'gif_read' => FALSE,
        'gif_write' => FALSE,
        'png' => FALSE,
        'jpg' => FALSE,
        'wbmp' => FALSE,
        'xpm' => FALSE
      );
    }
  }

  /**
  * can we convert one format to another?
  *
  * @param string $formatSrc Source format
  * @param string $formatDes Destination format
  * @access public
  * @return boolean
  */
  function canConvert($formatSrc, $formatDes = NULL) {
    if (isset($this->imageTypes[$formatSrc]) && $this->imageTypes[$formatSrc]) {
      $srcResult = TRUE;
    } elseif (isset($this->imageTypes[$formatSrc.'_read'])
              && $this->imageTypes[$formatSrc.'_read']) {
      $srcResult = TRUE;
    } else {
      $srcResult = FALSE;
    }
    if (!isset($formatDes)) {
      $desResult = TRUE;
    } elseif (isset($this->imageTypes[$formatDes]) && $this->imageTypes[$formatDes]) {
      $desResult = TRUE;
    } elseif (isset($this->imageTypes[$formatDes.'_write'])
              && $this->imageTypes[$formatDes.'_write']) {
      $desResult = TRUE;
    } else {
      $desResult = FALSE;
    }
    return ($srcResult && $desResult);
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
    if (is_file($fileSrc) && is_readable($fileSrc)) {
      if ($image = $this->loadFile($fileSrc)) {
        $width = imageSX($image);
        $height = imageSY($image);
        if ($width > 0 && $height > 0) {
          if (in_array($this->srcFormat, $this->transparentFormats) &&
              !in_array($formatDes, $this->transparentFormats)) {
            $targetImage = imagecreatetruecolor($width, $height);
            $bgColor = $this->colorToRGB($this->backgroundColor);
            $bgColorIdx = imagecolorallocate(
              $targetImage,
              $bgColor[0],
              $bgColor[1],
              $bgColor[2]
            );
            imagefilledrectangle($targetImage, 0, 0, $width, $height, $bgColorIdx);
            imagecopy($targetImage, $image, 0, 0, 0, 0, $width, $height);
            return $this->saveFile($targetImage, $fileDes, $formatDes);
          } else {
            return $this->saveFile($image, $fileDes, $formatDes);
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * load file
  *
  * @param string $fileName
  * @access public
  * @return resource image
  */
  function loadFile($fileName) {
    list(, , $fileType) = getimagesize($fileName);
    $result = FALSE;
    switch ($fileType) {
    case 1 :
      $result = imagecreatefromGIF($fileName);
      $this->srcFormat = 'gif';
      break;
    case 2 :
      $result = imagecreatefromJPEG($fileName);
      $this->srcFormat = 'jpg';
      break;
    case 3 :
      $result = imagecreatefromPNG($fileName);
      $this->srcFormat = 'png';
      break;
    case 15 :
      $result = imagecreatefromWBMP($fileName);
      $this->srcFormat = 'wbmp';
      break;
    case 16 :
      $result = imagecreatefromXBM($fileName);
      $this->srcFormat = 'xbm';
      break;
    case 17 :
      $result = imagecreatefromXPM($fileName);
      $this->srcFormat = 'xpm';
      break;
    }
    return $result;
  }

  /**
  * save file
  *
  * @param resource $im Image
  * @param string $fileName destination file name
  * @param string $fileFormat destination format
  * @access public
  * @return boolean
  */
  function saveFile($im, $fileName, $fileFormat) {
    switch ($fileFormat) {
    case 'gif' :
      $saved = imageGIF($im, $fileName);
      break;
    case 'jpg' :
    case 'jpeg' :
      $saved = imageJPEG($im, $fileName, $this->jpegQuality);
      break;
    case 'png' :
      $saved = imagePNG($im, $fileName);
      break;
    case 'wbmp' :
      $saved = imageWBMP($im, $fileName);
      break;
    case 'xbm' :
      $saved = imageXBM($im, $fileName);
      break;
    default:
      return FALSE;
    }
    return $saved;
  }
}


