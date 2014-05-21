<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaFilterExceptionLengthMaximumTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionLengthMaximum::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionLengthMaximum(21, 42);
    $this->assertEquals(
      'Value is too long. Expecting a maximum of 21 bytes, got 42.',
      $e->getMessage()
    );
  }
}
