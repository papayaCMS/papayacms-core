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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterEqualsTest extends \PapayaTestCase {

  /**
  * @covers \PapayaFilterEquals::__construct
  */
  public function testConstructor() {
    $filter = new \PapayaFilterEquals('success');
    $this->assertAttributeEquals(
      'success', '_value', $filter
    );
  }

  /**
   * @covers \PapayaFilterEquals::validate
   * @dataProvider provideEqualValues
   * @param mixed $expected
   * @param mixed $value
   * @throws \Papaya\Filter\Exception\NotEqual
   */
  public function testValidate($expected, $value) {
    $filter = new \PapayaFilterEquals($expected);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \PapayaFilterEquals::validate
   * @dataProvider provideNonEqualValues
   * @param mixed $expected
   * @param mixed $value
   * @throws \Papaya\Filter\Exception\NotEqual
   */
  public function testValidateExpectingException($expected, $value) {
    $filter = new \PapayaFilterEquals($expected);
    $this->expectException(\Papaya\Filter\Exception\NotEqual::class);
    $filter->validate($value);
  }

  /**
   * @covers \PapayaFilterEquals::filter
   * @dataProvider provideEqualValues
   * @param mixed $expected
   * @param mixed $value
   */
  public function testFilter($expected, $value) {
    $filter = new \PapayaFilterEquals($expected);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
   * @covers \PapayaFilterEquals::filter
   * @dataProvider provideNonEqualValues
   * @param mixed $expected
   * @param mixed $value
   */
  public function testFilterExpectingNull($expected, $value) {
    $filter = new \PapayaFilterEquals($expected);
    $this->assertNull($filter->filter($value));
  }

  /************************
  * Data Provider
  ************************/

  public static function provideEqualValues() {
    return array(
      array('true', 'true'),
      array(FALSE, FALSE),
      array(TRUE, TRUE),
      array(TRUE, 1),
      array(FALSE, 0),
      array(TRUE, 'true'),
      array(FALSE, '')
    );
  }

  public static function provideNonEqualValues() {
    return array(
      array('true', 'false'),
      array(TRUE, FALSE),
      array(FALSE, TRUE),
      array(TRUE, 0),
      array(FALSE, 1),
      array(FALSE, 'true'),
      array(TRUE, '')
    );
  }
}
