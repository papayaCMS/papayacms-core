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

class ApcTest extends \PapayaTestCase {

  /**
   * @covers Apc::setConfiguration
   */
  public function testSetConfiguration() {
    $service = new Apc();
    $this->assertTrue($service->setConfiguration(new \Papaya\Cache\Configuration));
  }

  /**
   * @covers Apc::setApcObject
   */
  public function testSetApcObject() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertSame($apc, $this->readAttribute($service, '_apcObject'));
  }

  /**
   * @covers Apc::getApcObject
   */
  public function testGetApcObject() {
    $service = new Apc();
    $this->assertInstanceOf(Apc\Wrapper::class, $service->getApcObject());
  }

  /**
   * @covers Apc::verify
   */
  public function testVerifyExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertTrue($service->verify());
  }

  /**
   * @covers Apc::verify
   */
  public function testVerifyExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertFalse($service->verify());
  }

  /**
   * @covers Apc::verify
   */
  public function testVerifyExpectingError() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new Apc();
    $service->setApcObject($apc);
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('APC is not available');
    $service->verify(FALSE);
  }

  /**
   * @covers Apc::write
   */
  public function testWrite() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertSame(
      'GROUP/ELEMENT/PARAMETERS',
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
   * @covers Apc::write
   */
  public function testWriteExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertFalse(
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
   * @covers Apc::read
   * @covers Apc::_read
   */
  public function testRead() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertSame(
      'DATA',
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
   * @covers Apc::read
   * @covers Apc::_read
   */
  public function testReadExpired() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 60)
    );
  }

  /**
   * @covers Apc::read
   * @covers Apc::_read
   */
  public function testReadDeprecated() {
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }

  /**
   * @covers Apc::read
   */
  public function testReadExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertFalse($service->read('GROUP', 'ELEMENT', 'PARAMETERS', 1800));
  }

  /**
   * @covers Apc::exists
   */
  public function testExists() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertTrue(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
   * @covers Apc::exists
   */
  public function testExistsDeprecated() {
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertFalse(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }

  /**
   * @covers Apc::exists
   */
  public function testExistsUsingCachedResult() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $service = new \Papaya\Cache\Service\PapayaCacheServiceApc_TestProxy();
    $service->setApcObject($apc);
    $service->_localCache['GROUP/ELEMENT/PARAMETERS'] = 'DATA';
    $this->assertTrue(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
   * @covers Apc::exists
   */
  public function testExistsExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertFalse($service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 1800));
  }

  /**
   * @covers Apc::created
   */
  public function testCreated() {
    $lastHour = time() - 3600;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertEquals(
      $lastHour,
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
    );
  }

  /**
   * @covers Apc::created
   */
  public function testCreatedWithExpiredExpectingFalse() {
    $lastHour = time() - 3600;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertFalse(
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
    );
  }

  /**
   * @covers Apc::created
   */
  public function testCreatedWithCachedResult() {
    $lastHour = time() - 3600;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
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
    $service = new Apc();
    $service->setApcObject($apc);
    $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 7200);
    $this->assertEquals(
      $lastHour,
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
    );
  }

  /**
   * @covers Apc::delete
   */
  public function testDelete() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
      ->method('clearCache')
      ->with('user')
      ->will($this->returnValue(TRUE));
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertTrue($service->delete());
  }

  /**
   * @covers Apc::delete
   */
  public function testDeleteExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Apc\Wrapper $apc */
    $apc = $this->createMock(Apc\Wrapper::class);
    $apc->expects($this->once())
      ->method('available')
      ->will($this->returnValue(FALSE));
    $service = new Apc();
    $service->setApcObject($apc);
    $this->assertSame(0, $service->delete());
  }
}

class PapayaCacheServiceApc_TestProxy extends Apc {
  public $_localCache;
}
