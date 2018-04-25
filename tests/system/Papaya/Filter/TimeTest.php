<?php
require_once __DIR__.'/../../../bootstrap.php';

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
    $this->expectException(UnexpectedValueException::class);
    new PapayaFilterTime(-1);
  }

  /**
  * @covers PapayaFilterTime::validate
  * @dataProvider validateSuccessProvider
  */
  public function testValidateSuccess($timeString) {
    $filter = new PapayaFilterTime(1);
    /** @noinspection PhpUnhandledExceptionInspection */
    $this->assertTrue($filter->validate($timeString));
  }

  /**
  * @covers PapayaFilterTime::validate
  * @dataProvider validateExceptionTypeProvider
  */
  public function testValidateExceptionType($timeString) {
    $filter = new PapayaFilterTime();
    $this->expectException(PapayaFilterExceptionType::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate($timeString);
  }

  /**
  * @covers PapayaFilterTime::validate
  * @dataProvider validateExceptionRangeMaximumProvider
  */
  public function testValidateExceptionRangeMaximum($timeString) {
    $filter = new PapayaFilterTime();
    $this->expectException(PapayaFilterExceptionRangeMaximum::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate($timeString);
  }

  /**
  * @covers PapayaFilterTime::validate
  */
  public function testValidateExceptionTypeForStepMismatch() {
    $filter = new PapayaFilterTime(1800);
    $this->expectException(PapayaFilterExceptionType::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate('17:45');
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
    $filter = $this->getProxy(PapayaFilterTime::class);
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
