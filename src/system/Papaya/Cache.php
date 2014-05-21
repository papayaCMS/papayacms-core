<?php
/**
* Papaya caching interface with flexible services
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
* @package Papaya-Library
* @subpackage Cache
* @version $Id: Cache.php 39721 2014-04-07 13:13:23Z weinert $
*/

/**
* Papaya caching interface with flexible services
* @package Papaya-Library
* @subpackage Cache
*/
class PapayaCache {

  const OUTPUT = 'main';
  const DATA = 'data';
  const IMAGES = 'images';

  /**
  * Store create cache service depoending on their configuration
  *
  * @var array(string=>PapayaCacheService)
  */
  private static $_serviceObjects = array();

  /**
   * Get papaya caching service object
   *
   * @param PapayaConfiguration $configuration
   * @param boolean $static remember service object an return at second request
   * @throws UnexpectedValueException
   * @return PapayaCacheService
   */
  public static function getService($configuration, $static = TRUE) {
    $configuration = self::prepareConfiguration($configuration);
    $configurationId = $configuration->getHash();
    if ($static && isset(self::$_serviceObjects[$configurationId])) {
      return self::$_serviceObjects[$configurationId];
    }
    if (!empty($configuration['SERVICE'])) {
      $class = 'PapayaCacheService'.ucfirst($configuration['SERVICE']);
      if (class_exists($class)) {
        $object = new $class($configuration);
        if ($static) {
          return self::$_serviceObjects[$configurationId] = $object;
        } else {
          return $object;
        }
      } else {
        throw new UnexpectedValueException(
          sprintf('Unknown cache service "%s".', $class)
        );
      }
    } else {
      throw new UnexpectedValueException('No cache service defined.');
    }
  }

  /**
   * If not already provided, create a cache configuration from the given configuation
   * using mapping definition.
   *
   * @param PapayaConfiguration $configuration
   * @return \PapayaCacheConfiguration
   */
  public static function prepareConfiguration($configuration) {
    if (!($configuration instanceof PapayaCacheConfiguration)) {
      $result = new PapayaCacheConfiguration();
      $result->assign(
        array(
          'SERVICE' => $configuration->get('PAPAYA_CACHE_SERVICE', 'file'),
          'FILESYSTEM_PATH' => $configuration->get('PAPAYA_PATH_CACHE'),
          'FILESYSTEM_DISABLE_CLEAR' =>
            $configuration->get('PAPAYA_CACHE_DISABLE_FILE_DELETE'),
          'FILESYSTEM_NOTIFIER_SCRIPT' => $configuration->get('PAPAYA_CACHE_NOTIFIER', ''),
          'MEMCACHE_SERVERS' => $configuration->get('PAPAYA_CACHE_MEMCACHE_SERVERS'),
        )
      );
      return $result;
    }
    return $configuration;
  }

  /**
   * Get the cache for the specified use.
   *
   * @param string $for
   * @param PapayaConfiguration $globalConfiguration
   * @param bool $static
   * @return FALSE|PapayaCacheService
   */
  public static function get($for, $globalConfiguration, $static = TRUE) {
    switch ($for) {
    case self::DATA :
      if ($globalConfiguration->get('PAPAYA_CACHE_DATA', FALSE)) {
        $configuration = new PapayaCacheConfiguration();
        $configuration->assign(
          array(
            'SERVICE' =>
              $globalConfiguration->get('PAPAYA_CACHE_DATA_SERVICE', 'file'),
            'FILESYSTEM_PATH' =>
              $globalConfiguration->get('PAPAYA_PATH_CACHE'),
            'FILESYSTEM_NOTIFIER_SCRIPT' =>
              $configuration->get('PAPAYA_CACHE_NOTIFIER', ''),
            'MEMCACHE_SERVERS' =>
              $globalConfiguration->get('PAPAYA_CACHE_DATA_MEMCACHE_SERVERS'),
          )
        );
        return self::getService($configuration, $static);
      }
      break;
    case self::IMAGES :
      if ($globalConfiguration->get('PAPAYA_CACHE_IMAGES', FALSE)) {
        $configuration = new PapayaCacheConfiguration();
        $configuration->assign(
          array(
            'SERVICE' =>
              $globalConfiguration->get('PAPAYA_CACHE_IMAGES_SERVICE', 'file'),
            'FILESYSTEM_PATH' =>
              $globalConfiguration->get('PAPAYA_PATH_CACHE'),
            'FILESYSTEM_NOTIFIER_SCRIPT' =>
              $configuration->get('PAPAYA_CACHE_NOTIFIER', ''),
            'MEMCACHE_SERVERS' =>
              $globalConfiguration->get('PAPAYA_CACHE_IMAGES_MEMCACHE_SERVERS'),
          )
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
    self::$_serviceObjects = array();
  }
}