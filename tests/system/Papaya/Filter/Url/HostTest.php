<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterUrlHostTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterUrlHost
  * @dataProvider provideHostNameValues
  */
  public function testValidate($value) {
    $filter = new PapayaFilterUrlHost();
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterUrlHost
  * @dataProvider provideInvalidValues
  */
  public function testValidateExpectingException($value) {
    $filter = new PapayaFilterUrlHost();
    $this->expectException(PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterUrlHost
  */
  public function testFilterExpectingNull() {
    $filter = new PapayaFilterUrlHost();
    $this->assertNull($filter->filter(''));
  }

  /**
  * @covers PapayaFilterUrlHost
  */
  public function testFilterExpectingValue() {
    $filter = new PapayaFilterUrlHost();
    $this->assertEquals('localhost', $filter->filter('localhost'));
  }

  /************************
  * Data Provider
  ************************/

  public static function provideHostNameValues() {
    return array(
      array('localhost'),
      array('example.tld'),
      array('www.example.tld'),
      array('kölsch.köln.de'),
      array('kölsch.köln.de:8080')
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
