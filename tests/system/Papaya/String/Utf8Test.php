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

include_once __DIR__.'/../../../bootstrap.php';

class PapayaStringUtf8Test extends \PapayaTestCase {

  /**
   * @covers \PapayaStringUtf8
   */
  public function testConstructor() {
    $string = new \PapayaStringUtf8('TEST');
    $this->assertEquals('TEST', (string)$string);
  }

  /**
   * @covers \PapayaStringUtf8
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testSetMode($mode) {
    $string = new \PapayaStringUtf8('TEST');
    $string->setMode($mode);
    $this->assertEquals($mode, $string->getMode());
  }

  /**
   * @covers \PapayaStringUtf8
   */
  public function testSetModeWithInvalidMode() {
    $string = new \PapayaStringUtf8('TEST');
    $this->expectException(LogicException::class);
    $string->setMode(999999);
  }

  /**
   * @covers \PapayaStringUtf8
   */
  public function testLength() {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $this->assertEquals(3, $string->length());
  }

  /**
   * @covers \PapayaStringUtf8
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testCharAt($mode) {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $string->setMode($mode);
    $this->assertEquals('Ö', $string->charAt(1));
  }

  /**
   * @covers \PapayaStringUtf8
   */
  public function testIndexOf() {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $this->assertEquals(1, $string->indexOf('Ö'));
  }

  /**
   * @covers \PapayaStringUtf8
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testIndexOfWithOffset($mode) {
    $string = new \PapayaStringUtf8('ÄÖÜÄÖÜ');
    $string->setMode($mode);
    $this->assertEquals(4, $string->indexOf('Ö', 3));
  }

  /**
   * @covers \PapayaStringUtf8
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testIndexOfWithoutMatch($mode) {
    $string = new \PapayaStringUtf8('ÄÖÜÄÖÜ');
    $string->setMode($mode);
    $this->assertFalse($string->indexOf('A'));
  }

  /**
   * @covers \PapayaStringUtf8
   */
  public function testLastIndexOf() {
    $string = new \PapayaStringUtf8('ÄÖÜÄÖÜ');
    $this->assertEquals(4, $string->lastIndexOf('Ö'));
  }

  /**
   * @covers \PapayaStringUtf8
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testLastIndexOfWithOffset($mode) {
    $string = new \PapayaStringUtf8('ÄÖÜÄÖÜ');
    $string->setMode($mode);
    $this->assertEquals(1, $string->lastIndexOf('Ö', 3));
  }

  /**
   * @covers \PapayaStringUtf8
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testLastIndexOfWithoutMatch($mode) {
    $string = new \PapayaStringUtf8('ÄÖÜÄÖÜ');
    $string->setMode($mode);
    $this->assertFalse($string->lastIndexOf('A'));
  }

  /**
   * @covers \PapayaStringUtf8
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testSubstr($mode) {
    $string = new \PapayaStringUtf8('ÄÖÜ Hellö ÄÖÜ');
    $string->setMode($mode);
    $substring = $string->substr(4, 5);
    $this->assertInstanceOf(\PapayaStringUtf8::class, $substring);
    $this->assertEquals('Hellö', (string)$substring);
  }

  /**
   * @covers \PapayaStringUtf8
   */
  public function testIterator() {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $this->assertEquals(array('Ä', 'Ö', 'Ü'), iterator_to_array($string));
  }

  /**
   * @covers \PapayaStringUtf8
   */
  public function testOffsetExistsExpectingTrue() {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $this->assertTrue(isset($string[1]));
  }
  /**
   * @covers \PapayaStringUtf8
   */
  public function testOffsetExistsExpectingFalse() {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $this->assertFalse(isset($string[999]));
  }

  /**
   * @covers \PapayaStringUtf8
   */
  public function testOffsetGet() {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $this->assertEquals('Ö', $string[1]);
  }

  /**
   * @covers \PapayaStringUtf8
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testOffsetSet($mode) {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $string->setMode($mode);
    $string[1] = 'ö';
    $this->assertEquals('ÄöÜ', (string)$string);
  }

  /**
   * @covers \PapayaStringUtf8
   */
  public function testOffsetSetWithInvalidArgumentExpectingException() {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $this->expectException(LogicException::class);
    $string[1] = 'öüä';
  }

  /**
   * @covers \PapayaStringUtf8
   */
  public function testOffsetUnsetWithInvalidArgumentExpectingException() {
    $string = new \PapayaStringUtf8('ÄÖÜ');
    $this->expectException(LogicException::class);
    unset($string[1]);
  }

  public function dataProviderSupportedModes() {
    $modes = array();
    if (extension_loaded('intl')) {
      $modes['intl'] = array(\PapayaStringUtf8::MODE_INTL);
    }
    if (extension_loaded('iconv')) {
      $modes['iconv'] = array(\PapayaStringUtf8::MODE_ICONV);
    }
    if (extension_loaded('mbstring')) {
      $modes['mbstring'] = array(\PapayaStringUtf8::MODE_MBSTRING);
    }
    return $modes;
  }
}

