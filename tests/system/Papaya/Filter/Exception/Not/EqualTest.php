<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionNotEqualTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionNotEqual::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionNotEqual('42');
    $this->assertEquals(
      'Value does not equal comparsion value. Expected "42".',
      $e->getMessage()
    );
  }
}
