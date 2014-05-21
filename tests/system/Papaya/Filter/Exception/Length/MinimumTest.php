<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaFilterExceptionLengthMinimumTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionLengthMinimum::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionLengthMinimum(42, 21);
    $this->assertEquals(
      'Value is too short. Expecting a minimum of 42 bytes, got 21.',
      $e->getMessage()
    );
  }
}
