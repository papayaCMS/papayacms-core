<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaFilterTimeTest extends PapayaTestCase {
  /**
  * @covers PapayaFilterTime::__construct
  */
  public function testConstructSuccess() {
    $filter = new PapayaFilterTime(600.0);
    $this->assertAttributeEquals(600.0, '_step', $filter);
  }

  /**
  * @covers PapayaFilterTime::__construct
  */
  public function testConstructFailure() {
    try {
      $filter = new PapayaFilterTime(-1);
    } catch(UnexpectedValueException $e) {
      $this->assertInstanceOf('UnexpectedValueException', $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterTime::validate
  * @dataProvider validateSuccessProvider
  */
  public function testValidateSuccess($timeString) {
    $filter = new PapayaFilterTime(1);
    $this->assertTrue($filter->validate($timeString));
  }

  /**
  * @covers PapayaFilterTime::validate
  * @dataProvider validateExceptionTypeProvider
  */
  public function testValidateExceptionType($timeString) {
    $filter = new PapayaFilterTime();
    try {
      $filter->validate($timeString);
    } catch(PapayaFilterExceptionType $e) {
      $this->assertInstanceOf('PapayaFilterExceptionType', $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterTime::validate
  * @dataProvider validateExceptionRangeMaximumProvider
  */
  public function testValidateExceptionRangeMaximum($timeString) {
    $filter = new PapayaFilterTime();
    try {
      $filter->validate($timeString);
    } catch(PapayaFilterExceptionRangeMaximum $e) {
      $this->assertInstanceOf('PapayaFilterExceptionRangeMaximum', $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterTime::validate
  */
  public function testValidateExceptionTypeForStepMismatch() {
    $filter = new PapayaFilterTime(1800);
    try {
      $filter->validate('17:45');
    } catch(PapayaFilterExceptionType $e) {
      $this->assertInstanceOf('PapayaFilterExceptionType', $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterTime::filter
  * @dataProvider filterProvider
  */
  public function testFilter($timeString, $expected) {
    $filter = new PapayaFilterTime();
    $this->assertEquals($expected, $filter->filter($timeString));
  }

  /**
  * @covers PapayaFilterTime::_toTimestamp
  */
  public function testToTimestamp() {
    $filter = $this->getProxy('PapayaFilterTime');
    $this->assertEquals(3661, $filter->_toTimestamp(1, 1, 1));
  }

  public static function validateSuccessProvider() {
    return array(
      array('00:00:00'),
      array('12:00'),
      array('19:57:21'),
      array('23:59'),
      array('11:31:23Z')
    );
  }

  public static function validateExceptionTypeProvider() {
    return array(
      array('I am not a valid time'),
      array(''),
      array('hh:mm:ss'),
      array('12_56_29'),
      array('11:31:23+02:00')
    );
  }

  public static function validateExceptionRangeMaximumProvider() {
    return array(
      array('25:11:21'),
      array('23:82:11'),
      array('11:45:99')
    );
  }

  public static function filterProvider() {
    return array(
      array('23:23', '23:23'),
      array('23:15:00   ', '23:15:00'),
      array('45:87:91', NULL),
      array('I am not a time', NULL)
    );
  }
}