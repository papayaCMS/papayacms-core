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

namespace Papaya\Cache\Service;

require_once __DIR__.'/../../../../bootstrap.php';

class APCTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Cache\Service\APC::setConfiguration
   */
  public function testSetConfiguration() {
    $service = new APC();
    $this->assertTrue($service->setConfiguration(new \Papaya\Cache\Configuration));
  }

  /**
   */
  public function testSetAndGetApcObject() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertSame($apc, $service->getAPCObject());
  }

  /**
   * @covers \Papaya\Cache\Service\APC::setAPCObject
   * @covers \Papaya\Cache\Service\APC::getAPCObject
   */
  public function testGetApcObject() {
    $service = new APC();
    $this->assertInstanceOf(APC\Wrapper::class, $service->getAPCObject());
  }

  /**
   * @covers \Papaya\Cache\Service\APC::verify
   */
  public function testVerifyExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertTrue($service->verify());
  }

  /**
   * @covers \Papaya\Cache\Service\APC::verify
   */
  public function testVerifyExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertFalse($service->verify());
  }

  /**
   * @covers \Papaya\Cache\Service\APC::verify
   */
  public function testVerifyExpectingError() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new APC();
    $service->setAPCObject($apc);
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('APC is not available');
    $service->verify(FALSE);
  }

  /**
   * @covers \Papaya\Cache\Service\APC::write
   */
  public function testWrite() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('store')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS'),
        $this->isType('array'),
        $this->equalTo(30)
      )
      ->will($this->returnValue(TRUE));
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertSame(
      'GROUP/ELEMENT/PARAMETERS',
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::write
   */
  public function testWriteExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('store')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS'),
        $this->isType('array'),
        $this->equalTo(30)
      )
      ->will($this->returnValue(FALSE));
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertFalse(
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::read
   * @covers \Papaya\Cache\Service\APC::_read
   */
  public function testRead() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('fetch')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS')
      )
      ->will(
        $this->returnValue(array(time(), 'DATA'))
      );
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertSame(
      'DATA',
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::read
   * @covers \Papaya\Cache\Service\APC::_read
   */
  public function testReadExpired() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('fetch')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS')
      )
      ->will(
        $this->returnValue(array(time() - 1800, 'DATA'))
      );
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 60)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::read
   * @covers \Papaya\Cache\Service\APC::_read
   */
  public function testReadDeprecated() {
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('fetch')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS')
      )
      ->will(
        $this->returnValue(array($lastHour, 'DATA'))
      );
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::read
   */
  public function testReadExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertFalse($service->read('GROUP', 'ELEMENT', 'PARAMETERS', 1800));
  }

  /**
   * @covers \Papaya\Cache\Service\APC::exists
   */
  public function testExists() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('fetch')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS')
      )
      ->will(
        $this->returnValue(array(time(), 'DATA'))
      );
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertTrue(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::exists
   */
  public function testExistsDeprecated() {
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('fetch')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS')
      )
      ->will(
        $this->returnValue(array($lastHour, 'DATA'))
      );
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertFalse(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::exists
   */
  public function testExistsUsingCachedResult() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $service = new APC_TestProxy();
    $service->setAPCObject($apc);
    $service->_localCache['GROUP/ELEMENT/PARAMETERS'] = 'DATA';
    $this->assertTrue(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::exists
   */
  public function testExistsExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertFalse($service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 1800));
  }

  /**
   * @covers \Papaya\Cache\Service\APC::created
   */
  public function testCreated() {
    $lastHour = time() - 3600;
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('fetch')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS')
      )
      ->will(
        $this->returnValue(array($lastHour, 'DATA'))
      );
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertEquals(
      $lastHour,
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::created
   */
  public function testCreatedWithExpiredExpectingFalse() {
    $lastHour = time() - 3600;
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('fetch')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS')
      )
      ->will(
        $this->returnValue(array($lastHour, 'DATA'))
      );
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertFalse(
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::created
   */
  public function testCreatedWithCachedResult() {
    $lastHour = time() - 3600;
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->any())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('fetch')
      ->with(
        $this->equalTo('GROUP/ELEMENT/PARAMETERS')
      )
      ->will(
        $this->returnValue(array($lastHour, 'DATA'))
      );
    $service = new APC();
    $service->setAPCObject($apc);
    $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 7200);
    $this->assertEquals(
      $lastHour,
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
    );
  }

  /**
   * @covers \Papaya\Cache\Service\APC::delete
   */
  public function testDelete() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('clearCache')
      ->with('user')
      ->will($this->returnValue(TRUE));
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertTrue($service->delete());
  }

  /**
   * @covers \Papaya\Cache\Service\APC::delete
   */
  public function testDeleteExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|APC\Wrapper $apc */
    $apc = $this->createMock(APC\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new APC();
    $service->setAPCObject($apc);
    $this->assertSame(0, $service->delete());
  }
}

class APC_TestProxy extends APC {
  public $_localCache;
}
