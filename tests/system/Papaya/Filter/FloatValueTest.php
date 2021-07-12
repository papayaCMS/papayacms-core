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

  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Filter\FloatValue
   */
  class FloatValueTest extends TestCase {

    public function testValidate() {
      $filter = new FloatValue();
      $this->expectException(Exception\NotNumeric::class);
      $filter->validate('sgs');
    }

    public function testValidateWithMinimum() {
      $filter = new FloatValue(-20.0);
      $this->expectException(Exception\OutOfRange\ToSmall::class);
      $filter->validate('-40');
    }

    public function testValidateWithMinimumAndMaximum() {
      $filter = new FloatValue(-20.0, 40.5);
      $this->expectException(Exception\OutOfRange\ToLarge::class);
      $filter->validate('50');
    }

    public function testValidateTrue() {
      $filter = new FloatValue(-20.0, 40.5);
      $this->assertTrue($filter->validate('10.51'));
    }

    /**
     * @dataProvider provideValidFilterValues
     * @param float $expected
     * @param mixed $value
     * @param float $minimum
     * @param float $maximum
     */
    public function testFilterExpectingValue($expected, $value, $minimum, $maximum) {
      $filter = new FloatValue($minimum, $maximum);
      $this->assertEquals($expected, $filter->filter($value));
    }

    /**
     * @dataProvider provideInvalidFilterValues
     * @param mixed $value
     * @param float $minimum
     * @param float $maximum
     */
    public function testFilterExpectingNull($value, $minimum, $maximum) {
      $filter = new FloatValue($minimum, $maximum);
      $this->assertNull($filter->filter($value));
    }

    public static function provideValidFilterValues() {
      return [
        [10.51, '10.51', NULL, NULL],
        [0, 'abc', NULL, NULL],
        [23.1, '23.10', 21, 42]
      ];
    }

    public static function provideInvalidFilterValues() {
      return [
        ['10', 11, 20],
        ['42', 11, 20]
      ];
    }
  }
}
