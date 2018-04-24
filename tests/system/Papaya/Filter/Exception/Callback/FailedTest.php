<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionCallbackFailedTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionCallbackFailed::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionCallbackFailed('strpos');
    $this->assertEquals(
      'Callback has failed: "strpos"',
      $e->getMessage()
    );
  }
}
