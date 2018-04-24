<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterUrlHttpTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterUrlHttp::validate
  * @covers PapayaFilterUrlHttp::prepare
  * @dataProvider providValidUrls
  */
  public function testValidate($value) {
    $filter = new PapayaFilterUrlHttp();
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterUrlHttp::validate
  * @covers PapayaFilterUrlHttp::prepare
  * @dataProvider provideInvalidValues
  */
  public function testValidateExpectingException($value) {
    $filter = new PapayaFilterUrlHttp();
    $this->setExpectedException(PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterUrlHttp::filter
  * @covers PapayaFilterUrlHttp::prepare
  */
  public function testFilterExpectingNull() {
    $filter = new PapayaFilterUrlHttp();
    $this->assertNull($filter->filter(''));
  }

  /**
  * @covers PapayaFilterUrlHttp::filter
  * @covers PapayaFilterUrlHttp::prepare
  */
  public function testFilterExpectingValue() {
    $filter = new PapayaFilterUrlHttp();
    $this->assertEquals('http://www.sample.tld', $filter->filter('http://www.sample.tld'));
  }

  /**
  * @covers PapayaFilterUrlHttp::filter
  * @covers PapayaFilterUrlHttp::prepare
  */
  public function testFilterExpectingExtendedValue() {
    $filter = new PapayaFilterUrlHttp();
    $this->assertEquals('http://localhost', $filter->filter('localhost'));
  }

  /************************
  * Data Provider
  ************************/

  public static function providValidUrls() {
    return array(
      array('localhost'),
      array('example.tld'),
      array('www.example.tld'),
      array('http://localhost'),
      array('https://example.tld')
    );
  }

  public static function provideInvalidValues() {
    return array(
      array('foo.'),
      array(':8080'),
      array(''),
      array(' ')
    );
  }
}
