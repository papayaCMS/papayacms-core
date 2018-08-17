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

class TextTest extends \PapayaTestCase {

  /**
   * @covers       \Papaya\Utility\Text::truncate
   * @dataProvider provideTruncateSamples
   * @param string $expected
   * @param string $string
   * @param int $length
   * @param bool $cut
   */
  public function testTruncate($expected, $string, $length, $cut) {
    $this->assertEquals(
      $expected, Text::truncate($string, $length, $cut)
    );
  }

  /**
   * @covers \Papaya\Utility\Text::truncate
   */
  public function testTruncateAppendsSuffix() {
    $this->assertEquals(
      "Hello\xE2\x80\xA6", Text::truncate('Hello World', 6, FALSE, "\xE2\x80\xA6")
    );
  }

  /**
   * @covers \Papaya\Utility\Text::truncate
   */
  public function testTruncateWithShortStringExpectingNoSuffix() {
    $this->assertEquals(
      'Hello', Text::truncate('Hello', 6, FALSE, "\xE2\x80\xA6")
    );
  }

  /**
   * @covers       \Papaya\Utility\Text::escapeForPrintf
   * @dataProvider provideEscapingSamples
   * @param $expected
   * @param $input
   */
  public function testEscapeForPrintf($expected, $input) {
    $this->assertEquals(
      $expected, Text::escapeForPrintf($input)
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
