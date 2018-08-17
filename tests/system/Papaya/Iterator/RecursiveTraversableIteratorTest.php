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

namespace Papaya\Iterator;

class RecursiveTraversableIteratorTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \Papaya\Iterator\RecursiveTraversableIterator
   */
  public function testRecursiveTraversableIteratorForArray() {
    $iterator = new RecursiveTraversableIterator([0, 1, [2, 3]], RecursiveTraversableIterator::LEAVES_ONLY);
    $this->assertEquals(
      [0,1,2,3],
      iterator_to_array($iterator, FALSE)
    );
  }

  /**
   * @covers \Papaya\Iterator\RecursiveTraversableIterator
   */
  public function testRecursiveTraversableIteratorForRecursiveIterator() {
    $iterator = new RecursiveTraversableIterator(new \RecursiveArrayIterator([0, 1, [2, 3]]), RecursiveTraversableIterator::LEAVES_ONLY);
    $this->assertEquals(
      [0,1,2,3],
      iterator_to_array($iterator, FALSE)
    );
  }

  /**
   * @covers \Papaya\Iterator\RecursiveTraversableIterator
   */
  public function testRecursiveTraversableIteratorForIteratorAggregate() {
    $iterator = new RecursiveTraversableIterator(new RecursiveIteratorAggregate_TestStub(), RecursiveTraversableIterator::LEAVES_ONLY);
    $this->assertEquals(
      [0,1,2,3],
      iterator_to_array($iterator, FALSE)
    );
  }

}

class RecursiveIteratorAggregate_TestStub implements \IteratorAggregate {
  public function getIterator() {
    return new \RecursiveArrayIterator([0, 1, [2, 3]]);
  }
}
