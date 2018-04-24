<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaIteratorCachingTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorCaching::__construct
  * @covers PapayaIteratorCaching::setCallback
  */
  public function testConstructor() {
    $iterator = new PapayaIteratorCaching($innerIterator = new EmptyIterator());
    $this->assertSame($innerIterator, $iterator->getInnerIterator());
  }

  /**
  * @covers PapayaIteratorCaching::__construct
  * @covers PapayaIteratorCaching::setCallback
  * @covers PapayaIteratorCaching::getCallback
  */
  public function testConstructorWithCallback() {
    $iterator = new PapayaIteratorCaching(
      $innerIterator = new EmptyIterator(),
      array($this, 'callbackThrowException')
    );
    $this->assertEquals(
      array($this, 'callbackThrowException'),
      $iterator->getCallback()
    );
  }

  /**
  * @covers PapayaIteratorCaching::__construct
  */
  public function testConstructorWithTraversable() {
    $traversable = $this->getMock('IteratorAggregate');
    $iterator = new PapayaIteratorCaching(
      $traversable,
      array($this, 'callbackThrowException')
    );
    $this->assertSame($traversable, $iterator->getInnerIterator()->getInnerIterator());
  }

  /**
  * @covers PapayaIteratorCaching::__construct
  * @covers PapayaIteratorCaching::setCallback
  */
  public function testConstructorWithInvalidCallbackExpectingException() {
    $this->setExpectedException(
      'InvalidArgumentException',
      'Provided callback parameter is not valid.'
    );
    $iterator = new PapayaIteratorCaching(
      $innerIterator = new EmptyIterator(),
      new stdClass()
    );
  }

  /**
  * @covers PapayaIteratorCaching::getCache
  * @covers PapayaIteratorCaching::rewind
  */
  public function testIterationCallsCallback() {
    $this->_arrayObject = new ArrayObject();
    $iterator = new PapayaIteratorCaching(
      $this->_arrayObject,
      array($this, 'callbackFillCache')
    );
    $this->assertEquals(
      array(1, 2, 3),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorCaching::getCache
  * @covers PapayaIteratorCaching::rewind
  */
  public function testIterationCallsCallbackOnlyOnce() {
    $this->_arrayObject = new ArrayObject();
    $iterator = new PapayaIteratorCaching(
      $this->_arrayObject,
      array($this, 'callbackFillCache')
    );
    iterator_to_array($iterator);
    $this->assertEquals(
      array(1, 2, 3),
      iterator_to_array($iterator)
    );
  }

  public function callbackThrowException() {
    throw new LogicException('Constructor should not execute getCache callback.');
  }

  public function callbackFillCache() {
    $this->_arrayObject->append(count($this->_arrayObject) + 1);
    $this->_arrayObject->append(count($this->_arrayObject) + 1);
    $this->_arrayObject->append(count($this->_arrayObject) + 1);
  }
}
