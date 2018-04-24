<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterExceptionEmptyTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionEmpty::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionEmpty();
    $this->assertEquals(
      'Value is empty.',
      $e->getMessage()
    );
  }
}
