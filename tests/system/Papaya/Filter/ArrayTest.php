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

use Papaya\Filter;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterArrayTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Filter\ArrayOf::__construct
  */
  public function testConstructorWithElementFilter() {
    $filter = new \Papaya\Filter\ArrayOf($subFilter = $this->createMock(Papaya\Filter::class));
    $this->assertAttributeSame(
      $subFilter, '_elementFilter', $filter
    );
  }

  /**
   * @covers \Papaya\Filter\ArrayOf::validate
   * @dataProvider provideValidValidateData
   * @param mixed $value
   * @param NULL|Filter $elementFilter
   * @throws PapayaFilterException
   */
  public function testValidateExpectingTrue($value, $elementFilter = NULL) {
    $filter = new \Papaya\Filter\ArrayOf($elementFilter);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \Papaya\Filter\ArrayOf::validate
   * @dataProvider provideInvalidValidateData
   * @param mixed $value
   * @param NULL|Filter $elementFilter
   * @throws PapayaFilterException
   */
  public function testValidateExpectingException($value, $elementFilter = NULL) {
    $filter = new \Papaya\Filter\ArrayOf($elementFilter);
    $this->expectException(\PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
   * @covers \Papaya\Filter\ArrayOf::filter
   * @dataProvider provideValidFilterData
   * @param array|NULL $expected
   * @param mixed $value
   * @param NULL|Filter $elementFilter
   */
  public function testFilter($expected, $value, $elementFilter = NULL) {
    $filter = new \Papaya\Filter\ArrayOf($elementFilter);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
   * @covers \Papaya\Filter\ArrayOf::filter
   * @dataProvider provideInvalidFilterData
   * @param mixed $value
   * @param NULL|Filter $elementFilter
   */
  public function testFilterExpectingNull($value, $elementFilter = NULL) {
    $filter = new \Papaya\Filter\ArrayOf($elementFilter);
    $this->assertNull($filter->filter($value));
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidValidateData() {
    return array(
      array(array('foo')),
      array(array('foo'), new \PapayaFilterNotEmpty()),
      array(array('21', '42'), new \PapayaFilterInteger())
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      'empty string' => array(''),
      'empty array' => array(array()),
      'scalar' => array('23'),
      'empty element' => array(array(''), new \PapayaFilterNotEmpty()),
      'no integer element' => array(array('foo'), new \PapayaFilterInteger())
    );
  }

  public static function provideValidFilterData() {
    return array(
      array(array('foo'), array('foo')),
      array(array('foo'), array('foo'), new \PapayaFilterNotEmpty()),
      array(array(21, 42), array('21', '42'), new \PapayaFilterInteger())
    );
  }

  public static function provideInvalidFilterData() {
    return array(
      'empty string' => array(''),
      'empty array' => array(array()),
      'scalar' => array('23'),
      'empty element' => array(array(''), new \PapayaFilterNotEmpty())
    );
  }
}
