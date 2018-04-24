<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionCountMismatchTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionCountMismatch::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionCountMismatch(2, 1, 'type');
    $this->assertEquals(
      '2 element(s) of type "type" expected, 1 found.',
      $e->getMessage()
    );
  }

}
