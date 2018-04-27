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

class PapayaIteratorRepeatDecrementTest extends PapayaTestCase {

  /**
   * @covers PapayaIteratorRepeatDecrement::__construct
   * @covers PapayaIteratorRepeatDecrement::decrement
   * @dataProvider provideLimits
   * @param array $expected
   * @param int $minimum
   * @param int $maximum
   * @param int $step
   */
  public function testIteration($expected, $minimum, $maximum, $step) {
    $iterator = new PapayaIteratorRepeatDecrement($minimum, $maximum, $step);
    $this->assertEquals(
      $expected,
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorRepeatDecrement::__construct
  * @covers PapayaIteratorRepeatDecrement::decrement
  */
  public function testIterationWithAssocMode() {
    $iterator = new PapayaIteratorRepeatDecrement(
      100, 0, 10, PapayaIteratorRepeatDecrement::MODE_ASSOC
    );
    $this->assertEquals(
      array(
        100 => 100,
        90 => 90,
        80 => 80,
        70 => 70,
        60 => 60,
        50 => 50,
        40 => 40,
        30 => 30,
        20 => 20,
        10 => 10,
        0 => 0
      ),
      iterator_to_array($iterator)
    );
  }

  public static function provideLimits() {
    return array(
      'single entry' => array(
        array(42), 42, 42, 1
      ),
      'two entries, 2 to 1' => array(
        array(2, 1), 2, 1, 1
      ),
      'two entries, 3 to 1, step 2' => array(
        array(3, 1), 3, 1, 2
      ),
      'three entries, 0 to 5' => array(
        array(5, 4, 3, 2, 1, 0), 5, 0, 1
      )
    );
  }
}
