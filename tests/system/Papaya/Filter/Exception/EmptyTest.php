<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

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
