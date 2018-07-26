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
* image converter
*
* @package Papaya
* @subpackage Administration
* @version $Id: papaya_imageconvert.php 39616 2014-03-19 09:22:18Z weinert $
*/
abstract class papaya_imageconvert extends base_object {


  /**
   * get a converter object
   *
   * @param string $fileName
   * @param \PapayaConfiguration $options
   * @access public
   * @return imgconv_common
   */
  public static function getConverter($fileName, PapayaConfiguration $options = NULL) {
    $result = NULL;
    $converters = array(
      'gd', 'netpbm', 'imagemagick', 'graphicsmagick'
    );
    if (!isset($options)) {
      /** @noinspection PhpUndefinedFieldInspection */
      $options = \PapayaApplication::getInstance()->options;
    }
    if (is_file($fileName) && is_readable($fileName)) {
      $converter = $options->get(
        'PAPAYA_IMAGE_CONVERTER',
        'gd',
        new \PapayaFilterList($converters)
      );
      $className = 'imgconv_'.$converter;
      if ($converter != '' && class_exists($className)) {
        $result = new $className;
      }
    }
    return $result;
  }
}

