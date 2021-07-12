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

namespace Papaya\Cache\Service {

  use PHPUnit\Runner\Version as PHPUnitVersion;

  require_once __DIR__.'/../../../../bootstrap.php';
  require_once __DIR__.'/TestData/MemcacheClasses.php';

  class MemcacheTest extends \Papaya\TestFramework\TestCase {

    public function setUp(): void {
      if (
        PHP_VERSION_ID >= 80000 &&
        version_compare(PHPUnitVersion::id(), '9.0.0', '<')
       ) {
        $this->markTestSkipped("PHPUnit <= 8 doesn't support mocking memcache(d).");
      }
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache
     */
    public function testGetMemcacheObject() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Memcached $memcache */
      $memcache = $this->createMock(\Memcached::class);
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertSame($memcache, $service->getMemcacheObject());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::getMemcacheObject
     * @covers \Papaya\Cache\Service\Memcache::_createMemcacheObject
     */
    public function testGetMemcacheObjectExistingClass() {
      $service = new Memcache_TestProxy();
      $service->_memcacheClasses = array(\stdClass::class);
      $this->assertInstanceOf(\stdClass::class, $service->getMemcacheObject());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::getMemcacheObject
     * @covers \Papaya\Cache\Service\Memcache::_createMemcacheObject
     */
    public function testGetMemcacheObjectExistingAndFallbackClass() {
      $service = new Memcache_TestProxy();
      $service->_memcacheClasses = array(\stdClass::class, \Memcache::class);
      $this->assertInstanceOf(\stdClass::class, $service->getMemcacheObject());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::getMemcacheObject
     * @covers \Papaya\Cache\Service\Memcache::_createMemcacheObject
     */
    public function testGetMemcacheObjectNonexistingClass() {
      $service = new Memcache_TestProxy();
      $service->_memcacheClasses = array('NOT_EXISTING_CLASSNAME');
      $this->assertFalse($service->getMemcacheObject());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::getMemcacheObject
     * @covers \Papaya\Cache\Service\Memcache::_createMemcacheObject
     */
    public function testGetMemcacheObjectNonexistingButFallbackClass() {
      $service = new Memcache_TestProxy();
      $service->_memcacheClasses = array('NOT_EXISTING_CLASSNAME', \stdClass::class);
      $this->assertInstanceOf(\stdClass::class, $service->getMemcacheObject());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::setConfiguration
     */
    public function testSetConfiguration() {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] = 'TEST';
      $service = new Memcache();
      $service->setConfiguration($configuration);
      $this->assertSame('TEST', $service->getCachePath());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::setConfiguration
     */
    public function testSetConfigurationEmpty() {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] = '';
      $service = new Memcache();
      $service->setConfiguration($configuration);
      $this->assertThat(
        $service->getCachePath(),
        $this->logicalOr(
          $this->equalTo(''),
          $this->equalTo(ini_get('session.save_path'))
        )
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::verify
     */
    public function testVerifyExpectingTrue() {
      $memcache = $this->getMemcacheMockObjectFixture(TRUE);
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertTrue($service->verify());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::verify
     */
    public function testVerifyExpectingFalse() {
      $memcache = $this->getMemcacheMockObjectFixture(FALSE);
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertFalse($service->verify());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::verify
     */
    public function testVerifyExpectingError() {
      $memcache = $this->getMemcacheMockObjectFixture(FALSE);
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->expectException(\BadMethodCallException::class);
      $this->expectExceptionMessage('Memcache not available or invalid server.');
      $service->verify(FALSE);
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::setUp
     */
    public function testSetUpDefault() {
      $memcache = $this->getMemcacheMockObjectFixture(TRUE);
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertTrue($service->setUp());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::setUp
     */
    public function testSetUpWithOldMemcache() {
      $memcache = $this->getMemcacheMockObjectFixture(TRUE, 'Memcache');
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertTrue($service->setUp());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::setUp
     * @covers \Papaya\Cache\Service\Memcache::_connect
     */
    public function testSetUpWithOldMemcacheAndConfiguration() {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] =
        'tcp://host1:11211?persistent=1&weight=2&timeout=3&retry_interval=15';
      $memcache = $this->getMemcacheMockObjectFixture(TRUE, 'Memcached');
      $service = new Memcache();
      $service->setConfiguration($configuration);
      $service->setMemcacheObject($memcache);
      $this->assertTrue($service->setUp());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::setUp
     */
    public function testSetUpNoConnect() {
      $memcache = $this->getMemcacheMockObjectFixture(FALSE);
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertFalse($service->setUp());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::setUp
     * @covers \Papaya\Cache\Service\Memcache::_connect
     */
    public function testSetUpComplex() {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] =
        'tcp://host1:11211?persistent=1&weight=2&timeout=3&retry_interval=15';
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Memcache $memcache */
      $memcache = $this->createMock(\Memcache::class);
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
      $service = new Memcache();
      $service->setConfiguration($configuration);
      $service->setMemcacheObject($memcache);
      $this->assertTrue($service->setUp());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::setUp
     */
    public function testSetUpWithTwoServers() {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] = 'tcp://host1:11211;tcp://host2:11211;';
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Memcache $memcache */
      $memcache = $this->createMock(\Memcache::class);
      $memcache
        ->expects($this->exactly(2))
        ->method('addServer')
        ->with(
          $this->logicalOr($this->equalTo('host1'), $this->equalTo('host2')),
          $this->equalTo('11211')
        )
        ->will($this->returnValue(TRUE));
      $service = new Memcache();
      $service->setConfiguration($configuration);
      $service->setMemcacheObject($memcache);
      $this->assertTrue($service->setUp());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::setUp
     */
    public function testSetUpComplexWithOldMemcache() {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] =
        'tcp://host1:11211?persistent=1&weight=2&timeout=3&retry_interval=15';
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Memcache $memcache */
      $memcache = $this->createMock(\Memcache::class);
      $memcache
        ->expects($this->once())
        ->method('addServer')
        ->with(
          $this->equalTo('host1'),
          $this->equalTo('11211')
        )
        ->will($this->returnValue(TRUE));
      $service = new Memcache();
      $service->setConfiguration($configuration);
      $service->setMemcacheObject($memcache);
      $this->assertTrue($service->setUp());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::getServersConfiguration
     * @dataProvider getMemcacheConfigurationDataProvider
     * @param string $serverString
     * @param array $expected
     */
    public function testGetServersConfiguration($serverString, array $expected) {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] = $serverString;
      $service = new Memcache();
      $service->setConfiguration($configuration);
      $this->assertEquals(
        $expected,
        $service->getServersConfiguration()
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::write
     */
    public function testWriteExpectingFalse() {
      $memcache = $this->getMemcacheMockObjectFixture(FALSE);
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertFalse($service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30));
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::write
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertFalse($service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30));
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::write
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertSame(
        'GROUP/ELEMENT/PARAMETERS',
        $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::write
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertSame(
        'GROUP/ELEMENT/PARAMETERS',
        $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::read
     * @covers \Papaya\Cache\Service\Memcache::_read
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertSame(
        'DATA',
        $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::read
     * @covers \Papaya\Cache\Service\Memcache::_read
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertFalse(
        $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 60)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::read
     * @covers \Papaya\Cache\Service\Memcache::_read
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertFalse(
        $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::exists
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertTrue(
        $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::exists
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertFalse(
        $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::exists
     */
    public function testExistsUsingCachedResult() {
      $memcache = $this->getMemcacheMockObjectFixture(TRUE);
      $service = new Memcache_TestProxy();
      $service->setMemcacheObject($memcache);
      $service->_localCache['GROUP/ELEMENT/PARAMETERS'] = 'DATA';
      $this->assertTrue(
        $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::exists
     */
    public function testExistsExpectingFalse() {
      $memcache = $this->getMemcacheMockObjectFixture(FALSE);
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertFalse($service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 1800));
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::created
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertEquals(
        $lastHour,
        $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::created
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertFalse(
        $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::created
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
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 7200);
      $this->assertEquals(
        $lastHour,
        $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
      );
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::delete
     */
    public function testDeleteWithMultipleServers() {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] = 'tcp://host1:11211';
      $memcache = $this->getMemcacheMockObjectFixture(TRUE);
      $memcache
        ->expects($this->once())
        ->method('flush')
        ->will($this->returnValue(TRUE));
      $service = new Memcache_FlushTestProxy();
      $service->setConfiguration($configuration);
      $service->memcacheObjects = array(
        $memcache
      );
      $this->assertTrue($service->delete());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::delete
     */
    public function testDeleteWithMultipleServersOneServerReturnsFalse() {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] = 'tcp://host1:11211;tcp://host2:11211';
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
      $service = new Memcache_FlushTestProxy();
      $service->setConfiguration($configuration);
      $service->memcacheObjects = array(
        $memcacheOne, $memcacheTwo
      );
      $this->assertSame(0, $service->delete());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::delete
     */
    public function testDeleteWithMultipleServersOneServerCanNotConnect() {
      $configuration = new \Papaya\Cache\Configuration();
      $configuration['MEMCACHE_SERVERS'] = 'tcp://host1:11211;tcp://host2:11211';
      $memcacheOne = $this->getMemcacheMockObjectFixture(TRUE);
      $memcacheOne
        ->expects($this->once())
        ->method('flush')
        ->will($this->returnValue(TRUE));
      $memcacheTwo = $this->getMemcacheMockObjectFixture(FALSE);
      $service = new Memcache_FlushTestProxy();
      $service->setConfiguration($configuration);
      $service->memcacheObjects = array(
        $memcacheOne, $memcacheTwo
      );
      $this->assertSame(0, $service->delete());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::delete
     */
    public function testDeleteWithoutConfiguration() {
      $memcache = $this->getMemcacheMockObjectFixture(TRUE);
      $memcache
        ->expects($this->once())
        ->method('flush')
        ->will($this->returnValue(TRUE));
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertTrue($service->delete());
    }

    /**
     * @covers \Papaya\Cache\Service\Memcache::delete
     */
    public function testDeleteExpectingFalse() {
      $memcache = $this->getMemcacheMockObjectFixture(TRUE);
      $memcache
        ->expects($this->once())
        ->method('flush')
        ->will($this->returnValue(FALSE));
      $service = new Memcache();
      $service->setMemcacheObject($memcache);
      $this->assertSame(0, $service->delete());
    }

    /**************************************
     * Fixtures
     **************************************/

    /**
     * @param bool $canConnected
     * @param string $memcacheClassName
     * @return \PHPUnit_Framework_MockObject_MockObject|\Memcached
     */
    public function getMemcacheMockObjectFixture(
      $canConnected = FALSE,
      $memcacheClassName = \Memcached::class
    ) {
      $memcacheObject = $this
        ->getMockBuilder($memcacheClassName)
        ->getMock();
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

  class Memcache_TestProxy extends Memcache {
    public $_memcacheClasses;
    public $_localCache;
  }


  class Memcache_FlushTestProxy extends Memcache {

    public $memcacheObjects;
    public $memcacheObjectCounter = 0;

    public function _createMemcacheObject() {
      return $this->memcacheObjects[$this->memcacheObjectCounter++];
    }
  }
}

