<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilStringGuidTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringGuid::validate
  */
  public function testValidateExpectingTrue() {
    $this->assertTrue(
      PapayaUtilStringGuid::validate('aB123456789012345678901234567890')
    );
  }

  /**
  * @covers PapayaUtilStringGuid::validate
  */
  public function testValidateExpectingFalse() {
    $this->assertFalse(
      PapayaUtilStringGuid::validate('invalid', TRUE)
    );
  }

  /**
  * @covers PapayaUtilStringGuid::validate
  */
  public function testValidateExpectingException() {
    $this->setExpectedException(
      'UnexpectedValueException', 'Invalid guid: "invalid".'
    );
    PapayaUtilStringGuid::validate('invalid');
  }

  /**
  * @covers PapayaUtilStringGuid::toLower
  */
  public function testToLower() {
    $this->assertEquals(
      'ab123456789012345678901234567890',
      PapayaUtilStringGuid::toLower('aB123456789012345678901234567890')
    );
  }

  /**
  * @covers PapayaUtilStringGuid::toLower
  */
  public function testToLowerWithInvalidValueSilentExpectingEmptyString() {
    $this->assertEquals(
      '', PapayaUtilStringGuid::toLower('invalid', TRUE)
    );
  }

  /**
  * @covers PapayaUtilStringGuid::toUpper
  */
  public function testToUpper() {
    $this->assertEquals(
      'AB123456789012345678901234567890',
      PapayaUtilStringGuid::toUpper('aB123456789012345678901234567890')
    );
  }

  /**
  * @covers PapayaUtilStringGuid::toUpper
  */
  public function testToUpperWithInvalidValueSilentExpectingEmptyString() {
    $this->assertEquals(
      '', PapayaUtilStringGuid::toUpper('invalid', TRUE)
    );
  }
}
