<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUtilBytesTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilBytes::toString
  * @dataProvider provideBytesAndStrings
  */
  public function testToString($expected, $bytes) {
    $this->assertEquals(
      $expected, PapayaUtilBytes::toString($bytes)
    );
  }

  /**
  * @covers PapayaUtilBytes::toString
  */
  public function testToStringWithGermanDecimalSeparator() {
    $this->assertEquals(
      '39,1 GB', PapayaUtilBytes::toString(42001231205, 1, ',')
    );
  }

  /**
  * @covers PapayaUtilBytes::fromString
  * @dataProvider provideStringsAndBytes
  */
  public function testFromString($expected, $bytes) {
    $this->assertEquals(
      $expected, PapayaUtilBytes::fromString($bytes)
    );
  }

  public static function provideBytesAndStrings() {
    return array(
      array('0 B', 0),
      array('1 B', 1),
      array('42 B', 42),
      array('410.16 kB', 420005),
      array('40.09 MB', 42034005),
      array('39.12 GB', 42001231205)
    );
  }

  public static function provideStringsAndBytes() {
    return array(
      array(0, 'FOO'),
      array(0, '0 B'),
      array(1, '1 B'),
      array(42, '42'),
      array(42, '42 B'),
      array(419840, '410.16 kB'),
      array(41943040, '40.09 MB'),
      array(41875931136, '39.12 GB')
    );
  }
}