<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaIteratorGeneratorTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorGenerator::__construct
  * @covers PapayaIteratorGenerator::getIterator
  * @covers PapayaIteratorGenerator::createIterator
  */
  public function testGetIteratorWithoutData() {
    $iterator = new PapayaIteratorGenerator(
      array($this, 'callbackReturnArgument')
    );
    $this->assertInstanceOf('EmptyIterator', $iterator->getIterator());
  }

  /**
  * @covers PapayaIteratorGenerator::__construct
  * @covers PapayaIteratorGenerator::getIterator
  * @covers PapayaIteratorGenerator::createIterator
  */
  public function testGetIteratorWithArray() {
    $iterator = new PapayaIteratorGenerator(
      array($this, 'callbackReturnArgument'), array(array('foo', 'bar'))
    );
    $this->assertEquals(
      new ArrayIterator(array('foo', 'bar')), $iterator->getIterator()
    );
  }

  /**
  * @covers PapayaIteratorGenerator::__construct
  * @covers PapayaIteratorGenerator::getIterator
  * @covers PapayaIteratorGenerator::createIterator
  */
  public function testGetIteratorWithIterator() {
    $iterator = new PapayaIteratorGenerator(
      array($this, 'callbackReturnArgument'), array($innerIterator = new EmptyIterator)
    );
    $this->assertSame(
      $innerIterator, $iterator->getIterator()
    );
  }

  /**
  * @covers PapayaIteratorGenerator::__construct
  * @covers PapayaIteratorGenerator::getIterator
  * @covers PapayaIteratorGenerator::createIterator
  */
  public function testGetIteratorWithIteratorAggregate() {
    $wrapper = $this->createMock(IteratorAggregate::class);
    $wrapper
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array('foo'))));

    $iterator = new PapayaIteratorGenerator(
      array($this, 'callbackReturnArgument'),
      array($wrapper)
    );
    $this->assertEquals(
      array('foo'), iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorGenerator::__construct
  * @covers PapayaIteratorGenerator::getIterator
  */
  public function testMultipleCallsCreateIteratorOnlyOnce() {
    $iterator = new PapayaIteratorGenerator(
      array($this, 'callbackReturnArgument')
    );
    $this->assertInstanceOf('EmptyIterator', $innerIterator = $iterator->getIterator());
    $this->assertSame($innerIterator, $iterator->getIterator());
  }

  public function callbackReturnArgument($traversable = NULL) {
    return $traversable;
  }
}
