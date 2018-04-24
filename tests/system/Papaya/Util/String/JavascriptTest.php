<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilStringJavascriptTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringJavascript::quote
  * @dataProvider quoteDataProvider
  */
  public function testQuote($string, $expected) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringJavascript::quote($string)
    );
  }

  public function testQuoteWithDoubleQuotes() {
    $this->assertEquals(
      '"foo\\"-" + "-bar"',
      PapayaUtilStringJavascript::quote('foo"--bar', '"')
    );
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function quoteDataProvider() {
    return array(
      array('foo"bar', "'foo\"bar'"),
      array('foo\'bar', "'foo\\'bar'"),
      array('foo--bar', "'foo-' + '-bar'"),
      array("foo\r\nbar", "'foo\\r\\nbar'")
    );
  }
}
