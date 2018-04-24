<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionNotFloatTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionNotFloat::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionNotFloat('abc');
    $this->assertEquals(
      'Value is not a float: abc',
      $e->getMessage()
    );
  }
}
