<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS\Cache;

use Papaya\Cache\Configuration as CacheConfiguration;

/**
 * Papaya caching interface with flexible services
 *
 * @package Papaya-Library
 * @subpackage Cache
 */
class Cache extends \Papaya\Cache {

  public const OUTPUT = 'main';
  public const DATA = 'data';
  public const IMAGES = 'images';
  /**
   * Get papaya caching service object
   *
   * @param \Papaya\Configuration $configuration
   * @param bool $static remember service object an return at second request
   * @throws \UnexpectedValueException
   * @return \Papaya\Cache\Service
   */
  public static function getService($configuration, $static = TRUE) {
    return parent::getService(self::prepareConfiguration($configuration), $static);
  }

  /**
   * If not already provided, create a cache configuration from the given configuation
   * using mapping definition.
   *
   * @param \Papaya\Configuration $configuration
   *
   * @return CacheConfiguration
   */
  public static function prepareConfiguration(\Papaya\Configuration $configuration) {
    if (!($configuration instanceof CacheConfiguration)) {
      $result = new CacheConfiguration();
      $result->assign(
        [
          'SERVICE' => $configuration->get(\Papaya\CMS\CMSConfiguration::CACHE_SERVICE, 'file'),
          'FILESYSTEM_PATH' => $configuration->get(\Papaya\CMS\CMSConfiguration::PATH_CACHE),
          'FILESYSTEM_DISABLE_CLEAR' =>
          $configuration->get(\Papaya\CMS\CMSConfiguration::CACHE_DISABLE_FILE_DELETE),
          'FILESYSTEM_NOTIFIER_SCRIPT' => $configuration->get(\Papaya\CMS\CMSConfiguration::CACHE_NOTIFIER, ''),
          'MEMCACHE_SERVERS' => $configuration->get(\Papaya\CMS\CMSConfiguration::CACHE_MEMCACHE_SERVERS),
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
        if ($globalConfiguration->get(\Papaya\CMS\CMSConfiguration::CACHE_DATA, FALSE)) {
          $configuration = new CacheConfiguration();
          $configuration->assign(
            [
              'SERVICE' =>
              $globalConfiguration->get(\Papaya\CMS\CMSConfiguration::CACHE_DATA_SERVICE, 'file'),
              'FILESYSTEM_PATH' =>
              $globalConfiguration->get(\Papaya\CMS\CMSConfiguration::PATH_CACHE),
              'FILESYSTEM_NOTIFIER_SCRIPT' =>
              $configuration->get(\Papaya\CMS\CMSConfiguration::CACHE_NOTIFIER, ''),
              'MEMCACHE_SERVERS' =>
              $globalConfiguration->get(\Papaya\CMS\CMSConfiguration::CACHE_DATA_MEMCACHE_SERVERS),
            ]
          );
          return self::getService($configuration, $static);
        }
      break;
      case self::IMAGES :
        if ($globalConfiguration->get(\Papaya\CMS\CMSConfiguration::CACHE_IMAGES, FALSE)) {
          $configuration = new CacheConfiguration();
          $configuration->assign(
            [
              'SERVICE' =>
              $globalConfiguration->get(\Papaya\CMS\CMSConfiguration::CACHE_IMAGES_SERVICE, 'file'),
              'FILESYSTEM_PATH' =>
              $globalConfiguration->get(\Papaya\CMS\CMSConfiguration::PATH_CACHE),
              'FILESYSTEM_NOTIFIER_SCRIPT' =>
              $configuration->get(\Papaya\CMS\CMSConfiguration::CACHE_NOTIFIER, ''),
              'MEMCACHE_SERVERS' =>
              $globalConfiguration->get(\Papaya\CMS\CMSConfiguration::CACHE_IMAGES_MEMCACHE_SERVERS),
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
}
