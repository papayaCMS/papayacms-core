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

class Utf8Test extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Utility\Text\UTF8::ensure
   * @dataProvider ensureDataProvider
   * @param string $string
   * @param string $expected
   */
  public function testEnsure($string, $expected) {
    $this->assertEquals(
      $expected,
      UTF8::ensure($string)
    );
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::getCodePoint
   * @dataProvider getCodepointDataProvider
   * @param string $string
   * @param string $expected
   */
  public function testGetCodepoint($expected, $string) {
    $this->assertEquals(
      $expected,
      UTF8::getCodePoint($string)
    );
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::length
   * @dataProvider provideLengthSamples
   * @param string $string
   * @param string $expected
   */
  public function testLengthUsingIntl($expected, $string) {
    $this->skipIfExtensionNotLoaded('intl');
    UTF8::setExtension(UTF8::EXT_INTL);
    $this->assertEquals($expected, UTF8::length($string));
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::position
   * @dataProvider providePositionSamples
   * @param string $expected
   * @param string $haystack
   * @param string $needle
   * @param int $offset
   */
  public function testPositionUsingIntl($expected, $haystack, $needle, $offset = 0) {
    $this->skipIfExtensionNotLoaded('intl');
    UTF8::setExtension(UTF8::EXT_INTL);
    $this->assertSame($expected, UTF8::position($haystack, $needle, $offset));
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::copy
   * @dataProvider provideCopySamples
   * @param string $expected
   * @param string $haystack
   * @param int $start
   * @param int|null $length
   */
  public function testCopyUsingIntl($expected, $haystack, $start, $length = NULL) {
    $this->skipIfExtensionNotLoaded('intl');
    UTF8::setExtension(UTF8::EXT_INTL);
    $this->assertEquals($expected, UTF8::copy($haystack, $start, $length));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers \Papaya\Utility\Text\UTF8::toLowerCase
   * @testWith
   *   ["abc", "ABC"]
   *   ["abcädef", "ABCÄDEF"]
   */
  public function testLowerCaseUsingIntl($expected, $input) {
    $this->skipIfExtensionNotLoaded('intl');
    UTF8::setExtension(UTF8::EXT_INTL);
    $this->assertEquals($expected, UTF8::toLowerCase($input));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers \Papaya\Utility\Text\UTF8::toUpperCase
   * @testWith
   *   ["ABC", "abc"]
   *   ["ABCÄDEF", "abcädef"]
   */
  public function testUpperCaseUsingIntl($expected, $input) {
    $this->skipIfExtensionNotLoaded('intl');
    UTF8::setExtension(UTF8::EXT_INTL);
    $this->assertEquals($expected, UTF8::toUpperCase($input));
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::length
   * @dataProvider provideLengthSamples
   * @param string $string
   * @param string $expected
   */
  public function testLengthUsingMbstring($expected, $string) {
    $this->skipIfExtensionNotLoaded('mbstring');
    UTF8::setExtension(UTF8::EXT_MBSTRING);
    $this->assertEquals($expected, UTF8::length($string));
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::position
   * @dataProvider providePositionSamples
   * @param string $expected
   * @param string $haystack
   * @param string $needle
   * @param int $offset
   */
  public function testPositionUsingMbstring($expected, $haystack, $needle, $offset = 0) {
    $this->skipIfExtensionNotLoaded('mbstring');
    UTF8::setExtension(UTF8::EXT_MBSTRING);
    $this->assertSame($expected, UTF8::position($haystack, $needle, $offset));
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::copy
   * @dataProvider provideCopySamples
   * @param string $expected
   * @param string $haystack
   * @param int $start
   * @param int|null $length
   */
  public function testCopyUsingMbstring($expected, $haystack, $start, $length = NULL) {
    $this->skipIfExtensionNotLoaded('mbstring');
    UTF8::setExtension(UTF8::EXT_MBSTRING);
    $this->assertEquals($expected, UTF8::copy($haystack, $start, $length));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers \Papaya\Utility\Text\UTF8::toLowerCase
   * @testWith
   *   ["abc", "ABC"]
   *   ["abcädef", "ABCÄDEF"]
   */
  public function testLowerCaseUsingMBString($expected, $input) {
    $this->skipIfExtensionNotLoaded('mbstring');
    UTF8::setExtension(UTF8::EXT_MBSTRING);
    $this->assertEquals($expected, UTF8::toLowerCase($input));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers \Papaya\Utility\Text\UTF8::toUpperCase
   * @testWith
   *   ["ABC", "abc"]
   *   ["ABCÄDEF", "abcädef"]
   */
  public function testUpperCaseUsingMBString($expected, $input) {
    $this->skipIfExtensionNotLoaded('mbstring');
    UTF8::setExtension(UTF8::EXT_MBSTRING);
    $this->assertEquals($expected, UTF8::toUpperCase($input));
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::length
   * @dataProvider provideLengthSamples
   * @param string $string
   * @param string $expected
   */
  public function testLengthUsingPcreFallback($expected, $string) {
    UTF8::setExtension(UTF8::EXT_PCRE);
    $this->assertEquals($expected, UTF8::length($string));
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::position
   * @dataProvider providePositionSamples
   * @param string $expected
   * @param string $haystack
   * @param string $needle
   * @param int $offset
   */
  public function testPositionUsingPcreFallback($expected, $haystack, $needle, $offset = 0) {
    UTF8::setExtension(UTF8::EXT_PCRE);
    $this->assertSame($expected, UTF8::position($haystack, $needle, $offset));
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::copy
   * @dataProvider provideCopySamples
   * @param string $expected
   * @param string $haystack
   * @param int $start
   * @param int|null $length
   */
  public function testCopyUsingPcreFallback($expected, $haystack, $start, $length = NULL) {
    UTF8::setExtension(UTF8::EXT_PCRE);
    $this->assertEquals($expected, UTF8::copy($haystack, $start, $length));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers \Papaya\Utility\Text\UTF8::toLowerCase
   * @testWith
   *   ["abc", "ABC"]
   *   ["abcÄdef", "ABCÄDEF"]
   */
  public function testLowerCaseUsingPcreFallback($expected, $input) {
    UTF8::setExtension(UTF8::EXT_PCRE);
    $this->assertEquals($expected, UTF8::toLowerCase($input));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers \Papaya\Utility\Text\UTF8::toUpperCase
   * @testWith
   *   ["ABC", "abc"]
   *   ["ABCäDEF", "abcädef"]
   */
  public function testUpperCaseUsingPcreFallback($expected, $input) {
    UTF8::setExtension(UTF8::EXT_PCRE);
    $this->assertEquals($expected, UTF8::toUpperCase($input));
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::getExtension
   */
  public function testGetExtension() {
    UTF8::setExtension(UTF8::EXT_UNKNOWN);
    $this->assertNotEquals(
      UTF8::EXT_UNKNOWN,
      UTF8::getExtension()
    );
  }

  /**
   * @covers \Papaya\Utility\Text\UTF8::setExtension
   * @covers \Papaya\Utility\Text\UTF8::getExtension
   */
  public function testGetExtensionAfterSetExtension() {
    UTF8::setExtension(UTF8::EXT_INTL);
    $this->assertEquals(
      UTF8::EXT_INTL,
      UTF8::getExtension()
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
