<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaIteratorMultipleTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorMultiple::__construct
  */
  public function testConstructor() {
    $iterator = new PapayaIteratorMultiple();
    $this->assertEquals(0, $iterator->countIterators());
  }

  /**
  * @covers PapayaIteratorMultiple::__construct
  * @covers PapayaIteratorMultiple::setFlags
  * @covers PapayaIteratorMultiple::getFlags
  */
  public function testConstructorWithFlags() {
    $iterator = new PapayaIteratorMultiple(PapayaIteratorMultiple::MIT_KEYS_ASSOC);
    $this->assertEquals(PapayaIteratorMultiple::MIT_KEYS_ASSOC, $iterator->getFlags());
  }

  /**
  * @covers PapayaIteratorMultiple::__construct
  * @covers PapayaIteratorMultiple::setFlags
  * @covers PapayaIteratorMultiple::getFlags
  */
  public function testConstructorWithFlagsAndIterators() {
    $iterator = new PapayaIteratorMultiple(
      PapayaIteratorMultiple::MIT_KEYS_ASSOC,
      new ArrayIterator(),
      new ArrayIterator()
    );
    $this->assertEquals(PapayaIteratorMultiple::MIT_KEYS_ASSOC, $iterator->getFlags());
    $this->assertEquals(2, $iterator->countIterators());
  }

  /**
  * @covers PapayaIteratorMultiple::__construct
  * @covers PapayaIteratorMultiple::setFlags
  * @covers PapayaIteratorMultiple::getFlags
  */
  public function testConstructorWithOneIterator() {
    $iterator = new PapayaIteratorMultiple(
      new ArrayIterator()
    );
    $this->assertEquals(1, $iterator->countIterators());
  }

  /**
  * @covers PapayaIteratorMultiple::attachIterators
  */
  public function testAttachIterators() {
    $iterator = new PapayaIteratorMultiple();
    $iterator->attachIterators(
      new ArrayIterator(),
      new ArrayIterator()
    );
    $this->assertEquals(2, $iterator->countIterators());
  }

  /**
  * @covers PapayaIteratorMultiple::attachIterator
  */
  public function testAttachIterator() {
    $iterator = new PapayaIteratorMultiple();
    $iterator->attachIterator(new ArrayIterator());
    $this->assertEquals(1, $iterator->countIterators());
  }

  /**
  * @covers PapayaIteratorMultiple::attachIterator
  * @covers PapayaIteratorMultiple::getIteratorIdentifier
  */
  public function testAttachIteratorWithIteratorAggregate() {
    $traversable = $this->createMock(IteratorAggregate::class);
    $iterator = new PapayaIteratorMultiple();
    $iterator->attachIterator($traversable);
    $this->assertTrue($iterator->containsIterator($traversable));
  }

  /**
  * @covers PapayaIteratorMultiple::attachIterators
  */
  public function testAttachIteratorsWithTwoIteratorAggregate() {
    $traversableOne = $this->createMock(IteratorAggregate::class);
    $traversableTwo = $this->createMock(IteratorAggregate::class);
    $iterator = new PapayaIteratorMultiple();
    $iterator->attachIterators($traversableOne, $traversableTwo);
    $this->assertEquals(2, $iterator->countIterators());
    $this->assertTrue($iterator->containsIterator($traversableOne));
    $this->assertTrue($iterator->containsIterator($traversableTwo));
  }

  /**
  * @covers PapayaIteratorMultiple::attachIterator
  */
  public function testAttachIteratorWithArray() {
    $iterator = new PapayaIteratorMultiple();
    $iterator->attachIterator($array = array());
    $this->assertTrue($iterator->containsIterator($array));
  }

  /**
  * @covers PapayaIteratorMultiple::containsIterator
  */
  public function testContainsIteratorExpectingTrue() {
    $iterator = new PapayaIteratorMultiple($innerIterator = new ArrayIterator());
    $this->assertTrue($iterator->containsIterator($innerIterator));
  }

  /**
  * @covers PapayaIteratorMultiple::containsIterator
  */
  public function testContainsIteratorWithArrayExpectingTrue() {
    $iterator = new PapayaIteratorMultiple($array = array('foo'));
    $this->assertTrue($iterator->containsIterator($array));
  }

  /**
  * @covers PapayaIteratorMultiple::containsIterator
  */
  function testContainsIteratorExpectingFalse() {
    $iterator = new PapayaIteratorMultiple();
    $innerIterator = new ArrayIterator();
    $this->assertFalse($iterator->containsIterator($innerIterator));
  }

  /**
  * @covers PapayaIteratorMultiple::containsIterator
  */
  public function testContainsIteratorWithArrayExpectingFalse() {
    $iterator = new PapayaIteratorMultiple(array('foo'));
    $this->assertFalse($iterator->containsIterator(array('bar')));
  }

  /**
  * @covers PapayaIteratorMultiple::detachIterator
  */
  public function testDetachIterator() {
    $iterator = new PapayaIteratorMultiple($innerIterator = new ArrayIterator());
    $iterator->detachIterator($innerIterator);
    $this->assertFalse($iterator->containsIterator($innerIterator));
  }

  /**
  * @covers PapayaIteratorMultiple::getInnerIterator
  */
  public function testGetInnerIterator() {
    $iterator = new PapayaIteratorMultiple($innerIterator = new ArrayIterator());
    $this->assertSame($innerIterator, $iterator->getInnerIterator());
  }

  /**
  * @covers PapayaIteratorMultiple::rewind
  * @covers PapayaIteratorMultiple::key
  * @covers PapayaIteratorMultiple::current
  * @covers PapayaIteratorMultiple::next
  * @covers PapayaIteratorMultiple::valid
  */
  public function testIteration() {
    $iterator = new PapayaIteratorMultiple(
      new ArrayIterator(array(21 => 'One')),
      new ArrayIterator(array(42 => 'Two', 84 => 'Three'))
    );
    $this->assertEquals(
      array('One', 'Two', 'Three'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers PapayaIteratorMultiple::rewind
  * @covers PapayaIteratorMultiple::key
  * @covers PapayaIteratorMultiple::current
  * @covers PapayaIteratorMultiple::next
  * @covers PapayaIteratorMultiple::valid
  */
  public function testIterationWithKeys() {
    $iterator = new PapayaIteratorMultiple(
      PapayaIteratorMultiple::MIT_KEYS_ASSOC,
      new ArrayIterator(array(21 => 'One')),
      new ArrayIterator(array(42 => 'Two', 84 => 'Three'))
    );
    $this->assertEquals(
      array(21 => 'One', 42 => 'Two', 84 => 'Three'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers PapayaIteratorMultiple::rewind
  * @covers PapayaIteratorMultiple::key
  * @covers PapayaIteratorMultiple::current
  * @covers PapayaIteratorMultiple::next
  * @covers PapayaIteratorMultiple::valid
  */
  public function testIterationWithTraversable() {
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array(21 => 'One'))));

    $iterator = new PapayaIteratorMultiple(
      PapayaIteratorMultiple::MIT_KEYS_ASSOC,
      $traversable
    );
    $this->assertEquals(
      array(21 => 'One'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers PapayaIteratorMultiple::rewind
  * @covers PapayaIteratorMultiple::key
  * @covers PapayaIteratorMultiple::current
  * @covers PapayaIteratorMultiple::next
  * @covers PapayaIteratorMultiple::valid
  */
  public function testWithSecondIteratorIsEmpty() {
    $iterator = new PapayaIteratorMultiple(
      new ArrayIterator(array(21 => 'One')),
      new ArrayIterator()
    );
    $this->assertEquals(
      array('One'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers PapayaIteratorMultiple::rewind
  * @covers PapayaIteratorMultiple::key
  * @covers PapayaIteratorMultiple::current
  * @covers PapayaIteratorMultiple::next
  * @covers PapayaIteratorMultiple::valid
  */
  public function testWithFirstIteratorIsEmpty() {
    $iterator = new PapayaIteratorMultiple(
      new ArrayIterator(),
      new ArrayIterator(array(21 => 'One'))
    );
    $this->assertEquals(
      array('One'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers PapayaIteratorMultiple::countIterators
  */
  public function testCountIterators() {
    $iterator = new PapayaIteratorMultiple(new ArrayIterator());
    $this->assertEquals(1, $iterator->countIterators());
  }
}
