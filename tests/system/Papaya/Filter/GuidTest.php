<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterGuidTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterGuid
  */
  public function testValidateExpectingTrue() {
    $filter = new PapayaFilterGuid();
    $this->assertTrue(
      $filter->validate('123456789012345678901234567890ab')
    );
  }

  /**
  * @covers PapayaFilterGuid
  */
  public function testValidateExpectingException() {
    $filter = new PapayaFilterGuid();
    $this->setExpectedException('PapayaFilterException');
    $filter->validate('foo');
  }
}
