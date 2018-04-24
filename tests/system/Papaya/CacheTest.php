<?php
require_once __DIR__.'/../../bootstrap.php';

class PapayaCacheTest extends PapayaTestCase {

  public function tearDown() {
    PapayaCache::reset();
  }

  /**
  * @covers PapayaCache::getService
  */
  public function testGetServiceDefault() {
    $configuration = $this->mockPapaya()->options();
    $service = PapayaCache::getService($configuration);
    $this->assertInstanceOf('PapayaCacheService', $service);
    $serviceTwo = PapayaCache::getService($configuration);
    $this->assertSame($service, $serviceTwo);
  }

  /**
  * @covers PapayaCache::getService
  */
  public function testGetServiceInvalid() {
    $options = new PapayaCacheConfiguration();
    $options['SERVICE'] = 'InvalidName';
    $this->setExpectedException('UnexpectedValueException');
    $service = PapayaCache::getService($options, FALSE);
  }

  /**
  * @covers PapayaCache::getService
  */
  public function testGetServiceEmpty() {
    $options = new PapayaCacheConfiguration();
    $options['SERVICE'] = '';
    $this->setExpectedException('UnexpectedValueException');
    $service = PapayaCache::getService($options, FALSE);
  }

  /**
  * @covers PapayaCache::getService
  */
  public function testGetServiceStaticExpectingSameObject() {
    $configuration = $this->mockPapaya()->options();
    $service = PapayaCache::getService($configuration);
    $this->assertInstanceOf('PapayaCacheServiceFile', $service);
    $serviceTwo = PapayaCache::getService($configuration);
    $this->assertSame($service, $serviceTwo);
  }

  /**
  * @covers PapayaCache::getService
  */
  public function testGetServiceNonStaticExpectingDifferentObjects() {
    $configuration = $this->mockPapaya()->options();
    $service = PapayaCache::getService($configuration, FALSE);
    $this->assertInstanceOf('PapayaCacheServiceFile', $service);
    $serviceTwo = PapayaCache::getService($configuration, FALSE);
    $this->assertNotSame($service, $serviceTwo);
  }

  /**
  * @covers PapayaCache::prepareConfiguration
  */
  public function testPrepareConfigurationPasstrough() {
    $options = new PapayaCacheConfiguration();
    $this->assertSame($options, PapayaCache::prepareConfiguration($options));
  }

  /**
  * @covers PapayaCache::prepareConfiguration
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
    $options = PapayaCache::prepareConfiguration($configuration);
    $this->assertInstanceOf('PapayaCacheConfiguration', $options);
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
  * @covers PapayaCache::get
  */
  public function testGetForInvalidCacheExpectingFalse() {
    $this->assertFalse(
      PapayaCache::get(-23, $this->mockPapaya()->options())
    );
  }

  /**
  * @covers PapayaCache::get
  * @dataProvider provideCacheIdentifiers
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
    $service = PapayaCache::get($for, $configuration);
    $this->assertInstanceOf(
      'PapayaCacheServiceApc', $service
    );
  }

  /**
  * @covers PapayaCache::get
  * @dataProvider provideDisabledCacheIdentifiers
  */
  public function testGetCacheWithDisabledCachesExpectingFalse($for) {
    $configuration = $this->mockPapaya()->options(
      array(
        'PAPAYA_CACHE_DATA' => FALSE,
        'PAPAYA_CACHE_IMAGES' => FALSE,
      )
    );
    $this->assertFalse(
      PapayaCache::get($for, $configuration)
    );
  }

  /**
  * @covers PapayaCache::reset
  */
  public function testReset() {
    $configuration = $this->mockPapaya()->options();
    PapayaCache::getService($configuration);
    PapayaCache::reset();
    $this->assertAttributeEquals(
      array(), '_serviceObjects', 'PapayaCache'
    );
  }

  public static function provideCacheIdentifiers() {
    return array(
      array(PapayaCache::OUTPUT),
      array(PapayaCache::DATA),
      array(PapayaCache::IMAGES)
    );
  }

  public static function provideDisabledCacheIdentifiers() {
    return array(
      array(PapayaCache::DATA),
      array(PapayaCache::IMAGES)
    );
  }
}
