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

namespace Papaya\Iterator\Repeat;
require_once __DIR__.'/../../../../bootstrap.php';

class IncrementTest extends \Papaya\TestCase {

  /**
   * @covers       \Papaya\Iterator\Repeat\Increment::__construct
   * @covers       \Papaya\Iterator\Repeat\Increment::increment
   * @dataProvider provideLimits
   * @param array $expected
   * @param int $minimum
   * @param int $maximum
   * @param int $step
   */
  public function testIteration($expected, $minimum, $maximum, $step) {
    $iterator = new Increment($minimum, $maximum, $step);
    $this->assertEquals(
      $expected,
      iterator_to_array($iterator)
    );
  }

  /**
   * @covers \Papaya\Iterator\Repeat\Increment::__construct
   * @covers \Papaya\Iterator\Repeat\Increment::increment
   */
  public function testIterationWithAssocMode() {
    $iterator = new Increment(
      0, 100, 10, Increment::MODE_ASSOC
    );
    $this->assertEquals(
      array(
        0 => 0,
        10 => 10,
        20 => 20,
        30 => 30,
        40 => 40,
        50 => 50,
        60 => 60,
        70 => 70,
        80 => 80,
        90 => 90,
        100 => 100
      ),
      iterator_to_array($iterator)
    );
  }

  public static function provideLimits() {
    return array(
      'single entry' => array(
        array(42), 42, 42, 1
      ),
      'two entries, 1 to 2' => array(
        array(1, 2), 1, 2, 1
      ),
      'two entries, 1 to 3, step 2' => array(
        array(1, 3), 1, 3, 2
      ),
      'three entries, 0 to 5' => array(
        array(0, 1, 2, 3, 4, 5), 0, 5, 1
      )
    );
  }
}
