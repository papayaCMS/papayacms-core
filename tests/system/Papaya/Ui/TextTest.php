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

namespace Papaya\UI;
require_once __DIR__.'/../../../bootstrap.php';

class TextTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Text::__construct
   */
  public function testConstructor() {
    $string = new Text('Hello %s!', array('World'));
    $this->assertAttributeEquals(
      'Hello %s!', '_pattern', $string
    );
    $this->assertAttributeEquals(
      array('World'), '_values', $string
    );
  }

  /**
   * @covers \Papaya\UI\Text::__construct
   */
  public function testConstructorWithPatternOnly() {
    $string = new Text('Hello World!');
    $this->assertAttributeEquals(
      'Hello World!', '_pattern', $string
    );
    $this->assertAttributeEquals(
      array(), '_values', $string
    );
  }

  /**
   * @covers       \Papaya\UI\Text::__toString
   * @covers       \Papaya\UI\Text::compile
   * @dataProvider provideExamplesForToString
   * @param string $expected
   * @param string $pattern
   * @param array $values
   */
  public function testMagicMethodToString($expected, $pattern, array $values = array()) {
    $string = new Text($pattern, $values);
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
