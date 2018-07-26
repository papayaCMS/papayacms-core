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

class PapayaFilterPasswordTest extends PapayaTestCase {

  /**
  * @covers \PapayaFilterPassword::__construct
  */
  public function testConstructor() {
    $filter = new \PapayaFilterPassword(21, 42);
    $this->assertAttributeSame(
      21, '_minimumLength', $filter
    );
    $this->assertAttributeSame(
      42, '_maximumLength', $filter
    );
  }

  /**
  * @covers \PapayaFilterPassword::validate
  * @dataProvider provideValidPasswords
  */
  public function testValidateExpectingTrue() {
    $filter = new \PapayaFilterPassword();
    $this->assertTrue($filter->validate('Foo.Bar5'));
  }

  /**
  * @covers \PapayaFilterPassword::validate
  */
  public function testValidateExpectingExceptionLengthMinimum() {
    $filter = new \PapayaFilterPassword(5);
    $this->expectException(PapayaFilterExceptionLengthMinimum::class);
    $filter->validate('Foo');
  }

  /**
  * @covers \PapayaFilterPassword::validate
  */
  public function testValidateExpectingExceptionLengthMaximum() {
    $filter = new \PapayaFilterPassword(1, 2);
    $this->expectException(PapayaFilterExceptionLengthMaximum::class);
    $filter->validate('Foo');
  }

  /**
  * @covers \PapayaFilterPassword::validate
  * @dataProvider provideWeakPasswords
  */
  public function testValidateExpectingExceptionPasswordWeak() {
    $filter = new \PapayaFilterPassword(1, 10);
    $this->expectException(PapayaFilterExceptionPasswordWeak::class);
    $filter->validate('foo');
  }

  /**
  * @covers \PapayaFilterPassword::filter
  */
  public function testFilterExpectingValue() {
    $filter = new \PapayaFilterPassword();
    $this->assertEquals(
      'FooBar.5',
      $filter->filter('FooBar.5')
    );
  }

  /**
  * @covers \PapayaFilterPassword::filter
  */
  public function testFilterExpectingNull() {
    $filter = new \PapayaFilterPassword();
    $this->assertNull(
      $filter->filter('Foo')
    );
  }

  /************************
  * Data Provider
  *************************/

  public static function provideValidPasswords() {
    return array(
      array('abcdef12'),
      array('abc1def2'),
      array('abc/def§'),
      array('some text with whitespace')
    );
  }

  public static function provideWeakPasswords() {
    return array(
      array('abcdefgh'),
      array('abcdefghiklmn'),
      array('abcdefg1')
    );
  }
}
