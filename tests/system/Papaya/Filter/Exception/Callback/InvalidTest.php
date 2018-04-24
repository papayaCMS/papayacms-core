<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionCallbackInvalidTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionCallbackInvalid::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionCallbackInvalid('strpos');
    $this->assertEquals(
      'Invalid callback specified: "strpos"',
      $e->getMessage()
    );
  }
}
