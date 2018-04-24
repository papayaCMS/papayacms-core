<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterUrlTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterUrl::validate
  */
  public function testValidateExpectingTrue() {
    $filter = new PapayaFilterUrl();
    $this->assertTrue($filter->validate('http://www.papaya-cms.com'));
  }

  /**
  * @covers PapayaFilterUrl::validate
  */
  public function testValidateExpectingException() {
    $filter = new PapayaFilterUrl();
    $this->setExpectedException('PapayaFilterExceptionType');
    $filter->validate("invalid url");
  }

  /**
  * @covers PapayaFilterUrl::filter
  * @dataProvider provideFilterData
  */
  public function testFilter($expected, $input) {
    $filter = new PapayaFilterUrl();
    $this->assertEquals($expected, $filter->filter($input));
  }

  /**********************
  * Data Provider
  **********************/

  public static function provideFilterData() {
    return array(
      'valid' => array('http://www.papaya-cms.com', "http://www.papaya-cms.com"),
      'valid query string' => array(
         'http://www.papaya-cms.com?foo=bar', "http://www.papaya-cms.com?foo=bar"
      ),
      'invalid domain' => array(NULL, 'http://www.papaya cms.com'),
      'invalid prefix' => array(NULL, 'h t t p ://www.papaya-cms.com'),
      'invalid tld' => array(NULL, 'http://www.papaya-cms.')
    );
  }
}
