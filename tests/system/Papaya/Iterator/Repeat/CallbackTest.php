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

class CallbackTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Iterator\Repeat\Callback
   */
  public function testIteration() {
    $iterator = new Callback(array($this, 'incrementToThree'), 0);
    $this->assertEquals(
      array(1, 2, 3),
      iterator_to_array($iterator)
    );
  }

  /**
   * @covers \Papaya\Iterator\Repeat\Callback
   */
  public function testIterationAfterRewind() {
    $iterator = new Callback(array($this, 'incrementToThree'), 0);
    iterator_to_array($iterator);
    $this->assertEquals(
      array(1, 2, 3),
      iterator_to_array($iterator)
    );
  }

  public function incrementToThree($value, $key) {
    $value++;
    $key++;
    if ($value < 4) {
      return array($value, $key);
    }
    return FALSE;
  }
}
