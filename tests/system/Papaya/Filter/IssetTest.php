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

class PapayaFilterIssetTest extends PapayaTestCase {

  /**
   * @covers \PapayaFilterIsset::validate
   * @dataProvider provideValues
   * @param mixed $value
   * @throws PapayaFilterExceptionUndefined
   */
  public function testCheck($value) {
    $filter = new \PapayaFilterIsset();
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers \PapayaFilterIsset::validate
  */
  public function testCheckExpectingException() {
    $filter = new \PapayaFilterIsset();
    $this->expectException(PapayaFilterExceptionUndefined::class);
    $filter->validate(NULL);
  }

  /**
   * @covers \PapayaFilterIsset::filter
   * @dataProvider provideValues
   * @param mixed $value
   */
  public function testFilter($value) {
    $filter = new \PapayaFilterIsset();
    $this->assertSame($value, $filter->filter($value));
  }

  /************************
  * Data Provider
  ************************/

  public static function provideValues() {
    return array(
      array(''),
      array(' '),
      array('0'),
      array(array()),
      array('some'),
      array('0'),
      array(array('0'))
    );
  }
}
