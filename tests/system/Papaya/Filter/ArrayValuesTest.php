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

namespace Papaya\Filter;

require_once __DIR__.'/../../../bootstrap.php';

/**
 * @covers \Papaya\Filter\ArrayValues
 */
class ArrayValuesTest extends \Papaya\TestCase {

  /**
   * @dataProvider provideValidValidateData
   * @param mixed $value
   * @param array|\Traversable $validValues
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateExpectingTrue($value, $validValues) {
    $filter = new ArrayValues($validValues);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @dataProvider provideInvalidValidateData
   * @param mixed $value
   * @param array|\Traversable $validValues
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateExpectingException($value, $validValues) {
    $filter = new ArrayValues($validValues);
    $this->expectException(\Papaya\Filter\Exception::class);
    $filter->validate($value);
  }

  /**
   * @dataProvider provideValidFilterData
   * @param mixed $expected
   * @param mixed $value
   * @param array|\Traversable $validValues
   */
  public function testFilter($expected, $value, $validValues) {
    $filter = new ArrayValues($validValues);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**************************
   * Data Provider
   **************************/

  public static function provideValidValidateData() {
    return array(
      array(array('21'), array(21, 42)),
      array(array('21'), array('21', '42')),
      array(array('21', 42), array('21', '42')),
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      array(array('23'), array(21, 42)),
      array(array('21', 23), array(21, 42)),
      array('string', array(21, 42)),
    );
  }

  public static function provideValidFilterData() {
    return array(
      array(array(21), array('21'), array(21, 42)),
      array(array(21), array('21', '23'), array(21, 42)),
      array(array(21, 42), array('21', '42'), array(21, 42)),
      array(array('21'), array('21'), array('21', '42')),
    );
  }
}
