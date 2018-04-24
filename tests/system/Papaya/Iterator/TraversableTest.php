<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaIteratorTraversableTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorTraversable::__construct
  * @covers PapayaIteratorTraversable::getInnerIterator
  */
  public function testConstructor() {
    $traversable = $this->getMock('IteratorAggregate');
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
    $traversable = $this->getMock('IteratorAggregate');
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
    $traversable = $this->getMock('IteratorAggregate');
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
    $traversable = $this->getMock('IteratorAggregate');
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
    $traversable = $this->getMock('Iterator');
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
    $this->assertInstanceOf(
      'IteratorIterator', $innerIterator = $iterator->getIteratorForTraversable()
    );
    $this->assertSame($traversable, $innerIterator->getInnerIterator());
  }
}

interface Traversable_SampleInterface extends Traversable {

}
