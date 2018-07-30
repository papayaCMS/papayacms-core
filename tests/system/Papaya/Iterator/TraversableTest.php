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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaIteratorTraversableTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Iterator\Traversable::__construct
  * @covers \Papaya\Iterator\Traversable::getInnerIterator
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->never())
      ->method('getIterator');
    $iterator = new \Papaya\Iterator\Traversable($traversable);
    $this->assertSame(
      $traversable, $iterator->getInnerIterator()
    );
  }

  /**
  * @covers \Papaya\Iterator\Traversable::getIteratorForTraversable
  * @covers \Papaya\Iterator\Traversable::current
  * @covers \Papaya\Iterator\Traversable::key
  * @covers \Papaya\Iterator\Traversable::next
  * @covers \Papaya\Iterator\Traversable::valid
  * @covers \Papaya\Iterator\Traversable::rewind
  */
  public function testIteration() {
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array('42'))));
    $iterator = new \Papaya\Iterator\Traversable($traversable);
    $this->assertEquals(
      array('42'), iterator_to_array($iterator)
    );
  }

  /**
  * @covers \Papaya\Iterator\Traversable::getIteratorForTraversable
  */
  public function testIterationIsCalledOnlyOnce() {
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array('42'))));
    $iterator = new \Papaya\Iterator\Traversable($traversable);
    iterator_to_array($iterator);
    $this->assertEquals(
      array('42'), iterator_to_array($iterator)
    );
  }

  /**
  * @covers \Papaya\Iterator\Traversable::getIteratorForTraversable
  */
  public function testGetIteratorForTraversableUsingIteratorAggregate() {
    $innerIterator = new ArrayIterator(array(42));
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue($innerIterator));
    $iterator = new \Papaya\Iterator\Traversable($traversable);
    $this->assertEquals(
      $innerIterator, $iterator->getIteratorForTraversable()
    );
  }

  /**
  * @covers \Papaya\Iterator\Traversable::getIteratorForTraversable
  */
  public function testGetIteratorForTraversableUsingIterator() {
    $traversable = $this->createMock(Iterator::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $iterator = new \Papaya\Iterator\Traversable($traversable);
    $this->assertEquals(
      $traversable, $iterator->getIteratorForTraversable()
    );
  }

  /**
  * @covers \Papaya\Iterator\Traversable::getIteratorForTraversable
  */
  public function testGetIteratorForTraversableUsingArray() {
    $traversable = array('one', 'two');
    $iterator = new \Papaya\Iterator\Traversable($traversable);
    $this->assertInstanceOf(
      'ArrayIterator', $innerIterator = $iterator->getIteratorForTraversable()
    );
    $this->assertEquals(
      array('one', 'two'), iterator_to_array($iterator)
    );
  }

  /**
  * @covers \Papaya\Iterator\Traversable::getIteratorForTraversable
  */
  public function testGetIteratorForTraversable() {
    $document = new \PapayaXmlDocument();
    $traversable = $document->appendElement('sample')->childNodes;
    if (!$document->appendElement('sample')->childNodes instanceof Traversable) {
      $this->markTestSkipped('Old PHP - DOMNodelist does not implement Traversable (Bug)');
    }
    $iterator = new \Papaya\Iterator\Traversable($traversable);
    /** @var IteratorIterator $innerIterator */
    $innerIterator = $iterator->getIteratorForTraversable();
    $this->assertInstanceOf('IteratorIterator', $innerIterator);
    $this->assertSame($traversable, $innerIterator->getInnerIterator());
  }
}

interface Traversable_SampleInterface extends Traversable {

}
