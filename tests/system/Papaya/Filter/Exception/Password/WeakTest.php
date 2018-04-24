<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionPasswordWeakTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionPasswordWeak::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionPasswordWeak();
    $this->assertEquals(
      'Password is to weak.',
      $e->getMessage()
    );
  }
}
