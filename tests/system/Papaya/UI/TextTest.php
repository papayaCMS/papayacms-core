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

namespace Papaya\UI {

  use Papaya\BaseObject\Interfaces\StringCastable;
  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Text
   */
  class TextTest extends TestCase {

    public function testConstructor() {
      $string = new Text('Hello %s!', ['World']);
      $this->assertEquals(
        'Hello %s!', $string->getPattern()
      );
      $this->assertEquals(
        ['World'], $string->getValues()
      );
    }

    public function testConstructorWithStringCastable() {
      $pattern = $this->createMock(StringCastable::class);
      $pattern
        ->method('__toString')
        ->willReturn('Hello %s!');

      $string = new Text($pattern, ['World']);
      $this->assertEquals(
        'Hello %s!', $string->getPattern()
      );
      $this->assertEquals(
        ['World'], $string->getValues()
      );
    }

    public function testConstructorWithPatternOnly() {
      $string = new Text('Hello World!');
      $this->assertEquals(
        'Hello World!', $string->getPattern()
      );
      $this->assertEquals(
        [], $string->getValues()
      );
    }

    /**
     * @dataProvider provideExamplesForToString
     * @param string $expected
     * @param string $pattern
     * @param array $values
     */
    public function testMagicMethodToString($expected, $pattern, array $values = []) {
      $string = new Text($pattern, $values);
      $this->assertEquals(
        $expected, (string)$string
      );
    }

    /**************************
     * Data Provider
     **************************/

    public static function provideExamplesForToString() {
      return [
        'string only' => ['Hello World!', 'Hello World!', []],
        'single value' => ['Hello World!', 'Hello %s!', ['World']],
        'two values' => ['Hello 2. World!', 'Hello %d. %s!', [2, 'World']]
      ];
    }
  }
}
