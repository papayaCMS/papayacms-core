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
 * @covers \Papaya\Filter\ArrayOf
 */
class ArrayOfTest extends \Papaya\TestCase {

  /**
   * @dataProvider provideValidValidateData
   * @param mixed $value
   * @param NULL|\Papaya\Filter $elementFilter
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateExpectingTrue($value, $elementFilter = NULL) {
    $filter = new ArrayOf($elementFilter);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @dataProvider provideInvalidValidateData
   * @param mixed $value
   * @param NULL|\Papaya\Filter $elementFilter
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateExpectingException($value, $elementFilter = NULL) {
    $filter = new ArrayOf($elementFilter);
    $this->expectException(Exception::class);
    $filter->validate($value);
  }

  /**
   * @dataProvider provideValidFilterData
   * @param array|NULL $expected
   * @param mixed $value
   * @param NULL|\Papaya\Filter $elementFilter
   */
  public function testFilter($expected, $value, $elementFilter = NULL) {
    $filter = new ArrayOf($elementFilter);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
   * @dataProvider provideInvalidFilterData
   * @param mixed $value
   * @param NULL|\Papaya\Filter $elementFilter
   */
  public function testFilterExpectingNull($value, $elementFilter = NULL) {
    $filter = new ArrayOf($elementFilter);
    $this->assertNull($filter->filter($value));
  }

  /**************************
   * Data Provider
   **************************/

  public static function provideValidValidateData() {
    return array(
      array(array('foo')),
      array(array('foo'), new NotEmpty()),
      array(array('21', '42'), new IntegerValue())
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      'empty string' => array(''),
      'empty array' => array(array()),
      'scalar' => array('23'),
      'empty element' => array(array(''), new NotEmpty()),
      'no integer element' => array(array('foo'), new IntegerValue())
    );
  }

  public static function provideValidFilterData() {
    return array(
      array(array('foo'), array('foo')),
      array(array('foo'), array('foo'), new NotEmpty()),
      array(array(21, 42), array('21', '42'), new IntegerValue())
    );
  }

  public static function provideInvalidFilterData() {
    return array(
      'empty string' => array(''),
      'empty array' => array(array()),
      'scalar' => array('23'),
      'empty element' => array(array(''), new NotEmpty())
    );
  }
}
