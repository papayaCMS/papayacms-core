<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterExceptionPcreTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionPcre::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionPcre('(foo)');
    $this->assertEquals(
      'Value does not match pattern "(foo)"',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionPcre::getPattern
  */
  public function testGetPattern() {
    $e = new PapayaFilterExceptionPcre('(foo)');
    $this->assertEquals(
      '(foo)',
      $e->getPattern()
    );
  }
}
