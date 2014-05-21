<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterExceptionTypeTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionType::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionType('integer number');
    $this->assertEquals(
      'Value is not a "integer number".',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionType::getExpectedType
  */
  public function testGetExpectedType() {
    $e = new PapayaFilterExceptionType('integer number');
    $this->assertEquals(
      'integer number',
      $e->getExpectedType()
    );
  }
}
