<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilStringUtf8Test extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringUtf8::ensure
  * @covers PapayaUtilStringUtf8::ensureCharCallback
  * @dataProvider ensureDataProvider
  */
  public function testEnsure($string, $expected) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringUtf8::ensure($string)
    );
  }

  /**
  * @covers PapayaUtilStringUtf8::getCodepoint
  * @dataProvider getCodepointDataProvider
  */
  public function testGetCodepoint($expected, $string) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringUtf8::getCodepoint($string)
    );
  }

  /**
  * @covers PapayaUtilStringUtf8::length
  * @dataProvider provideLengthSamples
  */
  public function testLengthUsingIntl($expected, $string) {
    $this->skipIfExtensionNotLoaded('intl');
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_INTL);
    $this->assertEquals($expected, PapayaUtilStringUtf8::length($string));
  }

  /**
  * @covers PapayaUtilStringUtf8::position
  * @dataProvider providePositionSamples
  */
  public function testPositionUsingIntl($expected, $haystack, $needle, $offset = 0) {
    $this->skipIfExtensionNotLoaded('intl');
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_INTL);
    $this->assertSame($expected, PapayaUtilStringUtf8::position($haystack, $needle, $offset));
  }

  /**
  * @covers PapayaUtilStringUtf8::copy
  * @dataProvider provideCopySamples
  */
  public function testCopyUsingIntl($expected, $haystack, $start, $length = NULL) {
    $this->skipIfExtensionNotLoaded('intl');
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_INTL);
    $this->assertEquals($expected, PapayaUtilStringUtf8::copy($haystack, $start, $length));
  }

  /**
  * @covers PapayaUtilStringUtf8::length
  * @dataProvider provideLengthSamples
  */
  public function testLengthUsingMbstring($expected, $string) {
    $this->skipIfExtensionNotLoaded('mbstring');
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_MBSTRING);
    $this->assertEquals($expected, PapayaUtilStringUtf8::length($string));
  }

  /**
  * @covers PapayaUtilStringUtf8::position
  * @dataProvider providePositionSamples
  */
  public function testPositionUsingMbstring($expected, $haystack, $needle, $offset = 0) {
    $this->skipIfExtensionNotLoaded('mbstring');
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_MBSTRING);
    $this->assertSame($expected, PapayaUtilStringUtf8::position($haystack, $needle, $offset));
  }

  /**
  * @covers PapayaUtilStringUtf8::copy
  * @dataProvider provideCopySamples
  */
  public function testCopyUsingMbstring($expected, $haystack, $start, $length = NULL) {
    $this->skipIfExtensionNotLoaded('mbstring');
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_MBSTRING);
    $this->assertEquals($expected, PapayaUtilStringUtf8::copy($haystack, $start, $length));
  }

  /**
  * @covers PapayaUtilStringUtf8::length
  * @dataProvider provideLengthSamples
  */
  public function testLengthUsingPcreFallback($expected, $string) {
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_PCRE);
    $this->assertEquals($expected, PapayaUtilStringUtf8::length($string));
  }

  /**
  * @covers PapayaUtilStringUtf8::position
  * @dataProvider providePositionSamples
  */
  public function testPositionUsingPcreFallback($expected, $haystack, $needle, $offset = 0) {
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_PCRE);
    $this->assertSame($expected, PapayaUtilStringUtf8::position($haystack, $needle, $offset));
  }

  /**
  * @covers PapayaUtilStringUtf8::copy
  * @dataProvider provideCopySamples
  */
  public function testCopyUsingPcreFallback($expected, $haystack, $start, $length = NULL) {
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_PCRE);
    $this->assertEquals($expected, PapayaUtilStringUtf8::copy($haystack, $start, $length));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers PapayaUtilStringUtf8::toLowerCase
   * @testWith
   *   ["abc", "ABC"]
   *   ["abcÄdef", "ABCÄDEF"]
   */
  public function testLowerCaseUsingPcreFallback($expected, $input) {
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_PCRE);
    $this->assertEquals($expected, PapayaUtilStringUtf8::toLowerCase($input));
  }

  /**
   * @param string $expected
   * @param string $input
   * @covers PapayaUtilStringUtf8::toUpperCase
   * @testWith
   *   ["ABC", "abc"]
   *   ["ABCäDEF", "abcädef"]
   */
  public function testUpperCaseUsingPcreFallback($expected, $input) {
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_PCRE);
    $this->assertEquals($expected, PapayaUtilStringUtf8::toUpperCase($input));
  }

    /**
  * @covers PapayaUtilStringUtf8::getExtension
  */
  public function testGetExtension() {
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_UNKNOWN);
    $this->assertNotEquals(
      PapayaUtilStringUtf8::EXT_UNKNOWN,
      PapayaUtilStringUtf8::getExtension()
    );
  }

  /**
  * @covers PapayaUtilStringUtf8::setExtension
  * @covers PapayaUtilStringUtf8::getExtension
  */
  public function testGetExtensionAfterSetExtension() {
    PapayaUtilStringUtf8::setExtension(PapayaUtilStringUtf8::EXT_INTL);
    $this->assertEquals(
      PapayaUtilStringUtf8::EXT_INTL,
      PapayaUtilStringUtf8::getExtension()
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
