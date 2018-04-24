<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterExceptionRangeTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionRange::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionRange_TestProxy('Range Error', 42, 21);
    $this->assertEquals(
      'Range Error',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionRange::getExpectedLimit
  */
  public function testGetExpectedLimit() {
    $e = new PapayaFilterExceptionRange_TestProxy('Range Error', 42, 21);
    $this->assertEquals(
      42,
      $e->getExpectedLimit()
    );
  }

  /**
  * @covers PapayaFilterExceptionRange::getActualValue
  */
  public function testgetActualValue() {
    $e = new PapayaFilterExceptionRange_TestProxy('Range Error', 42, 21);
    $this->assertEquals(
      21,
      $e->getActualValue()
    );
  }
}

class PapayaFilterExceptionRange_TestProxy extends PapayaFilterExceptionRange {

}
