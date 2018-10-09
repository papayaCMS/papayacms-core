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
namespace Papaya\Media;

/**
 * Papaya Media Storage Service Factory
 *
 * @package Papaya-Library
 * @subpackage Media-Storage
 */
class Storage {
  private static $_serviceObjects = [];

  private static $_services = ['File', 'S3'];

  /**
   * get the service
   *
   * @param string $service
   * @param \Papaya\Configuration $configuration
   * @param bool $static optional, default value TRUE
   *
   * @throws \InvalidArgumentException
   *
   * @return \Papaya\Media\Storage\Service
   */
  public static function getService($service = '', \Papaya\Configuration $configuration = NULL, $static = TRUE) {
    if (empty($service)) {
      $service = \defined('PAPAYA_MEDIA_STORAGE_SERVICE')
        ? PAPAYA_MEDIA_STORAGE_SERVICE : 'File';
    }
    $service = \ucfirst(\strtolower($service));
    if (\in_array($service, self::$_services, TRUE)) {
      if ($static && isset(self::$_serviceObjects[$service])) {
        return self::$_serviceObjects[$service];
      }
      $class = __CLASS__.'\\Service\\'.$service;
      $object = new $class();
      if (NULL !== $configuration && \method_exists($object, 'setConfiguration')) {
        $object->setConfiguration($configuration);
      }
      if ($static) {
        return self::$_serviceObjects[$service] = $object;
      }
      return $object;
    }
    throw new \InvalidArgumentException(
      'Unknown media storage service: '.$service
    );
  }
}
