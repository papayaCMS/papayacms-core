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
namespace Papaya;

/**
 * Papaya caching interface with flexible services
 *
 * @package Papaya-Library
 * @subpackage Cache
 */
class Cache {
  const OUTPUT = 'main';

  const DATA = 'data';

  const IMAGES = 'images';

  /**
   * Store create cache service depending on their configuration
   *
   * @var array(string=>\Papaya\Cache\Service)
   */
  private static $_serviceObjects = [];

  /**
   * Get papaya caching service object
   *
   * @param \Papaya\Configuration $configuration
   * @param bool $static remember service object an return at second request
   *
   * @throws \UnexpectedValueException
   *
   * @return \Papaya\Cache\Service
   */
  public static function getService($configuration, $static = TRUE) {
    $configuration = self::prepareConfiguration($configuration);
    $configurationId = $configuration->getHash();
    if ($static && isset(self::$_serviceObjects[$configurationId])) {
      return self::$_serviceObjects[$configurationId];
    }
    if (!empty($configuration['SERVICE'])) {
      $class = 'Papaya\\Cache\\Service\\'.\ucfirst($configuration['SERVICE']);
      if (\class_exists($class)) {
        $object = new $class($configuration);
        if ($static) {
          return self::$_serviceObjects[$configurationId] = $object;
        }
        return $object;
      }
      throw new \UnexpectedValueException(
        \sprintf('Unknown cache service "%s".', $class)
      );
    }
    throw new \UnexpectedValueException('No cache service defined.');
  }

  /**
   * If not already provided, create a cache configuration from the given configuation
   * using mapping definition.
   *
   * @param \Papaya\Configuration $configuration
   *
   * @return \Papaya\Cache\Configuration
   */
  public static function prepareConfiguration($configuration) {
    if (!($configuration instanceof Cache\Configuration)) {
      $result = new Cache\Configuration();
      $result->assign(
        [
          'SERVICE' => $configuration->get('PAPAYA_CACHE_SERVICE', 'file'),
          'FILESYSTEM_PATH' => $configuration->get('PAPAYA_PATH_CACHE'),
          'FILESYSTEM_DISABLE_CLEAR' =>
          $configuration->get('PAPAYA_CACHE_DISABLE_FILE_DELETE'),
          'FILESYSTEM_NOTIFIER_SCRIPT' => $configuration->get('PAPAYA_CACHE_NOTIFIER', ''),
          'MEMCACHE_SERVERS' => $configuration->get('PAPAYA_CACHE_MEMCACHE_SERVERS'),
        ]
      );
      return $result;
    }
    return $configuration;
  }

  /**
   * Get the cache for the specified use.
   *
   * @param string $for
   * @param \Papaya\Configuration $globalConfiguration
   * @param bool $static
   *
   * @return false|\Papaya\Cache\Service
   */
  public static function get($for, $globalConfiguration, $static = TRUE) {
    switch ($for) {
      case self::DATA :
        if ($globalConfiguration->get('PAPAYA_CACHE_DATA', FALSE)) {
          $configuration = new Cache\Configuration();
          $configuration->assign(
            [
              'SERVICE' =>
              $globalConfiguration->get('PAPAYA_CACHE_DATA_SERVICE', 'file'),
              'FILESYSTEM_PATH' =>
              $globalConfiguration->get('PAPAYA_PATH_CACHE'),
              'FILESYSTEM_NOTIFIER_SCRIPT' =>
              $configuration->get('PAPAYA_CACHE_NOTIFIER', ''),
              'MEMCACHE_SERVERS' =>
              $globalConfiguration->get('PAPAYA_CACHE_DATA_MEMCACHE_SERVERS'),
            ]
          );
          return self::getService($configuration, $static);
        }
      break;
      case self::IMAGES :
        if ($globalConfiguration->get('PAPAYA_CACHE_IMAGES', FALSE)) {
          $configuration = new Cache\Configuration();
          $configuration->assign(
            [
              'SERVICE' =>
              $globalConfiguration->get('PAPAYA_CACHE_IMAGES_SERVICE', 'file'),
              'FILESYSTEM_PATH' =>
              $globalConfiguration->get('PAPAYA_PATH_CACHE'),
              'FILESYSTEM_NOTIFIER_SCRIPT' =>
              $configuration->get('PAPAYA_CACHE_NOTIFIER', ''),
              'MEMCACHE_SERVERS' =>
              $globalConfiguration->get('PAPAYA_CACHE_IMAGES_MEMCACHE_SERVERS'),
            ]
          );
          return self::getService($configuration, $static);
        }
      break;
      case self::OUTPUT :
        return self::getService($globalConfiguration, $static);
    }
    return FALSE;
  }

  /**
   * Unset all stored static cache objects
   */
  public static function reset() {
    self::$_serviceObjects = [];
  }
}
