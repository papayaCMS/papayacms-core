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

class NoLinebreakTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\NoLinebreak::validate
   */
  public function testValidateExpectingTrue() {
    $filter = new NoLinebreak();
    $this->assertTrue($filter->validate('Some Text Without Linebreak'));
  }

  /**
   * @covers \Papaya\Filter\NoLinebreak::validate
   */
  public function testValidateExpectingException() {
    $filter = new NoLinebreak();
    $this->expectException(Exception\InvalidCharacter::class);
    $filter->validate("Two\r\nLines");
  }

  /**
   * @covers       \Papaya\Filter\NoLinebreak::filter
   * @dataProvider provideFilterData
   * @param string|NULL $expected
   * @param mixed $input
   */
  public function testFilter($expected, $input) {
    $filter = new NoLinebreak();
    $this->assertEquals(
      $expected, $filter->filter($input)
    );
  }

  /**********************
   * Data Provider
   **********************/

  public static function provideFilterData() {
    return array(
      '1 line' => array('Line', 'Line'),
      '2 lines' => array('Line One Line Two', "Line One\r\nLine Two"),
      '3 lines' => array('Line One Line Two Line Three', "Line One\r\nLine Two\r\nLine Three"),
      'spaces' => array('Line One Line Two', "Line One   \r\n   Line Two")
    );
  }
}
