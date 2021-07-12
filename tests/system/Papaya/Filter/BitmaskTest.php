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
 * @covers \Papaya\Filter\Bitmask
 */
class BitmaskTest extends \Papaya\TestFramework\TestCase {


  /**
   * @dataProvider provideValidBitmasks
   * @param mixed $bitmask
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateExpectingTrue($bitmask) {
    $filter = new Bitmask(array(1, 2, 4, 16));
    $this->assertTrue(
      $filter->validate($bitmask)
    );
  }

  /**
   * @dataProvider provideInvalidBitmasks
   * @param mixed $bitmask
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateExpectingInvalidValueException($bitmask) {
    $filter = new Bitmask(array(1, 2, 4, 16));
    $this->expectException(\Papaya\Filter\Exception\InvalidValue::class);
    $filter->validate($bitmask);
  }

  public function testValidateExpectingInvalidValueTypeException() {
    $filter = new Bitmask(array(1, 2, 4, 16));
    $this->expectException(\Papaya\Filter\Exception\UnexpectedType::class);
    $filter->validate('fail');
  }

  /**
   * @dataProvider provideValidBitmasks
   * @param mixed $bitmask
   */
  public function testFilterWithValidBitmasks($bitmask) {
    $filter = new Bitmask(array(1, 2, 4, 16));
    $this->assertEquals(
      $bitmask, $filter->filter($bitmask)
    );
  }

  /**
   * @dataProvider provideInvalidBitmasks
   * @param mixed $bitmask
   */
  public function testFilterWithInvalidBitmasks($bitmask) {
    $filter = new Bitmask(array(1, 2, 4, 16));
    $this->assertNull(
      $filter->filter($bitmask)
    );
  }

  public static function provideValidBitmasks() {
    return array(
      array(0),
      array(1 | 2),
      array(1 | 16),
      array(1 | 2 | 4 | 16)
    );
  }

  public static function provideInvalidBitmasks() {
    return array(
      array(-1),
      array(32),
      array(1 | 8),
      array(8)
    );
  }
}
