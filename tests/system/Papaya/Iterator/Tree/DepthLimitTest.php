<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Iterator\Tree;
require_once __DIR__.'/../../../../bootstrap.php';

class DepthLimitTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Iterator\Tree\DepthLimit
   */
  public function testWithLimitOne() {
    $iterator = new \RecursiveIteratorIterator(
      new DepthLimit($this->getIteratorFixture(), 1),\RecursiveIteratorIterator::SELF_FIRST
    );
    $this->assertEquals(
      [
        1 => 'A',
        2 => 'B'
      ],
      iterator_to_array($iterator)
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\DepthLimit
   */
  public function testWithLimitTwo() {
    $iterator = new \RecursiveIteratorIterator(
      new DepthLimit($this->getIteratorFixture(), 2), \RecursiveIteratorIterator::SELF_FIRST
    );
    $this->assertEquals(
      [
        1 => 'A',
        3 => 'A.1',
        2 => 'B',
      ],
      iterator_to_array($iterator)
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\DepthLimit
   */
  public function testWithLimitThree() {
    $iterator = new \RecursiveIteratorIterator(
      new DepthLimit($this->getIteratorFixture(), 3), \RecursiveIteratorIterator::SELF_FIRST
    );
    $this->assertEquals(
      [
        1 => 'A',
        3 => 'A.1',
        4 => 'A.1.1',
        2 => 'B',
      ],
      iterator_to_array($iterator)
    );
  }



  /**
   * @return \RecursiveIterator
   */
  public function getIteratorFixture() {
    return new Children(
      array(
        1 => 'A',
        2 => 'B',
        3 => 'A.1',
        4 => 'A.1.1'
      ),
      array(
        0 => array(1, 2),
        1 => array(3),
        3 => array(4)
      )
    );
  }
}
