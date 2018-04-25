<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaCacheServiceApcTest extends PapayaTestCase {

  /**
  * @covers PapayaCacheServiceApc::setConfiguration
  */
  public function testSetConfiguration() {
    $service = new PapayaCacheServiceApc();
    $this->assertTrue($service->setConfiguration(new PapayaCacheConfiguration));
  }

  /**
  * @covers PapayaCacheServiceApc::setApcObject
  */
  public function testSetApcObject() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertSame($apc, $this->readAttribute($service, '_apcObject'));
  }

  /**
  * @covers PapayaCacheServiceApc::getApcObject
  */
  public function testGetApcObject() {
    $service = new PapayaCacheServiceApc();
    $this->assertInstanceOf(PapayaCacheServiceApcObject::class, $service->getApcObject());
  }

  /**
  * @covers PapayaCacheServiceApc::verify
  */
  public function testVerifyExpectingTrue() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
    $apc->expects($this->once())
        ->method('available')
        ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertTrue($service->verify());
  }

  /**
  * @covers PapayaCacheServiceApc::verify
  */
  public function testVerifyExpectingFalse() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
    $apc->expects($this->once())
        ->method('available')
        ->will($this->returnValue(FALSE));
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertFalse($service->verify());
  }

  /**
  * @covers PapayaCacheServiceApc::verify
  */
  public function testVerifyExpectingError() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
    $apc->expects($this->once())
        ->method('available')
        ->will($this->returnValue(FALSE));
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('APC is not available');
    $service->verify(FALSE);
  }

  /**
  * @covers PapayaCacheServiceApc::write
  */
  public function testWrite() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertSame(
      'GROUP/ELEMENT/PARAMETERS',
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::write
  */
  public function testWriteExpectingFalse() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertSame(
      FALSE,
      $service->write('GROUP', 'ELEMENT', 'PARAMETERS', 'DATA', 30)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::read
  * @covers PapayaCacheServiceApc::_read
  */
  public function testRead() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertSame(
      'DATA',
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::read
  * @covers PapayaCacheServiceApc::_read
  */
  public function testReadExpired() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 60)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::read
  * @covers PapayaCacheServiceApc::_read
  */
  public function testReadDeprecated() {
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertFalse(
      $service->read('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::read
  */
  public function testReadExpectingFalse() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
    $apc->expects($this->once())
        ->method('available')
        ->will($this->returnValue(FALSE));
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertFalse($service->read('GROUP', 'ELEMENT', 'PARAMETERS', 1800));
  }

  /**
  * @covers PapayaCacheServiceApc::exists
  */
  public function testExists() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertTrue(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::exists
  */
  public function testExistsDeprecated() {
    $lastHour = time() - 3600;
    $threeMinutesAgo = time() - 180;
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertFalse(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400, $threeMinutesAgo)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::exists
  */
  public function testExistsUsingCachedResult() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
    $apc->expects($this->once())
        ->method('available')
        ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceApc_TestProxy();
    $service->setApcObject($apc);
    $service->_localCache['GROUP/ELEMENT/PARAMETERS'] = 'DATA';
    $this->assertTrue(
      $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 86400)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::exists
  */
  public function testExistsExpectingFalse() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
    $apc->expects($this->once())
        ->method('available')
        ->will($this->returnValue(FALSE));
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertFalse($service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 1800));
  }

  /**
  * @covers PapayaCacheServiceApc::created
  */
  public function testCreated() {
    $lastHour = time() - 3600;
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertEquals(
      $lastHour,
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::created
  */
  public function testCreatedWithExpiredExpectingFalse() {
    $lastHour = time() - 3600;
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertFalse(
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 1800)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::created
  */
  public function testCreatedWithCachedResult() {
    $lastHour = time() - 3600;
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
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
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $service->exists('GROUP', 'ELEMENT', 'PARAMETERS', 7200);
    $this->assertEquals(
      $lastHour,
      $service->created('GROUP', 'ELEMENT', 'PARAMETERS', 7200)
    );
  }

  /**
  * @covers PapayaCacheServiceApc::delete
  */
  public function testDelete() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
    $apc->expects($this->once())
        ->method('available')
        ->will($this->returnValue(TRUE));
    $apc->expects($this->once())
        ->method('clearCache')
        ->with('user')
        ->will($this->returnValue(TRUE));
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertTrue($service->delete());
  }

  /**
  * @covers PapayaCacheServiceApc::delete
  */
  public function testDeleteExpectingFalse() {
    $apc = $this->createMock(PapayaCacheServiceApcObject::class);
    $apc->expects($this->once())
        ->method('available')
        ->will($this->returnValue(FALSE));
    $service = new PapayaCacheServiceApc();
    $service->setApcObject($apc);
    $this->assertSame(0, $service->delete());
  }
}

class PapayaCacheServiceApc_TestProxy extends PapayaCacheServiceApc {
  public $_localCache;
}
