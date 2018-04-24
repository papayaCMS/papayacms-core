<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionRangeMaximumTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionRangeMaximum::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionRangeMaximum(21, 42);
    $this->assertEquals(
      'Value is to large. Expecting a maximum of "21", got "42".',
      $e->getMessage()
    );
  }
}
