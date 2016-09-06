<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaCacheServiceMemcacheTest extends PapayaTestCase {

  /**
  * @covers PapayaCacheServiceMemcache::setMemcacheObject
  */
  public function testSetMemcacheObject() {
    $memcache = $this->getMock(
      'Memcached'
    );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertAttributeSame(
      $memcache, '_memcache', $service
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::getMemcacheObject
  */
  public function testGetMemcacheObject() {
    $memcache = $this->getMock(
      'Memcached'
    );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertSame($memcache, $service->getMemcacheObject());
  }

  /**
  * @covers PapayaCacheServiceMemcache::getMemcacheObject
  * @covers PapayaCacheServiceMemcache::_createMemcacheObject
  */
  public function testGetMemcacheObjectExistingClass() {
    $service = new PapayaCacheServiceMemcache_TestProxy();
    $service->_memcacheClasses = array('stdClass');
    $this->assertInstanceOf('stdClass', $service->getMemcacheObject());
  }

  /**
  * @covers PapayaCacheServiceMemcache::getMemcacheObject
  * @covers PapayaCacheServiceMemcache::_createMemcacheObject
  */
  public function testGetMemcacheObjectExistingAndFallbackClass() {
    $service = new PapayaCacheServiceMemcache_TestProxy();
    $service->_memcacheClasses = array('stdClass', 'PapayaCacheServiceMemcache');
    $this->assertInstanceOf('stdClass', $service->getMemcacheObject());
  }

  /**
  * @covers PapayaCacheServiceMemcache::getMemcacheObject
  * @covers PapayaCacheServiceMemcache::_createMemcacheObject
  */
  public function testGetMemcacheObjectNonexistingClass() {
    $service = new PapayaCacheServiceMemcache_TestProxy();
    $service->_memcacheClasses = array('NOT_EXISTING_CLASSNAME');
    $this->assertFalse($service->getMemcacheObject());
  }

  /**
  * @covers PapayaCacheServiceMemcache::getMemcacheObject
  * @covers PapayaCacheServiceMemcache::_createMemcacheObject
  */
  public function testGetMemcacheObjectNonexistingButFallbackClass() {
    $service = new PapayaCacheServiceMemcache_TestProxy();
    $service->_memcacheClasses = array('NOT_EXISTING_CLASSNAME', 'stdClass');
    $this->assertInstanceOf('stdClass', $service->getMemcacheObject());
  }

  /**
  * @covers PapayaCacheServiceMemcache::setConfiguration
  */
  public function testSetConfiguration() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] = 'TEST';
    $service = new PapayaCacheServiceMemcache();
    $service->setConfiguration($configuration);
    $this->assertSame('TEST', $this->readAttribute($service, '_cachePath'));
  }

  /**
  * @covers PapayaCacheServiceMemcache::setConfiguration
  */
  public function testSetConfigurationEmpty() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] = '';
    $service = new PapayaCacheServiceMemcache();
    $service->setConfiguration($configuration);
    $this->assertThat(
      $this->readAttribute($service, '_cachePath'),
      $this->logicalOr(
        $this->equalTo(''),
        $this->equalTo(ini_get('session.save_path'))
      )
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::verify
  */
  public function testVerifyExpectingTrue() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertTrue($service->verify());
  }

  /**
  * @covers PapayaCacheServiceMemcache::verify
  */
  public function testVerifyExpectingFalse() {
    $memcache = $this->getMemcacheMockObjectFixture(FALSE);
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertFalse($service->verify());
  }

  /**
  * @covers PapayaCacheServiceMemcache::verify
  */
  public function testVerifyExpectingError() {
    $memcache = $this->getMemcacheMockObjectFixture(FALSE);
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->setExpectedException(
      'BadMethodCallException', 'Memcache not available or invalid server.'
    );
    $service->verify(FALSE);
  }

  /**
  * @covers PapayaCacheServiceMemcache::setUp
  */
  public function testSetUpDefault() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertTrue($service->setUp());
  }

  /**
  * @covers PapayaCacheServiceMemcache::setUp
  */
  public function testSetUpWithOldMemcache() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE, 'Memcache');
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertTrue($service->setUp());
  }

  /**
  * @covers PapayaCacheServiceMemcache::setUp
  * @covers PapayaCacheServiceMemcache::_connect
  */
  public function testSetUpWithOldMemcacheAndConfiguration() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] =
      'tcp://host1:11211?persistent=1&weight=2&timeout=3&retry_interval=15';
    $memcache = $this->getMemcacheMockObjectFixture(TRUE, 'Memcached');
    $service = new PapayaCacheServiceMemcache();
    $service->setConfiguration($configuration);
    $service->setMemcacheObject($memcache);
    $this->assertTrue($service->setUp());
  }

  /**
  * @covers PapayaCacheServiceMemcache::setUp
  */
  public function testSetUpNoConnect() {
    $memcache = $this->getMemcacheMockObjectFixture(FALSE);
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertFalse($service->setUp());
  }

  /**
  * @covers PapayaCacheServiceMemcache::setUp
  * @covers PapayaCacheServiceMemcache::_connect
  */
  public function testSetUpComplex() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] =
      'tcp://host1:11211?persistent=1&weight=2&timeout=3&retry_interval=15';
    $memcache = $this->getMock(
      'Memcache',
      array(
        'addServer'
      )
    );
    $memcache
      ->expects($this->once())
      ->method('addServer')
      ->with(
        $this->equalTo('host1'),
        $this->equalTo('11211'),
        $this->equalTo('1'),
        $this->equalTo('2'),
        $this->equalTo('3'),
        $this->equalTo('15')
      )
      ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceMemcache();
    $service->setConfiguration($configuration);
    $service->setMemcacheObject($memcache);
    $this->assertTrue($service->setUp());
  }

  /**
  * @covers PapayaCacheServiceMemcache::setUp
  */
  public function testSetUpWithTwoServers() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] = 'tcp://host1:11211;tcp://host2:11211;';
    $memcache = $this->getMock(
      'Memcached',
      array(
        'addServer'
      )
    );
    $memcache
      ->expects($this->exactly(2))
      ->method('addServer')
      ->with(
        $this->logicalOr($this->equalTo('host1'), $this->equalTo('host2')),
        $this->equalTo('11211')
      )
      ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceMemcache();
    $service->setConfiguration($configuration);
    $service->setMemcacheObject($memcache);
    $this->assertTrue($service->setUp());
  }

  /**
  * @covers PapayaCacheServiceMemcache::setUp
  */
  public function testSetUpComplexWithOldMemcache() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] =
      'tcp://host1:11211?persistent=1&weight=2&timeout=3&retry_interval=15';
    $memcache = $this->getMock(
      'Memcache',
      array(
        'addServer'
      )
    );
    $memcache
      ->expects($this->once())
      ->method('addServer')
      ->with(
        $this->equalTo('host1'),
        $this->equalTo('11211')
      )
      ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceMemcache();
    $service->setConfiguration($configuration);
    $service->setMemcacheObject($memcache);
    $this->assertTrue($service->setUp());
  }

  /**
  * @covers PapayaCacheServiceMemcache::getServersConfiguration
  * @dataProvider getMemcacheConfigurationDataProvider
  */
  public function testGetServersConfiguration($serverString, $expected) {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] = $serverString;
    $service = new PapayaCacheServiceMemcache();
    $service->setConfiguration($configuration);
    $this->assertEquals(
      $expected,
      $service->getServersConfiguration()
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::write
  */
  public function testWriteExpectingFalse() {
    $memcache = $this->getMemcacheMockObjectFixture(FALSE);
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertFalse($service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30));
  }

  /**
  * @covers PapayaCacheServiceMemcache::write
  */
  public function testWriteExpectingFailure() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('replace')
      ->will($this->returnValue(FALSE));
    $memcache
      ->expects($this->once())
      ->method('set')
      ->will($this->returnValue(FALSE));
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertFalse($service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30));
  }

  /**
  * @covers PapayaCacheServiceMemcache::write
  */
  public function testWriteNewCacheData() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('replace')
      ->will($this->returnValue(FALSE));
    $memcache
      ->expects($this->once())
      ->method('set')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS'),
        $this->stringEndsWith(':DATA'),
        $this->equalTo(30)
      )
      ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertSame(
      'GROUP/ELEMENT/PARAMETERS',
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::write
  */
  public function testWriteUpdatedCacheData() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('replace')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS'),
        $this->stringEndsWith(':DATA'),
        $this->equalTo(30)
      )
      ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertSame(
      'GROUP/ELEMENT/PARAMETERS',
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::read
  * @covers PapayaCacheServiceMemcache::_read
  */
  public function testRead() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('GROUP/ELEMENT/PARAMETERS'))
      ->will(
        $this->returnValue(time().':DATA')
      );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertSame(
      'DATA',
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::read
  * @covers PapayaCacheServiceMemcache::_read
  */
  public function testReadExpired() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('GROUP/ELEMENT/PARAMETERS'))
      ->will(
        $this->returnValue((time() - 1800).':DATA')
      );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 60)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::read
  * @covers PapayaCacheServiceMemcache::_read
  */
  public function testReadDeprecated() {
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('GROUP/ELEMENT/PARAMETERS'))
      ->will(
        $this->returnValue($lastHour.':DATA')
      );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::exists
  */
  public function testExists() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('GROUP/ELEMENT/PARAMETERS'))
      ->will(
        $this->returnValue(time().':DATA')
      );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertTrue(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::exists
  */
  public function testExistsDeprecated() {
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('GROUP/ELEMENT/PARAMETERS'))
      ->will(
        $this->returnValue($lastHour.':DATA')
      );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertFalse(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::exists
  */
  public function testExistsUsingCachedResult() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $service = new PapayaCacheServiceMemcache_TestProxy();
    $service->setMemcacheObject($memcache);
    $service->_localCache['GROUP/ELEMENT/PARAMETERS'] = 'DATA';
    $this->assertTrue(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::exists
  */
  public function testExistsExpectingFalse() {
    $memcache = $this->getMemcacheMockObjectFixture(FALSE);
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertFalse($service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 1800));
  }

  /**
  * @covers PapayaCacheServiceMemcache::created
  */
  public function testCreated() {
    $lastHour = time() - 3600;
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('GROUP/ELEMENT/PARAMETERS'))
      ->will(
        $this->returnValue($lastHour.':DATA')
      );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertEquals(
      $lastHour,
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::created
  */
  public function testCreatedWithExpiredExpectingFalse() {
    $lastHour = time() - 3600;
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('GROUP/ELEMENT/PARAMETERS'))
      ->will(
        $this->returnValue($lastHour.':DATA')
      );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $this->assertFalse(
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::created
  */
  public function testCreatedWithCachedResult() {
    $lastHour = time() - 3600;
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('GROUP/ELEMENT/PARAMETERS'))
      ->will(
        $this->returnValue($lastHour.':DATA')
      );
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 7200);
    $this->assertEquals(
      $lastHour,
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
    );
  }

  /**
  * @covers PapayaCacheServiceMemcache::delete
  */
  public function testDeleteWithMultipleServers() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] = 'tcp://host1:11211';
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('flush')
      ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceMemcache_FlushTestProxy();
    $service->setConfiguration($configuration);
    $service->memcacheObjects = array(
      $memcache
    );
    $this->assertTrue($service->delete());
  }

  /**
  * @covers PapayaCacheServiceMemcache::delete
  */
  public function testDeleteWithMultipleServersOneServerReturnsFalse() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] =  'tcp://host1:11211;tcp://host2:11211';
    $memcacheOne = $this->getMemcacheMockObjectFixture(TRUE);
    $memcacheOne
      ->expects($this->once())
      ->method('flush')
      ->will($this->returnValue(TRUE));
    $memcacheTwo = $this->getMemcacheMockObjectFixture(TRUE);
    $memcacheTwo
      ->expects($this->once())
      ->method('flush')
      ->will($this->returnValue(FALSE));
    $service = new PapayaCacheServiceMemcache_FlushTestProxy();
    $service->setConfiguration($configuration);
    $service->memcacheObjects = array(
      $memcacheOne, $memcacheTwo
    );
    $this->assertSame(0, $service->delete());
  }

  /**
  * @covers PapayaCacheServiceMemcache::delete
  */
  public function testDeleteWithMultipleServersOneServerCanNotConnect() {
    $configuration = new PapayaCacheConfiguration();
    $configuration['MEMCACHE_SERVERS'] =  'tcp://host1:11211;tcp://host2:11211';
    $memcacheOne = $this->getMemcacheMockObjectFixture(TRUE);
    $memcacheOne
      ->expects($this->once())
      ->method('flush')
      ->will($this->returnValue(TRUE));
    $memcacheTwo = $this->getMemcacheMockObjectFixture(FALSE);
    $service = new PapayaCacheServiceMemcache_FlushTestProxy();
    $service->setConfiguration($configuration);
    $service->memcacheObjects = array(
      $memcacheOne, $memcacheTwo
    );
    $this->assertSame(0, $service->delete());
  }

  /**
  * @covers PapayaCacheServiceMemcache::delete
  */
  public function testDeleteWithoutConfiguration() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('flush')
      ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $service->memcacheObjects = array(
      $memcache
    );
    $this->assertTrue($service->delete());
  }

  /**
  * @covers PapayaCacheServiceMemcache::delete
  */
  public function testDeleteExpectingFalse() {
    $memcache = $this->getMemcacheMockObjectFixture(TRUE);
    $memcache
      ->expects($this->once())
      ->method('flush')
      ->will($this->returnValue(FALSE));
    $service = new PapayaCacheServiceMemcache();
    $service->setMemcacheObject($memcache);
    $service->memcacheObjects = array(
      $memcache
    );
    $this->assertSame(0, $service->delete());
  }

  /**************************************
  * Fixtures
  **************************************/

  public function getMemcacheMockObjectFixture(
    $canConnected = FALSE,
    $memcacheClassName = 'Memcached'
  ) {
    $memcacheObject = $this
      ->getMockBuilder($memcacheClassName)
      ->setMethods(
        array(
          'addServer',
          'flush',
          'get',
          'set',
          'replace'
        )
      )->getMock();
    $memcacheObject
      ->expects($this->any())
      ->method('addServer')
      ->withAnyParameters()
      ->will($this->returnValue($canConnected));
    return $memcacheObject;
  }

  /**************************************
  * Data Provider
  **************************************/

  public static function getMemcacheConfigurationDataProvider() {
    return array(
      array(
        'tcp://host1:11211?persistent=1&weight=2&timeout=3&retry_interval=15',
        array(
          array(
            'host' => 'host1',
            'port' => '11211',
            'persistent' => '1',
            'weight' => '2',
            'timeout' => '3',
            'retry_interval' => '15'
          )
        )
      ),
      array(
        'tcp://localhost',
        array(
          array(
            'host' => 'localhost',
            'port' => '',
            'persistent' => '',
            'weight' => '',
            'timeout' => '',
            'retry_interval' => ''
          )
        )
      ),
      array(
        'tcp://host1:123, tcp://host2:234',
        array(
          array(
            'host' => 'host1',
            'port' => '123',
            'persistent' => '',
            'weight' => '',
            'timeout' => '',
            'retry_interval' => ''
          ),
          array(
            'host' => 'host2',
            'port' => '234',
            'persistent' => '',
            'weight' => '',
            'timeout' => '',
            'retry_interval' => ''
          )
        )
      ),
      array(
        'tcp://stage0-memcache1:11211?persistent=0',
        array(
          array(
            'host' => 'stage0-memcache1',
            'port' => '11211',
            'persistent' => '0',
            'weight' => NULL,
            'timeout' => NULL,
            'retry_interval' => NULL
          )
        )
      ),
      array(
        'tcp://127.0.0.1:11212',
        array(
          array(
            'host' => '127.0.0.1',
            'port' => '11212',
            'persistent' => NULL,
            'weight' => NULL,
            'timeout' => NULL,
            'retry_interval' => NULL
          )
        )
      ),
      array(
        'tcp://memcache.local:11212',
        array(
          array(
            'host' => 'memcache.local',
            'port' => '11212',
            'persistent' => NULL,
            'weight' => NULL,
            'timeout' => NULL,
            'retry_interval' => NULL
          )
        )
      )
    );
  }
}

class PapayaCacheServiceMemcache_TestProxy extends PapayaCacheServiceMemcache {
  public $_memcacheClasses;
  public $_localCache;
}

class PapayaCacheServiceMemcache_FlushTestProxy extends PapayaCacheServiceMemcache {

  public $memcacheObjects;
  public $memcacheObjectCounter = 0;

  public function _createMemcacheObject() {
    return $this->memcacheObjects[$this->memcacheObjectCounter++];
  }
}

if (!class_exists('Memcached', FALSE)) {

  class Memcached {
    function addServer() {}
    function flush() {}
    function get() {}
    function set() {}
    function replace() {}
  }
}


