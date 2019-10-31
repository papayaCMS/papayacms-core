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

namespace Papaya\Filter {

  use InvalidArgumentException;
  use Papaya\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Filter\Identifier
   */
  class IdentifierTest extends TestCase {

    public function testConstructorWithInvalidMinimumExpectingException() {
      $this->expectException(InvalidArgumentException::class);
      new Identifier(-1);
    }

    public function testConstructorWithInvalidMaximumExpectingException() {
      $this->expectException(InvalidArgumentException::class);
      new Identifier(1, -1);
    }

    public function testConstructorWithMinimumLargerMaximumExpectingException() {
      $this->expectException(InvalidArgumentException::class);
      new Identifier(10, 5);
    }

    /**
     * @dataProvider provideValuesForFilter
     * @param string|NULL $expected
     * @param mixed $input
     * @param int $minimum
     * @param int $maximum
     * @param int $mode
     */
    public function testFilter($expected, $input, $minimum = 0, $maximum = 0, $mode = Identifier::CASE_INSENSITIVE) {
      $filter = new Identifier($minimum, $maximum, $mode);
      $this->assertEquals($expected, $filter->filter($input));
    }

    /**
     * @dataProvider provideValuesForValidateExpectingTrue
     * @param mixed $input
     * @param int $minimum
     * @param int $maximum
     * @param int $mode
     * @throws Exception
     */
    public function testValidateExpectingTrue($input, $minimum = 0, $maximum = 0, $mode = Identifier::CASE_INSENSITIVE
    ) {
      $filter = new Identifier($minimum, $maximum, $mode);
      $this->assertTrue($filter->validate($input));
    }

    /**
     * @dataProvider provideValuesForValidateExpectingException
     * @param mixed $input
     * @param int $minimum
     * @param int $maximum
     * @param int $mode
     * @throws Exception
     */
    public function testValidateExpectingException(
      $input, $minimum = 0, $maximum = 0, $mode = Identifier::CASE_INSENSITIVE
    ) {
      $filter = new Identifier($minimum, $maximum, $mode);
      $this->expectException(Exception\InvalidValue::class);
      $this->assertTrue($filter->validate($input));
    }

    /**
     * @throws Exception\InvalidValue
     * @throws Exception\UnexpectedType
     */
    public function testValidateWithInvalidValueTypeExpectingException(
    ) {
      $filter = new Identifier();
      $this->expectException(Exception\UnexpectedType::class);
      $this->assertTrue($filter->validate([]));
    }

    public function provideValuesForFilter() {
      return [
        ['foo', 'foo'],
        [NULL, '$$$'],
        ['foo', 'foo', 1, 3],
        ['foo', 'foobar', 1, 3],
        [NULL, 'foo', 4, 4],
        ['FOO', 'foo', 1, 3, Identifier::UPPERCASE],
        ['foo', 'FOO', 1, 3, Identifier::LOWERCASE],
        ['foo', 'f$o$o$']
      ];
    }

    public function provideValuesForValidateExpectingTrue() {
      return [
        ['foo'],
        ['foo', 1, 3],
        ['FOO', 1, 3, Identifier::UPPERCASE],
        ['foo', 1, 3, Identifier::LOWERCASE]
      ];
    }

    public function provideValuesForValidateExpectingException() {
      return [
        ['$$$'],
        ['foo', 4, 4],
        ['foobar', 1, 3],
        ['foo', 1, 3, Identifier::UPPERCASE],
        ['FOO', 1, 3, Identifier::LOWERCASE]
      ];
    }
  }
}
