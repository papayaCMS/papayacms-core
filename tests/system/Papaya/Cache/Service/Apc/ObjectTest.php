<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheServiceApcObjectTest extends PapayaTestCase {

  public function skipIfApcIsAvailable() {
    if (extension_loaded('apc')) {
      $this->markTestSkipped('Apc is availiable');
    }
  }

  public function skipIfApcIsNotAvailable() {
    if (!extension_loaded('apc')) {
      $this->markTestSkipped('Apc is not availiable');
    }
  }

  /**
  * @covers PapayaCacheServiceApcObject::available
  */
  public function testAvailableExpectingTrue() {
    $this->skipIfApcIsNotAvailable();
    $apc = new PapayaCacheServiceApcObject();
    $this->assertTrue($apc->available());
  }

  /**
  * @covers PapayaCacheServiceApcObject::available
  */
  public function testAvailableExpectingFalse() {
    $this->skipIfApcIsAvailable();
    $apc = new PapayaCacheServiceApcObject();
    $this->assertFalse($apc->available());
  }

  /**
  * @covers PapayaCacheServiceApcObject::store
  */
  public function testStore() {
    $this->skipIfApcIsNotAvailable();
    $apc = new PapayaCacheServiceApcObject();
    $this->assertInternalType('boolean', $apc->store('SAMPLE', 'DATA', 5));
  }


  /**
  * @covers PapayaCacheServiceApcObject::fetch
  */
  public function testFetch() {
    $this->skipIfApcIsNotAvailable();
    $apc = new PapayaCacheServiceApcObject();
    $success = FALSE;
    $apc->fetch('SAMPLE', $success);
    $this->assertInternalType('boolean', $success);
  }

  /**
  * @covers PapayaCacheServiceApcObject::clearCache
  */
  public function testClearCache() {
    $this->skipIfApcIsNotAvailable();
    $apc = new PapayaCacheServiceApcObject();
    $this->assertInternalType('boolean', $apc->clearCache('user'));
  }
}
