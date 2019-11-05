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

namespace Papaya\Iterator {

  use UnexpectedValueException;

  /**
   * @covers \Papaya\Iterator\RecursiveTraversableIterator
   */
  class RecursiveTraversableIteratorTest extends \PHPUnit_Framework_TestCase {

    public function testRecursiveTraversableIteratorForArray() {
      $iterator = new RecursiveTraversableIterator([0, 1, [2, 3]], RecursiveTraversableIterator::LEAVES_ONLY);
      $this->assertEquals(
        [0, 1, 2, 3],
        iterator_to_array($iterator, FALSE)
      );
    }

    public function testRecursiveTraversableIteratorForInvalidTraversableExpectingException() {
      $this->expectException(UnexpectedValueException::class);
      $this->expectExceptionMessage('Unexpected value type: Expected "array, Traversable" but "NULL" given.');
      new RecursiveTraversableIterator(NULL, RecursiveTraversableIterator::LEAVES_ONLY);
    }

    public function testRecursiveTraversableIteratorForNonRecursiveTraversableExpectingException() {
      $iterator = new RecursiveTraversableIterator(new \ArrayIterator([]), RecursiveTraversableIterator::LEAVES_ONLY);
      $this->expectException(UnexpectedValueException::class);
      $this->expectExceptionMessage('Could not get RecursiveIterator for/from provided ArrayIterator.');
        iterator_to_array($iterator, FALSE);
    }

    public function testRecursiveTraversableIteratorForRecursiveIterator() {
      $iterator = new RecursiveTraversableIterator(
        new \RecursiveArrayIterator([0, 1, [2, 3]]), RecursiveTraversableIterator::LEAVES_ONLY
      );
      $this->assertEquals(
        [0, 1, 2, 3],
        iterator_to_array($iterator, FALSE)
      );
    }

    public function testRecursiveTraversableIteratorForIteratorAggregate() {
      $iterator = new RecursiveTraversableIterator(
        new RecursiveIteratorAggregate_TestStub(), RecursiveTraversableIterator::LEAVES_ONLY
      );
      $this->assertEquals(
        [0, 1, 2, 3],
        iterator_to_array($iterator, FALSE)
      );
    }

    public function testRecursiveTraversableIteratorForArraySelfFirst() {
      $iterator = new RecursiveTraversableIterator([0, 1, [2, 3]], RecursiveTraversableIterator::SELF_FIRST);
      $this->assertEquals(
        [0, 1, [2, 3], 2, 3],
        iterator_to_array($iterator, FALSE)
      );
    }

    public function testRecursiveTraversableIteratorForArrayChildFirst() {
      $iterator = new RecursiveTraversableIterator([0, 1, [2, 3]], RecursiveTraversableIterator::CHILD_FIRST);
      $this->assertEquals(
        [0, 1, 2, 3, [2, 3]],
        iterator_to_array($iterator, FALSE)
      );
    }

    public function testGetDepthDelegation() {
      $iterator = new RecursiveTraversableIterator([0, 1, [2, 3]], RecursiveTraversableIterator::LEAVES_ONLY);
      $result = [];
      foreach ($iterator as $value) {
        $result[] = ['value' => $value, 'depth' => $iterator->getDepth()];
      }
      $this->assertSame(
        [
          ['value' => 0, 'depth' => 0],
          ['value' => 1, 'depth' => 0],
          ['value' => 2, 'depth' => 1],
          ['value' => 3, 'depth' => 1],
        ],
        $result
      );
    }

    public function testGetMaxDepthAfterSet() {
      $iterator = new RecursiveTraversableIterator([0, 1, [2, 3]], RecursiveTraversableIterator::LEAVES_ONLY);
      $iterator->setMaxDepth(4);
      $this->assertSame(
        4,
        $iterator->getMaxDepth()
      );
    }

    public function testGetSubIteratorDelegation() {
      $iterator = new RecursiveTraversableIterator([0, 1, [2, 3]], RecursiveTraversableIterator::LEAVES_ONLY);
      $result = [];
      foreach ($iterator as $value) {
        $result[] = [
          'value' => $value,
          'l0' => ($iterator->getSubIterator(0)) instanceof \RecursiveIterator,
          'l1' => ($iterator->getSubIterator(1)) instanceof \RecursiveIterator
        ];
      }
      $this->assertSame(
        [
          ['value' => 0, 'l0' => TRUE, 'l1' => FALSE],
          ['value' => 1, 'l0' => TRUE, 'l1' => FALSE],
          ['value' => 2, 'l0' => TRUE, 'l1' => TRUE],
          ['value' => 3, 'l0' => TRUE, 'l1' => TRUE],
        ],
        $result
      );
    }
  }

  class RecursiveIteratorAggregate_TestStub implements \IteratorAggregate {
    public function getIterator() {
      return new \RecursiveArrayIterator([0, 1, [2, 3]]);
    }
  }
}
