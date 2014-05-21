<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaFilterIntegerTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterInteger::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterInteger();
    $this->assertAttributeSame(
      NULL, '_minimum', $filter
    );
    $this->assertAttributeSame(
      NULL, '_maximum', $filter
    );
  }

  /**
  * @covers PapayaFilterInteger::__construct
  */
  public function testConstructorwithMinimumAndMaximum() {
    $filter = new PapayaFilterInteger(21, 42);
    $this->assertAttributeSame(
      21, '_minimum', $filter
    );
    $this->assertAttributeSame(
      42, '_maximum', $filter
    );
  }

  /**
  * @covers PapayaFilterInteger::__construct
  */
  public function testConstructorWithMaximumOnlyExpectingException() {
    $this->setExpectedException('RangeException');
    $filter = new PapayaFilterInteger(NULL, 4);
  }

  /**
  * @covers PapayaFilterInteger::__construct
  */
  public function testConstructorWithMaximumToSmallExpectingException() {
    $this->setExpectedException('RangeException');
    $filter = new PapayaFilterInteger(4, 2);
  }

  /**
  * @covers PapayaFilterInteger::validate
  * @dataProvider provideValidValidateData
  */
  public function testValidateWithLimitsExpectingTrue($value, $minimum, $maximum) {
    $filter = new PapayaFilterInteger($minimum, $maximum);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterInteger::validate
  * @dataProvider provideInvalidValidateData
  */
  public function testValidateWithLimitsExpectingException($value, $minimum, $maximum) {
    $filter = new PapayaFilterInteger($minimum, $maximum);
    $this->setExpectedException('PapayaFilterException');
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterInteger::validate
  */
  public function testValidateWithoutRange() {
    $filter = new PapayaFilterInteger();
    $this->assertTrue($filter->validate(42));
  }

  /**
  * @covers PapayaFilterInteger::validate
  */
  public function testValidateWithStringExpectingException() {
    $filter = new PapayaFilterInteger();
    $this->setExpectedException('PapayaFilterException');
    $filter->validate('foo');
  }

  /**
  * @covers PapayaFilterInteger::validate
  */
  public function testValidateWithFloatExpectingException() {
    $filter = new PapayaFilterInteger();
    $this->setExpectedException('PapayaFilterException');
    $filter->validate(42.21);
  }

  /**
  * @covers PapayaFilterInteger::validate
  */
  public function testValidateWithValueToSmallExpectingException() {
    $filter = new PapayaFilterInteger(21, 42);
    $this->setExpectedException('PapayaFilterExceptionRangeMinimum');
    $filter->validate(1);
  }

  /**
  * @covers PapayaFilterInteger::validate
  */
  public function testValidateWithValueToLargeExpectingException() {
    $filter = new PapayaFilterInteger(0, 1);
    $this->setExpectedException('PapayaFilterExceptionRangeMaximum');
    $filter->validate(21);
  }

  /**
  * @covers PapayaFilterInteger::filter
  */
  public function testFilter() {
    $filter = new PapayaFilterInteger(0, 1);
    $this->assertEquals(1, $filter->filter(1));
  }

  /**
  * @covers PapayaFilterInteger::filter
  */
  public function testFilterExpectingNull() {
    $filter = new PapayaFilterInteger(1);
    $this->assertNull($filter->filter('foo'));
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidValidateData() {
    return array(
      array('23', 21, 42),
      array('42', 21, NULL),
      array('-23', -42, 42),
      array('23', -42, 42),
      array('+23', -42, 42)
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      array('10', 21, 42),
      array('42', 21, 23),
      array('-23', 0, 10),
      array('+23', -10, 0)
    );
  }
}
