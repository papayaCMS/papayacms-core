<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

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