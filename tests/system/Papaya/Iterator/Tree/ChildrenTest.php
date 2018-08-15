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

class PapayaIteratorTreeChildrenTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Iterator\Tree\Children
  */
  public function testIterateRoot() {
    $iterator = $this->getIteratorFixture();
    $this->assertEquals(
      array(
        1 => 'one', 2 => 'two'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers \Papaya\Iterator\Tree\Children
  */
  public function testIterateLeafs() {
    $iterator = new \RecursiveIteratorIterator($this->getIteratorFixture());
    $this->assertEquals(
      array(
        3 => 'three', 2 => 'two'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers \Papaya\Iterator\Tree\Children
  */
  public function testIterateAll() {
    $iterator = new \RecursiveIteratorIterator(
      $this->getIteratorFixture(), \RecursiveIteratorIterator::SELF_FIRST
    );
    $this->assertEquals(
      array(
        1 => 'one', 3 => 'three', 2 => 'two'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * A simple test data tree
  *
  * 1 => 'one'
  *   3 => 'tree'
  * 2 => 'two'
  *
  * The element id 4 is included int the children ids to simulate a missing element.
  *
  * @return \Papaya\Iterator\Tree\Children
  */
  public function getIteratorFixture() {
    return new \Papaya\Iterator\Tree\Children(
      array(
        1 => 'one',
        2 => 'two',
        3 => 'three'
      ),
      array(
        0 => array(1, 4, 2),
        1 => array(3)
      )
    );
  }
}
