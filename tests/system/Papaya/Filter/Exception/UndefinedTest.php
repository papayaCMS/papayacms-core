<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterExceptionUndefinedTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionUndefined::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionUndefined();
    $this->assertEquals(
      'Value does not exist.',
      $e->getMessage()
    );
  }
}
