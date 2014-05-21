<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUtilStringTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilString::truncate
  * @dataProvider provideTruncateSamples
  */
  public function testTruncate($expected, $string, $length, $cut) {
    $this->assertEquals(
      $expected, PapayaUtilString::truncate($string, $length, $cut)
    );
  }

  /**
  * @covers PapayaUtilString::truncate
  */
  public function testTruncateAppendsSuffix() {
    $this->assertEquals(
      "Hello\xE2\x80\xA6", PapayaUtilString::truncate('Hello World', 6, FALSE, "\xE2\x80\xA6")
    );
  }

  /**
  * @covers PapayaUtilString::truncate
  */
  public function testTruncateWithShortStringExpectingNoSuffix() {
    $this->assertEquals(
      'Hello', PapayaUtilString::truncate('Hello', 6, FALSE, "\xE2\x80\xA6")
    );
  }

  /**
  * @covers PapayaUtilString::escapeForPrintf
  * @dataProvider provideEscapingSamples
  */
  public function testEscapeForPrintf($expected, $input) {
    $this->assertEquals(
      $expected, PapayaUtilString::escapeForPrintf($input)
    );
  }

  /*************************
  * Data Provider
  *************************/

  public static function provideTruncateSamples() {
    return array(
      'empty' => array('', 'sample', 3, FALSE),
      'copy all' => array('sample text', 'sample text', 100, FALSE),
      'first word' => array('sample', 'sample text', 9, FALSE),
      'second word cutted' => array('sample te', 'sample text', 9, TRUE),
      'unicode example' => array('äöü', 'äöüäöü', 3, TRUE),
      'unicode example with whitespaces' => array('äöü', 'äöü  äöü', 5, FALSE)
    );
  }

  public static function provideEscapingSamples() {
    return array(
      'empty' => array('', ''),
      'no special chars' => array('sample', 'sample'),
      'special chars' => array('%%sample%%', '%sample%'),
    );
  }
}