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

class PapayaUiStringTest extends PapayaTestCase {

  /**
  * @covers PapayaUiString::__construct
  */
  public function testConstructor() {
    $string = new PapayaUiString('Hello %s!', array('World'));
    $this->assertAttributeEquals(
      'Hello %s!', '_pattern', $string
    );
    $this->assertAttributeEquals(
      array('World'), '_values', $string
    );
  }

  /**
  * @covers PapayaUiString::__construct
  */
  public function testConstructorWithPatternOnly() {
    $string = new PapayaUiString('Hello World!');
    $this->assertAttributeEquals(
      'Hello World!', '_pattern', $string
    );
    $this->assertAttributeEquals(
      array(), '_values', $string
    );
  }

  /**
   * @covers PapayaUiString::__toString
   * @covers PapayaUiString::compile
   * @dataProvider provideExamplesForToString
   * @param string $expected
   * @param string $pattern
   * @param array $values
   */
  public function testMagicMethodToString($expected, $pattern, array $values = array()) {
    $string = new PapayaUiString($pattern, $values);
    $this->assertEquals(
      $expected, (string)$string
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideExamplesForToString() {
    return array(
      'string only' => array('Hello World!', 'Hello World!', array()),
      'single value' => array('Hello World!', 'Hello %s!', array('World')),
      'two values' => array('Hello 2. World!', 'Hello %d. %s!', array(2, 'World'))
    );
  }
}
