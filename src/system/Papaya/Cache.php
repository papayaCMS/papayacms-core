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
namespace Papaya {

  use Papaya\Cache\Configuration as CacheConfiguration;
  use Papaya\Cache\Service as CacheService;

  class Cache {


    /**
     * Store create cache service depending on their configuration
     *
     * @var array(string=>\Papaya\Cache\Service)
     */
    private static $_serviceObjects = [];

    /**
     * Get papaya caching service object
     *
     * @param CacheConfiguration $configuration
     * @param bool $static remember service object an return at second request
     * @return CacheService
     * @throws \UnexpectedValueException
     */
    public static function getService($configuration, bool $static = TRUE) {
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

    public static function getServices(): array {
      return self::$_serviceObjects;
    }

    /**
     * Unset all stored static cache objects
     */
    public static function reset(): void {
      self::$_serviceObjects = [];
    }
  }
}
