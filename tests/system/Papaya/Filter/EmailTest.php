<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterEmailTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterEmail::validate
  */
  public function testValidateExpectingTrue() {
    $filter = new PapayaFilterEmail();
    $this->assertTrue($filter->validate('info@papaya-cms.com'));
  }

  /**
  * @covers PapayaFilterEmail::validate
  */
  public function testValidateExpectingException() {
    $filter = new PapayaFilterEmail();
    $this->setExpectedException(PapayaFilterExceptionType::class);
    $filter->validate("invalid email @dress");
  }

  /**
  * @covers PapayaFilterEmail::filter
  * @dataProvider provideFilterData
  */
  public function testFilter($expected, $input) {
    $filter = new PapayaFilterEmail();
    $this->assertEquals($expected, $filter->filter($input));
  }

  /**********************
  * Data Provider
  **********************/

  public static function provideFilterData() {
    return array(
      'valid' => array('info@papaya-cms.com', "info@papaya-cms.com"),
      'invalid domain' => array(NULL, 'info@papaya cms.com'),
      'invalid prefix' => array(NULL, 'i n f o@papaya-cms.com'),
      'invalid tld' => array(NULL, 'info@papaya-cms.')
    );
  }
}
