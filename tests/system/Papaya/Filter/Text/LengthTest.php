<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Filter\Text {

  use Papaya\Filter\Exception as FilterException;
  use Papaya\Test\TestCase;
  use RangeException;

  /**
   * @covers \Papaya\Filter\Text\Length
   */
  class LengthTest extends TestCase {

    public function testConstructorWithMaximumSmallerMinimumExpectingException() {
      $this->expectException(RangeException::class);
      new Length(5, 1);
    }

    public function testConstructorWithMaximumButWithoutMinimumExpectingException() {
      $this->expectException(RangeException::class);
      new Length(NULL, 1);
    }

    /**
     * @param int $minimum
     * @param int $maximum
     * @param string $input
     * @throws FilterException
     * @testWith
     *   [null, null, "foo"]
     *   [1, 5, "foo"]
     *   [3, 3, "foo"]
     */
    public function testValidateExpectingTrue($minimum, $maximum, $input) {
      $filter = new Length($minimum, $maximum);
      $this->assertTrue($filter->validate($input));
    }

    /**
     * @param int $minimum
     * @param int $maximum
     * @param mixed $input
     * @throws FilterException
     * @testWith
     *   [0, 2, "foo"]
     *   [10, 10, "foo"]
     *   [null, null, []]
     */
    public function testValidateExpectingFalse($minimum, $maximum, $input) {
      $filter = new Length($minimum, $maximum);
      $this->expectException(FilterException::class);
      $filter->validate($input);
    }

    /**
     * @param mixed $expected
     * @param int $minimum
     * @param int $maximum
     * @param string $input
     * @testWith
     *   ["foo", null, null, "foo"]
     *   ["foo", 1, 5, "foo"]
     *   ["foo", 3, 3, "foo"]
     *   ["fo", 0, 2, "foo"]
     *   [null, 10, 10, "foo"]
     *   ["", null, null, []]
     */
    public function testFilter($expected, $minimum, $maximum, $input) {
      $filter = new Length($minimum, $maximum);
      $this->assertSame($expected, $filter->filter($input));
    }
  }

}
