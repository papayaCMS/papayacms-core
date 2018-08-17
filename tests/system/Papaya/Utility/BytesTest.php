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

namespace Papaya\Utility;
require_once __DIR__.'/../../../bootstrap.php';

class BytesTest extends \Papaya\TestCase {

  /**
   * @covers       \Papaya\Utility\Bytes::toString
   * @dataProvider provideBytesAndStrings
   * @param string $expected
   * @param int $bytes
   */
  public function testToString($expected, $bytes) {
    $this->assertEquals(
      $expected, Bytes::toString($bytes)
    );
  }

  /**
   * @covers \Papaya\Utility\Bytes::toString
   */
  public function testToStringWithGermanDecimalSeparator() {
    $this->assertEquals(
      '39,1 GB', Bytes::toString(42001231205, 1, ',')
    );
  }

  /**
   * @covers       \Papaya\Utility\Bytes::fromString
   * @dataProvider provideStringsAndBytes
   * @param int $expected
   * @param string $bytes
   */
  public function testFromString($expected, $bytes) {
    $this->assertEquals(
      $expected, Bytes::fromString($bytes)
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
