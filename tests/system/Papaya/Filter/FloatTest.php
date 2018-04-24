<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterFloatTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterFloat::__construct
  */
  public function testConstructWithoutParams(){
    $testObj = new PapayaFilterFloat();
    $this->assertAttributeEquals(NULL, '_min', $testObj);
    $this->assertAttributeEquals(NULL, '_max', $testObj);
  }

  /**
  * @covers PapayaFilterFloat::__construct
  */
  public function testConstructWithoutFirstParam(){
    $min = -120;
    $testObj = new PapayaFilterFloat($min);
    $this->assertAttributeEquals($min, '_min', $testObj);
    $this->assertAttributeEquals(NULL, '_max', $testObj);
  }

  /**
  * @covers PapayaFilterFloat::__construct
  */
  public function testConstructWithoutWithBothParams(){
    $min = -120;
    $max = 120;
    $testObj = new PapayaFilterFloat($min, $max);
    $this->assertAttributeEquals($min, '_min', $testObj);
    $this->assertAttributeEquals($max, '_max', $testObj);
  }

  /**
  * @covers PapayaFilterFloat::validate
  */
  public function testValidate() {
    $filter = new PapayaFilterFloat();
    $this->setExpectedException("PapayaFilterExceptionNotFloat");
    $filter->validate("sgs");
  }

  /**
  * @covers PapayaFilterFloat::validate
  */
  public function testValidateWithMinumum(){
    $filter = new PapayaFilterFloat("-20.0");
    $this->setExpectedException("PapayaFilterExceptionRangeMinimum");
    $filter->validate("-40");
  }

  /**
  * @covers PapayaFilterFloat::validate
  */
  public function testValidateWithMinumumAndMaximum(){
    $filter = new PapayaFilterFloat("-20.0", "40.5");
    $this->setExpectedException("PapayaFilterExceptionRangeMaximum");
    $filter->validate("50");
  }

  /**
  * @covers PapayaFilterFloat::validate
  */
  public function testValidateTrue() {
    $filter = new PapayaFilterFloat("-20.0", "40.5");
    $this->assertTrue($filter->validate("10.51"));
  }

  /**
  * @covers PapayaFilterFloat::filter
  * @dataProvider provideValidFilterValues
  */
  public function testFilterExpectingValue($expected, $value, $minimum, $maximum) {
    $filter = new PapayaFilterFloat($minimum, $maximum);
    $this->assertEquals($expected, $filter->filter($value));
  }

  /**
  * @covers PapayaFilterFloat::filter
  * @dataProvider provideInvalidFilterValues
  */
  public function testFilterExpectingNull($value, $minimum, $maximum) {
    $filter = new PapayaFilterFloat($minimum, $maximum);
    $this->assertNull($filter->filter($value));
  }

  public static function provideValidFilterValues() {
    return array(
      array(10.51, "10.51", NULL, NULL),
      array(0, "abc", NULL, NULL),
      array(23.1, "23.10", 21, 42)
    );

  }

 public static function provideInvalidFilterValues() {
    return array(
      array("10", 11, 20),
      array("42", 11, 20)
    );
  }
}
