<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterNumberTest extends PapayaTestCase {
  /**
  * @covers PapayaFilterNumber::__construct
  */
  public function testConstructSuccess() {
    $filter = new PapayaFilterNumber(15, 16);
    $this->assertAttributeEquals(15, '_minimumLength', $filter);
    $this->assertAttributeEquals(16, '_maximumLength', $filter);
  }

  /**
  * @covers PapayaFilterNumber::__construct
  * @dataProvider constructFailureProvider
  */
  public function testConstructFailure($minimumLength, $maximumLength) {
    try {
      $filter = new PapayaFilterNumber($minimumLength, $maximumLength);
    } catch(UnexpectedValueException $e) {
      $this->assertInstanceOf('UnexpectedValueException', $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterNumber::validate
  * @dataProvider validateSuccessProvider
  */
  public function testValidateSuccess($value) {
    $filter = new PapayaFilterNumber(3, 4);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterNumber::validate
  */
  public function testValidateFailureFormat() {
    $filter = new PapayaFilterNumber();
    try {
      $filter->validate('I am not a number');
    } catch(PapayaFilterExceptionType $e) {
      $this->assertInstanceOf(PapayaFilterExceptionType::class, $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterNumber::validate
  */
  public function testValidateFailureTooShort() {
    $filter = new PapayaFilterNumber(3);
    try {
      $filter->validate('22');
    } catch(PapayaFilterExceptionRangeMinimum $e) {
      $this->assertInstanceOf(PapayaFilterExceptionRangeMinimum::class, $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterNumber::validate
  */
  public function testValidateFailureTooLong() {
    $filter = new PapayaFilterNumber(NULL, 3);
    try {
      $filter->validate('2222');
    } catch(PapayaFilterExceptionRangeMaximum $e) {
      $this->assertInstanceOf(PapayaFilterExceptionRangeMaximum::class, $e);
      return;
    }
    $this->fail('Expected exception not thrown.');
  }

  /**
  * @covers PapayaFilterNumber::filter
  * @dataProvider filterSuccessProvider
  */
  public function testFilterSuccess($value, $filtered) {
    $filter = new PapayaFilterNumber(3, 4);
    $this->assertEquals($filtered, $filter->filter($value));
  }

  /**
  * @covers PapayaFilterNumber::filter
  */
  public function testFilterFailure() {
    $filter = new PapayaFilterNumber();
    $this->assertNull($filter->filter('I am not a number'));
  }

  public static function constructFailureProvider() {
    return array(
      array(-1, NULL),
      array('String', NULL),
      array(NULL, -1),
      array(NULL, 'String'),
      array(5, 4)
    );
  }

  public static function validateSuccessProvider() {
    return array(
      array('100'),
      array('003'),
      array('0001')
    );
  }

  public static function filterSuccessProvider() {
    return array(
      array('0234', '0234'),
      array('    0234   ', '0234'),
      array('123', '123')
    );
  }
}
