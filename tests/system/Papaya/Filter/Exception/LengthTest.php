<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterExceptionLengthTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionLength::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionLength_TestProxy('Length Error', 42, 21);
    $this->assertEquals(
      'Length Error',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionLength::getExpectedLength
  */
  public function testGetExpectedLength() {
    $e = new PapayaFilterExceptionLength_TestProxy('Length Error', 42, 21);
    $this->assertEquals(
      42,
      $e->getExpectedLength()
    );
  }

  /**
  * @covers PapayaFilterExceptionLength::getActualLength
  */
  public function testgetActualLength() {
    $e = new PapayaFilterExceptionLength_TestProxy('Length Error', 42, 21);
    $this->assertEquals(
      21,
      $e->getActualLength()
    );
  }
}

class PapayaFilterExceptionLength_TestProxy extends PapayaFilterExceptionLength {

}
