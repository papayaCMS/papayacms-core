<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterDateTest extends PapayaTestCase {
  /**
  * @covers PapayaFilterDate::__construct
  */
  public function testConstructSuccess() {
    $filter = new PapayaFilterDate(PapayaFilterDate::DATE_OPTIONAL_TIME, 600.0);
    $this->assertAttributeEquals(PapayaFilterDate::DATE_OPTIONAL_TIME, '_includeTime', $filter);
    $this->assertAttributeEquals(600.0, '_step', $filter);
  }

  /**
  * @covers PapayaFilterDate::__construct
  */
  public function testConstructExpectsExceptionIncludeTime() {
    try {
      $filter = new PapayaFilterDate(1000);
    } catch(UnexpectedValueException $e) {
      $this->assertInstanceOf('UnexpectedValueException', $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterDate::__construct
  */
  public function testConstructExpectsExceptionStep() {
    try {
      $filter = new PapayaFilterDate(PapayaFilterDate::DATE_OPTIONAL_TIME, -1);
    } catch(UnexpectedValueException $e) {
      $this->assertInstanceOf('UnexpectedValueException', $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterDate::validate
  * @dataProvider validateSuccessProvider
  */
  public function testValidateSuccess($includeTime, $value) {
    $filter = new PapayaFilterDate($includeTime);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterDate::validate
  * @dataProvider validateExceptionFormatProvider
  */
  public function testValidateExceptionFormat($includeTime, $value) {
    $filter = new PapayaFilterDate($includeTime);
    try {
      $filter->validate($value);
    } catch(PapayaFilterExceptionType $e) {
      $this->assertInstanceOf(PapayaFilterExceptionType::class, $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterDate::validate
  * @dataProvider validateExceptionRangeProvider
  */
  public function testValidateExceptionRange($value) {
    $filter = new PapayaFilterDate(PapayaFilterDate::DATE_NO_TIME);
    try {
      $filter->validate($value);
    } catch(PapayaFilterExceptionRangeMaximum $e) {
      $this->assertInstanceOf(PapayaFilterExceptionRangeMaximum::class, $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterDate::filter
  * @dataProvider filterSuccessProvider
  */
  public function testFilterSuccess($value, $result) {
    $filter = new PapayaFilterDate(PapayaFilterDate::DATE_OPTIONAL_TIME);
    $this->assertEquals($result, $filter->filter($value));
  }

  /**
  * @covers PapayaFilterDate::filter
  */
  public function testFilterFailure() {
    $filter = new PapayaFilterDate(PapayaFilterDate::DATE_OPTIONAL_TIME);
    $this->assertNull($filter->filter('I am not a date'));
  }

  public static function validateSuccessProvider() {
    return array(
      array(PapayaFilterDate::DATE_NO_TIME, '2010-02-28'),
      array(PapayaFilterDate::DATE_NO_TIME, '2012-02-29'),
      array(PapayaFilterDate::DATE_OPTIONAL_TIME, '2011-08-12'),
      array(PapayaFilterDate::DATE_OPTIONAL_TIME, '2011-08-12 18:11'),
      array(PapayaFilterDate::DATE_MANDATORY_TIME, '2011-08-12 18:11'),
      array(PapayaFilterDate::DATE_MANDATORY_TIME, '2013-04-15T04:41:59.44Z')
    );
  }

  public static function validateExceptionFormatProvider() {
    return array(
      array(PapayaFilterDate::DATE_NO_TIME, '11-08-12'),
      array(PapayaFilterDate::DATE_NO_TIME, '2011-08'),
      array(PapayaFilterDate::DATE_NO_TIME, '2011|08|12'),
      array(PapayaFilterDate::DATE_NO_TIME, 'I am not a date'),
      array(PapayaFilterDate::DATE_NO_TIME, '2011-08-12 18:36'),
      array(PapayaFilterDate::DATE_OPTIONAL_TIME, '2011-08-12 18:36 garbage'),
      array(PapayaFilterDate::DATE_MANDATORY_TIME, '2011-08-12')
    );
  }

  public static function validateExceptionRangeProvider() {
    return array(
      array('2011-02-29'),
      array('2012-02-30'),
      array('2011-08-32'),
      array('2011-13-01')
    );
  }

  public static function filterSuccessProvider() {
    return array(
      array('2011-08-12', '2011-08-12'),
      array('2011-08-12 18:53', '2011-08-12 18:53'),
      array('  2011-08-12  ', '2011-08-12')
    );
  }
}
