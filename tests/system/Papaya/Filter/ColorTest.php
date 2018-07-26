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

class PapayaFilterColorTest extends \PapayaTestCase {

  /**
  * @covers \PapayaFilterColor::validate
  */
  public function testValidateExpectingTrue() {
    $filter = new \PapayaFilterColor();
    $this->assertTrue($filter->validate('#FFFFFF'));
  }

  /**
  * @covers \PapayaFilterColor::validate
  */
  public function testValidateExpectingException() {
    $filter = new \PapayaFilterColor();
    $this->expectException(\PapayaFilterExceptionType::class);
    $filter->validate('invalid color');
  }

  /**
   * @covers \PapayaFilterColor::filter
   * @dataProvider provideFilterData
   * @param string|NULL $expected
   * @param mixed $input
   */
  public function testFilter($expected, $input) {
    $filter = new \PapayaFilterColor();
    $this->assertEquals($expected, $filter->filter($input));
  }

  /**********************
  * Data Provider
  **********************/

  public static function provideFilterData() {
    return array(
      'valid' => array('#FFFFFF', '#FFFFFF'),
      'invalid string' => array(NULL, '#FF FF FF'),
      'invalid prefix' => array(NULL, 'FFFFFF'),
      'invalid length' => array(NULL, '#FFFFFFFFFF')
    );
  }
}
