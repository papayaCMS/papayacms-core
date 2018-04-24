<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiOptionAlignTest extends PapayaTestCase {

  /**
  * @covers PapayaUiOptionAlign::getString
  */
  public function testGetString() {
    $this->assertEquals(
      'center',
      PapayaUiOptionAlign::getString(PapayaUiOptionAlign::CENTER)
    );
  }

  /**
  * @covers PapayaUiOptionAlign::getString
  */
  public function testGetStringWithInvalidValueExpectingLeft() {
    $this->assertEquals(
      'left',
      PapayaUiOptionAlign::getString(-42)
    );
  }

  /**
  * @covers PapayaUiOptionAlign::validate
  */
  public function testValidate() {
    $this->assertTrue(
      PapayaUiOptionAlign::validate(PapayaUiOptionAlign::CENTER)
    );
  }

  /**
  * @covers PapayaUiOptionAlign::validate
  */
  public function testValidateWithInvalidValue() {
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Invalid align value "-42".'
    );
    PapayaUiOptionAlign::validate(-42);
  }

  /**
  * @covers PapayaUiOptionAlign::validate
  */
  public function testValidateWithInvalidValueAndIndividualMessage() {
    $this->setExpectedException(
      'InvalidArgumentException',
      'Failed.'
    );
    PapayaUiOptionAlign::validate(-42, 'Failed.');
  }
}
