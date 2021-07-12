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

namespace Papaya\Text;
include_once __DIR__.'/../../../bootstrap.php';

class UTF8StringTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testConstructor() {
    $string = new UTF8String('TEST');
    $this->assertEquals('TEST', (string)$string);
  }

  /**
   * @covers \Papaya\Text\UTF8String
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testSetMode($mode) {
    $string = new UTF8String('TEST');
    $string->setMode($mode);
    $this->assertEquals($mode, $string->getMode());
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testSetModeWithInvalidMode() {
    $string = new UTF8String('TEST');
    $this->expectException(\LogicException::class);
    $string->setMode(999999);
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testLength() {
    $string = new UTF8String('ÄÖÜ');
    $this->assertEquals(3, $string->length());
  }

  /**
   * @covers \Papaya\Text\UTF8String
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testCharAt($mode) {
    $string = new UTF8String('ÄÖÜ');
    $string->setMode($mode);
    $this->assertEquals('Ö', $string->charAt(1));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testIndexOf() {
    $string = new UTF8String('ÄÖÜ');
    $this->assertEquals(1, $string->indexOf('Ö'));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testIndexOfWithOffset($mode) {
    $string = new UTF8String('ÄÖÜÄÖÜ');
    $string->setMode($mode);
    $this->assertEquals(4, $string->indexOf('Ö', 3));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testIndexOfWithoutMatch($mode) {
    $string = new UTF8String('ÄÖÜÄÖÜ');
    $string->setMode($mode);
    $this->assertFalse($string->indexOf('A'));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testLastIndexOf() {
    $string = new UTF8String('ÄÖÜÄÖÜ');
    $this->assertEquals(4, $string->lastIndexOf('Ö'));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testLastIndexOfWithOffset($mode) {
    $string = new UTF8String('ÄÖÜÄÖÜ');
    $string->setMode($mode);
    $this->assertEquals(1, $string->lastIndexOf('Ö', 3));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testLastIndexOfWithoutMatch($mode) {
    $string = new UTF8String('ÄÖÜÄÖÜ');
    $string->setMode($mode);
    $this->assertFalse($string->lastIndexOf('A'));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testSubstr($mode) {
    $string = new UTF8String('ÄÖÜ Hellö ÄÖÜ');
    $string->setMode($mode);
    $substring = $string->substr(4, 5);
    $this->assertInstanceOf(UTF8String::class, $substring);
    $this->assertEquals('Hellö', (string)$substring);
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testIterator() {
    $string = new UTF8String('ÄÖÜ');
    $this->assertEquals(array('Ä', 'Ö', 'Ü'), iterator_to_array($string));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testOffsetExistsExpectingTrue() {
    $string = new UTF8String('ÄÖÜ');
    $this->assertTrue(isset($string[1]));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testOffsetExistsExpectingFalse() {
    $string = new UTF8String('ÄÖÜ');
    $this->assertFalse(isset($string[999]));
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testOffsetGet() {
    $string = new UTF8String('ÄÖÜ');
    $this->assertEquals('Ö', $string[1]);
  }

  /**
   * @covers \Papaya\Text\UTF8String
   * @dataProvider dataProviderSupportedModes
   * @param int $mode
   */
  public function testOffsetSet($mode) {
    $string = new UTF8String('ÄÖÜ');
    $string->setMode($mode);
    $string[1] = 'ö';
    $this->assertEquals('ÄöÜ', (string)$string);
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testOffsetSetWithInvalidArgumentExpectingException() {
    $string = new UTF8String('ÄÖÜ');
    $this->expectException(\LogicException::class);
    $string[1] = 'öüä';
  }

  /**
   * @covers \Papaya\Text\UTF8String
   */
  public function testOffsetUnsetWithInvalidArgumentExpectingException() {
    $string = new UTF8String('ÄÖÜ');
    $this->expectException(\LogicException::class);
    unset($string[1]);
  }

  public function dataProviderSupportedModes() {
    $modes = array();
    if (extension_loaded('intl')) {
      $modes['intl'] = array(UTF8String::MODE_INTL);
    }
    if (extension_loaded('iconv')) {
      $modes['iconv'] = array(UTF8String::MODE_ICONV);
    }
    if (extension_loaded('mbstring')) {
      $modes['mbstring'] = array(UTF8String::MODE_MBSTRING);
    }
    return $modes;
  }
}

