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

namespace Papaya\Utility;

require_once __DIR__.'/../../../bootstrap.php';

class BitwiseTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Utility\Bitwise::inBitmask
   * @dataProvider provideInBitmaskPositiveData
   * @param int $bit
   * @param int $bitmask
   */
  public function testInBitmaskExpectingTrue($bit, $bitmask) {
    $this->assertTrue(
      Bitwise::inBitmask($bit, $bitmask)
    );
  }

  /**
   * @covers \Papaya\Utility\Bitwise::inBitmask
   * @dataProvider provideInBitmaskNegativeData
   * @param int $bit
   * @param int $bitmask
   */
  public function testInBitmaskExpectingFalse($bit, $bitmask) {
    $this->assertFalse(
      Bitwise::inBitmask($bit, $bitmask)
    );
  }

  /**
   * @covers \Papaya\Utility\Bitwise::union
   * @dataProvider provideUnionData
   * @param int $expected
   * @param array $bits
   */
  public function testUnion($expected, array $bits) {
    $this->assertEquals(
      $expected,
      call_user_func_array(Bitwise::class.'::union', $bits)
    );
  }

  /****************************************
   * Data Provider
   ****************************************/

  public static function provideInBitmaskPositiveData() {
    return array(
      array(0, 0),
      array(1, 3),
      array(2, 6),
      array(2, 7),
      array(1, 129)
    );
  }

  public static function provideInBitmaskNegativeData() {
    return array(
      array(1, 0),
      array(1, 6),
      array(2, 4),
      array(2, 128)
    );
  }

  public static function provideUnionData() {
    return array(
      array(1, array(1)),
      array(3, array(1, 2)),
      array(3, array(1, 2, 2)),
      array(6, array(2, 4, 0))
    );
  }
}
