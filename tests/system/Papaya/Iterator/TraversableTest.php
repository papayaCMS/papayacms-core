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

class PapayaIteratorTraversableTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorTraversable::__construct
  * @covers PapayaIteratorTraversable::getInnerIterator
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->never())
      ->method('getIterator');
    $iterator = new PapayaIteratorTraversable($traversable);
    $this->assertSame(
      $traversable, $iterator->getInnerIterator()
    );
  }

  /**
  * @covers PapayaIteratorTraversable::getIteratorForTraversable
  * @covers PapayaIteratorTraversable::current
  * @covers PapayaIteratorTraversable::key
  * @covers PapayaIteratorTraversable::next
  * @covers PapayaIteratorTraversable::valid
  * @covers PapayaIteratorTraversable::rewind
  */
  public function testIteration() {
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array('42'))));
    $iterator = new PapayaIteratorTraversable($traversable);
    $this->assertEquals(
      array('42'), iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorTraversable::getIteratorForTraversable
  */
  public function testIterationIsCalledOnlyOnce() {
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array('42'))));
    $iterator = new PapayaIteratorTraversable($traversable);
    iterator_to_array($iterator);
    $this->assertEquals(
      array('42'), iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorTraversable::getIteratorForTraversable
  */
  public function testGetIteratorForTraversableUsingIteratorAggregate() {
    $innerIterator = new ArrayIterator(array(42));
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue($innerIterator));
    $iterator = new PapayaIteratorTraversable($traversable);
    $this->assertEquals(
      $innerIterator, $iterator->getIteratorForTraversable()
    );
  }

  /**
  * @covers PapayaIteratorTraversable::getIteratorForTraversable
  */
  public function testGetIteratorForTraversableUsingIterator() {
    $traversable = $this->createMock(Iterator::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $iterator = new PapayaIteratorTraversable($traversable);
    $this->assertEquals(
      $traversable, $iterator->getIteratorForTraversable()
    );
  }

  /**
  * @covers PapayaIteratorTraversable::getIteratorForTraversable
  */
  public function testGetIteratorForTraversableUsingArray() {
    $traversable = array('one', 'two');
    $iterator = new PapayaIteratorTraversable($traversable);
    $this->assertInstanceOf(
      'ArrayIterator', $innerIterator = $iterator->getIteratorForTraversable()
    );
    $this->assertEquals(
      array('one', 'two'), iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorTraversable::getIteratorForTraversable
  */
  public function testGetIteratorForTraversable() {
    $dom = new PapayaXmlDocument();
    $traversable = $dom->appendElement('sample')->childNodes;
    if (!$dom->appendElement('sample')->childNodes instanceof Traversable) {
      $this->markTestSkipped('Old PHP - DOMNodelist does not implement Traversable (Bug)');
    }
    $iterator = new PapayaIteratorTraversable($traversable);
    /** @var IteratorIterator $innerIterator */
    $innerIterator = $iterator->getIteratorForTraversable();
    $this->assertInstanceOf('IteratorIterator', $innerIterator);
    $this->assertSame($traversable, $innerIterator->getInnerIterator());
  }
}

interface Traversable_SampleInterface extends Traversable {

}
