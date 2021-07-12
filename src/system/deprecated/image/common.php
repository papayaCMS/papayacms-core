<?php
/**
* image conversions base class
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
* @version $Id: common.php 38664 2013-09-09 18:54:43Z weinert $
*/

/**
* image conversions base class
*
* @package Papaya
* @subpackage Images-Convert
*/
class imgconv_common {

  /**
  * background color to replace transparent areas
  * @var integer
  */
  var $backgroundColor = '#FFFFFF';

  /**
  * image formats that support transparency
  * @var array
  */
  var $transparentFormats = array(
    'png', 'gif'
  );

  /**
  * Initialize image converter
  * @access public
  * @return void
  */
  function initialize() {
    //abstract function
  }

  /**
  * Can read $formatSrc, can write $formatDes
  *
  * @param string $formatSrc
  * @param string $formatDes optional
  * @access public
  * @return boolean
  */
  function canConvert($formatSrc, $formatDes = NULL) {
    return FALSE;
  }

  /**
  * convert $fileSrc to $fileDes in format $formatDes
  *
  * @param string $fileSrc path/filename of source file
  * @param string $fileDes path/filename of destination file
  * @param string $formatDes destination file format
  * @access public
  * @return boolean
  */
  function convert($fileSrc, $fileDes, $formatDes) {
    return FALSE;
  }

  /**
  * get file format of file
  *
  * @param string $fileName
  * @access public
  * @return string
  */
  function getFileFormat($fileName) {
    if (file_exists($fileName) && is_file($fileName) && is_readable($fileName)) {
      list(, , $formatId) = getimagesize($fileName);
      $formatIds = array(
        1 => 'gif', 2 => 'jpg', 3 => 'png', 4 => 'swf', 5 => 'psd',
        6 => 'bmp', 7 => 'tiff', 8 => 'tiff', 9 => 'jpc', 10 => 'jp2',
        11 => 'jpx', 12 => 'jb2', 13 => 'swf', 14 => 'iff', 15 => 'wbmp',
        16 => 'xbm', 17 => 'xpm'
      );
      if (isset($formatIds[$formatId])) {
        return $formatIds[$formatId];
      }
    }
    return FALSE;
  }

  /**
  * convert a color string to rgb
  *
  * @param $colorStr
  * @access public
  * @return array
  */
  function colorToRGB($colorStr) {
    $colorStr = ltrim($colorStr, '#');
    if (strlen($colorStr) <= 3) {
      while (strlen($colorStr) < 3) {
        $colorStr .= '0';
      }
      $r = hexdec(str_repeat(substr($colorStr, 0, 1), 2));
      $g = hexdec(str_repeat(substr($colorStr, 1, 1), 2));
      $b = hexdec(str_repeat(substr($colorStr, 2, 1), 2));
    } else {
      while (strlen($colorStr) < 6) {
        $colorStr .= '0';
      }
      $r = hexdec(substr($colorStr, 0, 2));
      $g = hexdec(substr($colorStr, 2, 2));
      $b = hexdec(substr($colorStr, 4, 2));
    }
    return array($r, $g, $b);
  }
}

