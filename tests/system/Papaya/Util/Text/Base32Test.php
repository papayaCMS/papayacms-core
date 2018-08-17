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

class Base32Test extends \PapayaTestCase {

  /**
   * @covers       \Papaya\Utility\Text\Base32::encode
   * @dataProvider provideValidSamples
   * @param string $plain
   * @param string $encoded
   */
  public function testEncode($plain, $encoded) {
    $this->assertEquals(
      $encoded,
      Base32::encode($plain)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\Base32::encode
   * @dataProvider provideValidSamplesWithPadding
   * @param string $plain
   * @param string $encoded
   */
  public function testEncodeWithPadding($plain, $encoded) {
    $this->assertEquals(
      $encoded,
      Base32::encode($plain, TRUE)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\Base32::decode
   * @dataProvider provideValidSamples
   * @param string $plain
   * @param string $encoded
   */
  public function testDecode($plain, $encoded) {
    $this->assertEquals(
      $plain,
      Base32::decode($encoded)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\Base32::decode
   * @dataProvider provideValidSamplesWithPadding
   * @param string $plain
   * @param string $encoded
   */
  public function testDecodeWithPadding($plain, $encoded) {
    $this->assertEquals(
      $plain,
      Base32::decode($encoded)
    );
  }

  /**
   * @covers       \Papaya\Utility\Text\Base32::decode
   * @dataProvider provideInvalidDecodeSamples
   * @param string $encoded
   */
  public function testDecodeExpectingException($encoded) {
    $this->expectException(\OutOfBoundsException::class);
    Base32::decode($encoded);
  }

  /*********************************
   * Data Provider
   *********************************/

  public static function provideValidSamples() {
    return array(
      array('f', 'my'),
      array('fo', 'mzxq'),
      array('foo', 'mzxw6'),
      array('foob', 'mzxw6yq'),
      array('fooba', 'mzxw6ytb'),
      array('foobar', 'mzxw6ytboi')
    );
  }

  public static function provideValidSamplesWithPadding() {
    return array(
      array('f', 'my======'),
      array('fo', 'mzxq===='),
      array('foo', 'mzxw6==='),
      array('foob', 'mzxw6yq='),
      array('fooba', 'mzxw6ytb'),
      array('foobar', 'mzxw6ytboi======')
    );
  }

  public static function provideInvalidDecodeSamples() {
    return array(
      'length' => array('orsxg5'),
      'char' => array('0nqw24dmmuqhg5dsnfxgo'),
      'padding chars' => array('m3')
    );
  }
}
