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

  /**
   * @covers \Papaya\Iterator\TraversableIterator
   */
  class TraversableIteratorTest extends \Papaya\TestCase {

    public function testConstructor() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\IteratorAggregate $traversable */
      $traversable = $this->createMock(\IteratorAggregate::class);
      $traversable
        ->expects($this->never())
        ->method('getIterator');
      $iterator = new TraversableIterator($traversable);
      $this->assertSame(
        $traversable, $iterator->getInnerIterator()
      );
    }

    public function testIteration() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\IteratorAggregate $traversable */
      $traversable = $this->createMock(\IteratorAggregate::class);
      $traversable
        ->expects($this->once())
        ->method('getIterator')
        ->willReturn(new \ArrayIterator(['42']));
      $iterator = new TraversableIterator($traversable);
      $this->assertEquals(
        ['42'], iterator_to_array($iterator)
      );
    }

    public function testIterationIsCalledOnlyOnce() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\IteratorAggregate $traversable */
      $traversable = $this->createMock(\IteratorAggregate::class);
      $traversable
        ->expects($this->once())
        ->method('getIterator')
        ->willReturn(new \ArrayIterator(['42']));
      $iterator = new TraversableIterator($traversable);
      iterator_to_array($iterator);
      $this->assertEquals(
        ['42'], iterator_to_array($iterator)
      );
    }

    public function testGetIteratorForTraversableUsingIteratorAggregate() {
      $innerIterator = new \ArrayIterator([42]);
      /** @var \PHPUnit_Framework_MockObject_MockObject|\IteratorAggregate $traversable */
      $traversable = $this->createMock(\IteratorAggregate::class);
      $traversable
        ->expects($this->once())
        ->method('getIterator')
        ->willReturn($innerIterator);
      $iterator = new TraversableIterator($traversable);
      $this->assertEquals(
        $innerIterator, $iterator->getIteratorForTraversable()
      );
    }

    public function testGetIteratorForTraversableUsingIterator() {
      $traversable = $this->createMock(\Iterator::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|\IteratorAggregate $traversable */
      $iterator = new TraversableIterator($traversable);
      $this->assertEquals(
        $traversable, $iterator->getIteratorForTraversable()
      );
    }

    public function testGetIteratorForTraversableUsingArray() {
      $traversable = ['one', 'two'];
      $iterator = new TraversableIterator($traversable);
      $this->assertInstanceOf(
        'ArrayIterator', $innerIterator = $iterator->getIteratorForTraversable()
      );
      $this->assertEquals(
        ['one', 'two'], iterator_to_array($iterator)
      );
    }

    public function testGetIteratorForTraversable() {
      $document = new \Papaya\XML\Document();
      $traversable = $document->appendElement('sample')->childNodes;
      if (!$document->appendElement('sample')->childNodes instanceof \Traversable) {
        $this->markTestSkipped('Old PHP - DOMNodelist does not implement Traversable (Bug)');
      }
      $iterator = new TraversableIterator($traversable);
      /** @var \IteratorIterator $innerIterator */
      $innerIterator = $iterator->getIteratorForTraversable();
      $this->assertInstanceOf(\Iterator::class, $innerIterator);
    }
  }
}
