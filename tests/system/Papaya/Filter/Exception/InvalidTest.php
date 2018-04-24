<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterExceptionInvalidTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionInvalid::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionInvalid('foo');
    $this->assertEquals(
      'Invalid value "foo".',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionInvalid::getActualValue
  */
  public function testGetPattern() {
    $e = new PapayaFilterExceptionInvalid('foo');
    $this->assertEquals(
      'foo',
      $e->getActualValue()
    );
  }
}
