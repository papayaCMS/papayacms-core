<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../bootstrap.php';

class PapayaUtilFileTest extends PapayaTestCase {

  /**
   * @covers \PapayaUtilFile::formatBytes
   * @dataProvider provideBytesAndStrings
   * @param string $expected
   * @param int $bytes
   */
  public function testFormatBytes($expected, $bytes) {
    $this->assertEquals(
      $expected, \PapayaUtilFile::formatBytes($bytes)
    );
  }

  /**
  * @covers \PapayaUtilFile::formatBytes
  */
  public function testFormatBytesWithGermanDecimalSeparator() {
    $this->assertEquals(
      '39,1 GB', \PapayaUtilFile::formatBytes(42001231205, 1, ',')
    );
  }

  /**
   * @covers \PapayaUtilFile::normalizeName
   * @dataProvider provideStringsForNames
   * @param string $expected
   * @param string $string
   */
  public function testNormalizeName($expected, $string) {
    $this->assertEquals(
      $expected, \PapayaUtilFile::normalizeName($string, 15, 'de')
    );
  }

  /**
  * @covers \PapayaUtilFile::normalizeName
  */
  public function testNormalizeNameWithUnderscoreSeparator() {
    $this->assertEquals(
      'Hallo_Welt', \PapayaUtilFile::normalizeName('Hallo Welt', 15, 'de', '_')
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
