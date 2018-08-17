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

require_once __DIR__.'/../../bootstrap.php';

class PapayaCacheTest extends \Papaya\TestCase {

  public function tearDown() {
    Cache::reset();
  }

  /**
  * @covers Cache::getService
  */
  public function testGetServiceDefault() {
    $configuration = $this->mockPapaya()->options();
    $service = Cache::getService($configuration);
    $this->assertInstanceOf(Cache\Service::class, $service);
    $serviceTwo = Cache::getService($configuration);
    $this->assertSame($service, $serviceTwo);
  }

  /**
  * @covers Cache::getService
  */
  public function testGetServiceInvalid() {
    $options = new Cache\Configuration();
    $options['SERVICE'] = 'InvalidName';
    $this->expectException(\UnexpectedValueException::class);
    Cache::getService($options, FALSE);
  }

  /**
  * @covers Cache::getService
  */
  public function testGetServiceEmpty() {
    $options = new Cache\Configuration();
    $options['SERVICE'] = '';
    $this->expectException(\UnexpectedValueException::class);
    Cache::getService($options, FALSE);
  }

  /**
  * @covers Cache::getService
  */
  public function testGetServiceStaticExpectingSameObject() {
    $configuration = $this->mockPapaya()->options();
    $service = Cache::getService($configuration);
    $this->assertInstanceOf(Cache\Service\File::class, $service);
    $serviceTwo = Cache::getService($configuration);
    $this->assertSame($service, $serviceTwo);
  }

  /**
  * @covers Cache::getService
  */
  public function testGetServiceNonStaticExpectingDifferentObjects() {
    $configuration = $this->mockPapaya()->options();
    $service = Cache::getService($configuration, FALSE);
    $this->assertInstanceOf(Cache\Service\File::class, $service);
    $serviceTwo = Cache::getService($configuration, FALSE);
    $this->assertNotSame($service, $serviceTwo);
  }

  /**
  * @covers Cache::prepareConfiguration
  */
  public function testPrepareConfigurationPasstrough() {
    $options = new Cache\Configuration();
    $this->assertSame($options, Cache::prepareConfiguration($options));
  }

  /**
  * @covers Cache::prepareConfiguration
  */
  public function testPrepareConfigurationFromGlobalConfiguration() {
    $configuration = $this->mockPapaya()->options(
      array(
        'PAPAYA_CACHE_SERVICE' => 'sample',
        'PAPAYA_PATH_CACHE' => '/tmp/sample',
        'PAPAYA_CACHE_NOTIFIER' => '/tmp/notify.php',
        'PAPAYA_CACHE_DISABLE_FILE_DELETE' => TRUE,
        'PAPAYA_CACHE_MEMCACHE_SERVERS' => 'sample.host'
      )
    );
    $options = Cache::prepareConfiguration($configuration);
    $this->assertInstanceOf(Cache\Configuration::class, $options);
    $this->assertEquals(
      array(
        'SERVICE' => 'sample',
        'FILESYSTEM_PATH' => '/tmp/sample',
        'FILESYSTEM_NOTIFIER_SCRIPT' => '/tmp/notify.php',
        'FILESYSTEM_DISABLE_CLEAR' => TRUE,
        'MEMCACHE_SERVERS' => 'sample.host'
      ),
      iterator_to_array($options)
    );
  }

  /**
  * @covers Cache::get
  */
  public function testGetForInvalidCacheExpectingFalse() {
    $this->assertFalse(
      Cache::get(-23, $this->mockPapaya()->options())
    );
  }

  /**
   * @covers Cache::get
   * @dataProvider provideCacheIdentifiers
   * @param string $for
   */
  public function testGetCache($for) {
    $configuration = $this->mockPapaya()->options(
      array(
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
      )
    );
    $service = Cache::get($for, $configuration);
    $this->assertInstanceOf(
      Cache\Service\Apc::class, $service
    );
  }

  /**
   * @covers Cache::get
   * @dataProvider provideDisabledCacheIdentifiers
   * @param string $for
   */
  public function testGetCacheWithDisabledCachesExpectingFalse($for) {
    $configuration = $this->mockPapaya()->options(
      array(
        'PAPAYA_CACHE_DATA' => FALSE,
        'PAPAYA_CACHE_IMAGES' => FALSE,
      )
    );
    $this->assertFalse(
      Cache::get($for, $configuration)
    );
  }

  /**
  * @covers Cache::reset
  */
  public function testReset() {
    $configuration = $this->mockPapaya()->options();
    Cache::getService($configuration);
    Cache::reset();
    $this->assertAttributeEquals(
      array(), '_serviceObjects', Cache::class
    );
  }

  public static function provideCacheIdentifiers() {
    return array(
      array(Cache::OUTPUT),
      array(Cache::DATA),
      array(Cache::IMAGES)
    );
  }

  public static function provideDisabledCacheIdentifiers() {
    return array(
      array(Cache::DATA),
      array(Cache::IMAGES)
    );
  }
}
