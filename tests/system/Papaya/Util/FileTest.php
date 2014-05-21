<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUtilFileTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilFile::formatBytes
  * @dataProvider provideBytesAndStrings
  */
  public function testFormatBytes($expected, $bytes) {
    $this->assertEquals(
      $expected, PapayaUtilFile::formatBytes($bytes)
    );
  }

  /**
  * @covers PapayaUtilFile::formatBytes
  */
  public function testFormatBytesWithGermanDecimalSeparator() {
    $this->assertEquals(
      '39,1 GB', PapayaUtilFile::formatBytes(42001231205, 1, ',')
    );
  }

  /**
  * @covers PapayaUtilFile::normalizeName
  * @dataProvider provideStringsForNames
  */
  public function testNormalizeName($expected, $string) {
    $this->assertEquals(
      $expected, PapayaUtilFile::normalizeName($string, 15, 'de')
    );
  }

  /**
  * @covers PapayaUtilFile::normalizeName
  * @dataProvider provideStringsForNames
  */
  public function testNormalizeNameWithUnderscoreSeparator($expected, $string) {
    $this->assertEquals(
      'Hallo_Welt', PapayaUtilFile::normalizeName('Hallo Welt', 15, 'de', '_')
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

  public static function provideStringsForNames() {
    return array(
      array('Hallo-Welt', 'Hallo Welt'),
      array('Hallo-Schoene', 'Hallo Schöne Welt!'),
      array('HalloSchoeneWel', 'HalloSchöneWelt!'),
      array('Hallo-Schoene', 'Hallo--Schöne--Welt!'),
    );
  }
}