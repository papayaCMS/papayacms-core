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

namespace Papaya\CMS\Cache {

  use Papaya\Cache\Configuration as CacheConfiguration;
  use Papaya\Cache\Service as CacheService;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\CMS\Cache\Cache
   */
  class CacheTest extends \Papaya\TestFramework\TestCase {

    public function tearDown(): void {
      Cache::reset();
    }

    public function testGetServiceDefault() {
      $configuration = $this->mockPapaya()->options();
      $service = Cache::getService($configuration);
      $this->assertInstanceOf(CacheService::class, $service);
      $serviceTwo = Cache::getService($configuration);
      $this->assertSame($service, $serviceTwo);
    }

    public function testGetServiceStaticExpectingSameObject() {
      $configuration = $this->mockPapaya()->options();
      $service = Cache::getService($configuration);
      $this->assertInstanceOf(CacheService\File::class, $service);
      $serviceTwo = Cache::getService($configuration);
      $this->assertSame($service, $serviceTwo);
    }

    public function testGetServiceNonStaticExpectingDifferentObjects() {
      $configuration = $this->mockPapaya()->options();
      $service = Cache::getService($configuration, FALSE);
      $this->assertInstanceOf(CacheService\File::class, $service);
      $serviceTwo = Cache::getService($configuration, FALSE);
      $this->assertNotSame($service, $serviceTwo);
    }

    public function testPrepareConfigurationPasstrough() {
      $options = new CacheConfiguration();
      $this->assertSame($options, Cache::prepareConfiguration($options));
    }

    public function testPrepareConfigurationFromGlobalConfiguration() {
      $configuration = $this->mockPapaya()->options(
        [
          'PAPAYA_CACHE_SERVICE' => 'sample',
          'PAPAYA_PATH_CACHE' => '/tmp/sample',
          'PAPAYA_CACHE_NOTIFIER' => '/tmp/notify.php',
          'PAPAYA_CACHE_DISABLE_FILE_DELETE' => TRUE,
          'PAPAYA_CACHE_MEMCACHE_SERVERS' => 'sample.host'
        ]
      );
      $options = Cache::prepareConfiguration($configuration);
      $this->assertInstanceOf(CacheConfiguration::class, $options);
      $this->assertEquals(
        [
          'SERVICE' => 'sample',
          'FILESYSTEM_PATH' => '/tmp/sample',
          'FILESYSTEM_NOTIFIER_SCRIPT' => '/tmp/notify.php',
          'FILESYSTEM_DISABLE_CLEAR' => TRUE,
          'MEMCACHE_SERVERS' => 'sample.host'
        ],
        iterator_to_array($options)
      );
    }

    public function testGetForInvalidCacheExpectingFalse() {
      $this->assertFalse(
        Cache::get(-23, $this->mockPapaya()->options())
      );
    }

    /**
     * @dataProvider provideCacheIdentifiers
     * @param string $for
     */
    public function testGetCache($for) {
      $configuration = $this->mockPapaya()->options(
        [
          'PAPAYA_CACHE_SERVICE' => 'apc',
          'PAPAYA_PATH_CACHE' => '/tmp/sample',
          'PAPAYA_CACHE_NOTIFIER' => '/tmp/notify.php',
          'PAPAYA_CACHE_MEMCACHE_SERVERS' => 'sample.host',
          'PAPAYA_CACHE_DATA' => TRUE,
          'PAPAYA_CACHE_DATA_SERVICE' => 'apc',
          'PAPAYA_CACHE_DATA_MEMCACHE_SERVERS' => 'sample.host',
          'PAPAYA_CACHE_IMAGES' => TRUE,
          'PAPAYA_CACHE_IMAGES_SERVICE' => 'apc',
          'PAPAYA_CACHE_IMAGES_MEMCACHE_SERVERS' => 'sample.host'
        ]
      );
      $service = Cache::get($for, $configuration);
      $this->assertInstanceOf(
        CacheService\APC::class, $service
      );
    }

    /**
     * @dataProvider provideDisabledCacheIdentifiers
     * @param string $for
     */
    public function testGetCacheWithDisabledCachesExpectingFalse($for) {
      $configuration = $this->mockPapaya()->options(
        [
          'PAPAYA_CACHE_DATA' => FALSE,
          'PAPAYA_CACHE_IMAGES' => FALSE,
        ]
      );
      $this->assertFalse(
        Cache::get($for, $configuration)
      );
    }

    public static function provideCacheIdentifiers(): array {
      return [
        [Cache::OUTPUT],
        [Cache::DATA],
        [Cache::IMAGES]
      ];
    }

    public static function provideDisabledCacheIdentifiers(): array {
      return [
        [Cache::DATA],
        [Cache::IMAGES]
      ];
    }
  }
}
