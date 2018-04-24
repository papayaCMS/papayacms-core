<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionNotEnclosedTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionNotEnclosed::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionNotEnclosed(42);
    $this->assertEquals(
      'Value is to not enclosed in list of valid elements. Got "42".',
      $e->getMessage()
    );
  }
}
