<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionRangeMinimumTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionRangeMinimum::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionRangeMinimum(42, 21);
    $this->assertEquals(
      'Value is to small. Expecting a minimum of "42", got "21".',
      $e->getMessage()
    );
  }
}
