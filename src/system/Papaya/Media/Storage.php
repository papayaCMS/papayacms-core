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
* Papaya Media Storage Service Factory
* @package Papaya-Library
* @subpackage Media-Storage
*/
class PapayaMediaStorage {

  private static $_serviceObjects = array();

  private static $_services = array('File', 'S3');

  /**
   * get the service
   *
   * @param string $service
   * @param \PapayaConfiguration $configuration
   * @param boolean $static optional, default value TRUE
   * @throws \InvalidArgumentException
   * @access public
   * @return \PapayaMediaStorageService
   */
  public static function getService($service = '', $configuration = NULL, $static = TRUE) {
    if (empty($service)) {
      $service = defined('PAPAYA_MEDIA_STORAGE_SERVICE')
        ? PAPAYA_MEDIA_STORAGE_SERVICE : 'File';
    }
    $service = ucfirst(strtolower($service));
    if (in_array($service, self::$_services)) {
      if ($static && isset(self::$_serviceObjects[$service])) {
        return self::$_serviceObjects[$service];
      }
      $class = 'PapayaMediaStorageService'.$service;
      $object = new $class();
      if (isset($configuration) && method_exists($object, 'setConfiguration')) {
        $object->setConfiguration($configuration);
      }
      if ($static) {
        return self::$_serviceObjects[$service] = $object;
      } else {
        return $object;
      }
    } else {
      throw new \InvalidArgumentException(
        'Unknown media storage service: '.$service
      );
    }
  }
}
