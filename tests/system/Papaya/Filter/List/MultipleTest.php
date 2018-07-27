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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterListMultipleTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Filter\ArrayValues::__construct
  */
  public function testConstructor() {
    $filter = new \Papaya\Filter\ArrayValues(array(21, 42));
    $this->assertAttributeSame(
      array(21, 42), '_list', $filter
    );
  }

  /**
   * @covers \Papaya\Filter\ArrayValues::validate
   * @dataProvider provideValidValidateData
   * @param mixed $value
   * @param array|Traversable $validValues
   * @throws \PapayaFilterException
   */
  public function testValidateExpectingTrue($value, $validValues) {
    $filter = new \Papaya\Filter\ArrayValues($validValues);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \Papaya\Filter\ArrayValues::validate
   * @dataProvider provideInvalidValidateData
   * @param mixed $value
   * @param array|Traversable $validValues
   * @throws \PapayaFilterException
   */
  public function testValidateExpectingException($value, $validValues) {
    $filter = new \Papaya\Filter\ArrayValues($validValues);
    $this->expectException(\PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
   * @covers \Papaya\Filter\ArrayValues::filter
   * @dataProvider provideValidFilterData
   * @param mixed $expected
   * @param mixed $value
   * @param array|Traversable $validValues
   */
  public function testFilter($expected, $value, $validValues) {
    $filter = new \Papaya\Filter\ArrayValues($validValues);
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
