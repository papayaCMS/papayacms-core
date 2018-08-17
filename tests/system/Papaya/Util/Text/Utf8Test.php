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

namespace Papaya\Utility\Text;
require_once __DIR__.'/../../../../bootstrap.php';

class Utf8Test extends \PapayaTestCase {

  /**
   * @covers       \Papaya\Utility\Text\Utf8::ensure
   * @dataProvider ensureDataProvider
   * @param string $string
   * @param string $expected
   */
  public function testEnsure($string, $expected) {
    $this->assertEquals(
      $expected,
      Utf8::ensure($string)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::getCodepoint
   * @dataProvider getCodepointDataProvider
   * @param string $string
   * @param string $expected
   */
  public function testGetCodepoint($expected, $string) {
    $this->assertEquals(
      $expected,
      Utf8::getCodepoint($string)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::length
   * @dataProvider provideLengthSamples
   * @param string $string
   * @param string $expected
   */
  public function testLengthUsingIntl($expected, $string) {
    $this->skipIfExtensionNotLoaded('intl');
    Utf8::setExtension(Utf8::EXT_INTL);
    $this->assertEquals($expected, Utf8::length($string));
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::position
   * @dataProvider providePositionSamples
   * @param string $expected
   * @param string $haystack
   * @param string $needle
   * @param int $offset
   */
  public function testPositionUsingIntl($expected, $haystack, $needle, $offset = 0) {
    $this->skipIfExtensionNotLoaded('intl');
    Utf8::setExtension(Utf8::EXT_INTL);
    $this->assertSame($expected, Utf8::position($haystack, $needle, $offset));
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::copy
   * @dataProvider provideCopySamples
   * @param string $expected
   * @param string $haystack
   * @param int $start
   * @param int|null $length
   */
  public function testCopyUsingIntl($expected, $haystack, $start, $length = NULL) {
    $this->skipIfExtensionNotLoaded('intl');
    Utf8::setExtension(Utf8::EXT_INTL);
    $this->assertEquals($expected, Utf8::copy($haystack, $start, $length));
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::length
   * @dataProvider provideLengthSamples
   * @param string $string
   * @param string $expected
   */
  public function testLengthUsingMbstring($expected, $string) {
    $this->skipIfExtensionNotLoaded('mbstring');
    Utf8::setExtension(Utf8::EXT_MBSTRING);
    $this->assertEquals($expected, Utf8::length($string));
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::position
   * @dataProvider providePositionSamples
   * @param string $expected
   * @param string $haystack
   * @param string $needle
   * @param int $offset
   */
  public function testPositionUsingMbstring($expected, $haystack, $needle, $offset = 0) {
    $this->skipIfExtensionNotLoaded('mbstring');
    Utf8::setExtension(Utf8::EXT_MBSTRING);
    $this->assertSame($expected, Utf8::position($haystack, $needle, $offset));
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::copy
   * @dataProvider provideCopySamples
   * @param string $expected
   * @param string $haystack
   * @param int $start
   * @param int|null $length
   */
  public function testCopyUsingMbstring($expected, $haystack, $start, $length = NULL) {
    $this->skipIfExtensionNotLoaded('mbstring');
    Utf8::setExtension(Utf8::EXT_MBSTRING);
    $this->assertEquals($expected, Utf8::copy($haystack, $start, $length));
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::length
   * @dataProvider provideLengthSamples
   * @param string $string
   * @param string $expected
   */
  public function testLengthUsingPcreFallback($expected, $string) {
    Utf8::setExtension(Utf8::EXT_PCRE);
    $this->assertEquals($expected, Utf8::length($string));
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::position
   * @dataProvider providePositionSamples
   * @param string $expected
   * @param string $haystack
   * @param string $needle
   * @param int $offset
   */
  public function testPositionUsingPcreFallback($expected, $haystack, $needle, $offset = 0) {
    Utf8::setExtension(Utf8::EXT_PCRE);
    $this->assertSame($expected, Utf8::position($haystack, $needle, $offset));
  }

  /**
   * @covers       \Papaya\Utility\Text\Utf8::copy
   * @dataProvider provideCopySamples
   * @param string $expected
   * @param string $haystack
   * @param int $start
   * @param int|null $length
   */
  public function testCopyUsingPcreFallback($expected, $haystack, $start, $length = NULL) {
    Utf8::setExtension(Utf8::EXT_PCRE);
    $this->assertEquals($expected, Utf8::copy($haystack, $start, $length));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers \Papaya\Utility\Text\Utf8::toLowerCase
   * @testWith
   *   ["abc", "ABC"]
   *   ["abcÄdef", "ABCÄDEF"]
   */
  public function testLowerCaseUsingPcreFallback($expected, $input) {
    Utf8::setExtension(Utf8::EXT_PCRE);
    $this->assertEquals($expected, Utf8::toLowerCase($input));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers \Papaya\Utility\Text\Utf8::toUpperCase
   * @testWith
   *   ["ABC", "abc"]
   *   ["ABCäDEF", "abcädef"]
   */
  public function testUpperCaseUsingPcreFallback($expected, $input) {
    Utf8::setExtension(Utf8::EXT_PCRE);
    $this->assertEquals($expected, Utf8::toUpperCase($input));
  }

  /**
   * @covers \Papaya\Utility\Text\Utf8::getExtension
   */
  public function testGetExtension() {
    Utf8::setExtension(Utf8::EXT_UNKNOWN);
    $this->assertNotEquals(
      Utf8::EXT_UNKNOWN,
      Utf8::getExtension()
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Utf8::setExtension
   * @covers \Papaya\Utility\Text\Utf8::getExtension
   */
  public function testGetExtensionAfterSetExtension() {
    Utf8::setExtension(Utf8::EXT_INTL);
    $this->assertEquals(
      Utf8::EXT_INTL,
      Utf8::getExtension()
    );
  }

  private function skipIfExtensionNotLoaded($extension) {
    if (!extension_loaded($extension)) {
      $this->markTestSkipped(sprintf('Extension "%s" not loaded.', $extension));
    }
  }

  /*********************************
   * Data Provider
   *********************************/

  public static function ensureDataProvider() {
    return array(
      'ascii' => array('ascii', 'ascii'),
      'utf-8' => array("\xC2\x80\xE0\xA0\x80", "\xC2\x80\xE0\xA0\x80"),
      'latin 1' => array("\xE6\xF8", "\xC3\xA6\xC3\xB8"),
      'utf-8 mixed' => array(
        "\xC2\x80\xE6\xE0\xA0\x80\xF8", "\xC2\x80\xC3\xA6\xE0\xA0\x80\xC3\xB8"
      ),
      'no-break space' => array("\xA0", "\xC2\xA0")
    );
  }

  public static function getCodepointDataProvider() {
    return array(
      array(FALSE, ''),
      array(FALSE, 'STRING'),
      array(FALSE, "\xFF\xFF\xFF\xFF"),
      array(97, 'a'),
      array(65, 'A'),
      array(228, 'ä'),
      array(9993, '✉'),
      array(10084, '❤'),
      array(65441, 'ﾡ'),
      array(150370, '𤭢')
    );
  }

  public static function provideLengthSamples() {
    return array(
      'empty' => array(0, ''),
      'ascii' => array(5, 'Hello'),
      'korean' => array(3, '한국어'),
      'german umlauts' => array(3, 'äöü')
    );
  }

  public static function providePositionSamples() {
    return array(
      'not found' => array(FALSE, 'Hello', 'World'),
      'ascii' => array(1, 'Hello', 'e'),
      'offset' => array(7, 'Hello World', 'o', 5),
      'korean' => array(2, '한국어', '어')
    );
  }

  public static function provideCopySamples() {
    return array(
      'ascii 1, *' => array('ello', 'Hello', 1),
      'korean 2, *' => array('어', '한국어', 2),
      'korean 2, 1' => array('어', '한국어', 2, 1),
      'korean 1, 2' => array('국어', '한국어', 1, 2),
      'korean 1, 20' => array('국어', '한국어', 1, 20),
      'korean -2, -1' => array('국', '한국어', -2, -1),
      'russian -3, 20' => array('кий', 'Русский', -3, 20),
      'ascii -3, 20' => array('cii', 'ascii', -3, 20),
      'ascii 1, 0' => array('', 'ascii', 1, 0),
      'ascii 10, 10' => array('', 'ascii', 10, 10)
    );
  }
}
