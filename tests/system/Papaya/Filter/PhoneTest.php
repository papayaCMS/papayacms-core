<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterPhoneTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterPhone::validate
  * @dataProvider provideValidPhoneNumbers
  */
  public function testValidateExpectingTrue($phoneNumber) {
    $filter = new PapayaFilterPhone();
    $this->assertTrue($filter->validate($phoneNumber));
  }

  /**
  * @covers PapayaFilterPhone::validate
  * @dataProvider provideInvalidData
  */
  public function testValidateExpectingException($string) {
    $filter = new PapayaFilterPhone();
    $this->setExpectedException(PapayaFilterExceptionType::class);
    $filter->validate($string);
  }

  /**
  * @covers PapayaFilterPhone::filter
  * @dataProvider provideFilterData
  */
  public function testFilter($expected, $input) {
    $filter = new PapayaFilterPhone();
    $this->assertEquals($expected, $filter->filter($input));
  }

  /**********************
  * Data Provider
  **********************/

  public static function provideValidPhoneNumbers() {
    return array(
      array('022157438070'),
      array('0221-5743-8070'),
      array('+49 221 5743-8070'),
      array('0049 221 5743-8070'),
      array('(0221) 5743-8070'),
      array('0221 5743-8070'),
      array('5743-8070'),
      array('5743 8070')
    );
  }

  public static function provideInvalidData() {
    return array(
      array('-49 0221 5743-8070'),
      array('no phone number'),
      array('24   53'),
    );
  }

  public static function provideFilterData() {
    return array(
      'valid' => array('1234567890', "1234567890"),
      'invalid signs' => array(NULL, '7389ksjdhu')
    );
  }
}
