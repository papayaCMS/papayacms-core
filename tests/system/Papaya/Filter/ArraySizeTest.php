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

namespace system\Papaya\Filter {

  use Papaya\Filter\ArraySize;
  use Papaya\Filter\Exception as FilterException;
  use Papaya\Test\TestCase;
  use RangeException;

  /**
   * @covers \Papaya\Filter\ArraySize
   */
  class ArraySizeTest extends TestCase {

    public function testConstructorWithMaximumSmallerMinimumExpectingException() {
      $this->expectException(RangeException::class);
      new ArraySize(5, 1);
    }

    public function testConstructorWithMaximumButWithoutMinimumExpectingException() {
      $this->expectException(RangeException::class);
      new ArraySize(NULL, 1);
    }

    /**
     * @param int $minimum
     * @param int $maximum
     * @param array $input
     * @throws FilterException
     * @testWith
     *   [null, null, ["foo"]]
     *   [2, 5, ["foo", "bar"]]
     *   [2, 2, ["foo", "bar"]]
     */
    public function testValidateExpectingTrue($minimum, $maximum, $input) {
      $filter = new ArraySize($minimum, $maximum);
      $this->assertTrue($filter->validate($input));
    }

    /**
     * @param int $minimum
     * @param int $maximum
     * @param mixed $input
     * @throws FilterException
     * @testWith
     *   [0, 2, ["foo", "bar", 42]]
     *   [10, 10, ["foo"]]
     *   [null, null, "test"]
     */
    public function testValidateExpectingException($minimum, $maximum, $input) {
      $filter = new ArraySize($minimum, $maximum);
      $this->expectException(FilterException::class);
      $filter->validate($input);
    }

    /**
     * @param int $minimum
     * @param int $maximum
     * @param string $input
     * @testWith
     *   [null, null, ["foo"]]
     *   [2, 5, ["foo", "bar"]]
     *   [2, 2, ["foo", "bar"]]
     */
    public function testFilterWithValidValues($minimum, $maximum, $input) {
      $filter = new ArraySize($minimum, $maximum);
      $this->assertSame($input, $filter->filter($input));
    }

    /**
     * @param int $minimum
     * @param int $maximum
     * @param string $input
     * @testWith
     *   [0, 2, ["foo", "bar", 42]]
     *   [10, 10, ["foo"]]
     */
    public function testFilterWithInvalidValues($minimum, $maximum, $input) {
      $filter = new ArraySize($minimum, $maximum);
      $this->assertSame([], $filter->filter($input));
    }
  }

}
