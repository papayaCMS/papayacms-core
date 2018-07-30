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

class PapayaIteratorMultipleTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Iterator\Union::__construct
  */
  public function testConstructor() {
    $iterator = new \Papaya\Iterator\Union();
    $this->assertEquals(0, $iterator->countIterators());
  }

  /**
  * @covers \Papaya\Iterator\Union::__construct
  * @covers \Papaya\Iterator\Union::setFlags
  * @covers \Papaya\Iterator\Union::getFlags
  */
  public function testConstructorWithFlags() {
    $iterator = new \Papaya\Iterator\Union(\Papaya\Iterator\Union::MIT_KEYS_ASSOC);
    $this->assertEquals(\Papaya\Iterator\Union::MIT_KEYS_ASSOC, $iterator->getFlags());
  }

  /**
  * @covers \Papaya\Iterator\Union::__construct
  * @covers \Papaya\Iterator\Union::setFlags
  * @covers \Papaya\Iterator\Union::getFlags
  */
  public function testConstructorWithFlagsAndIterators() {
    $iterator = new \Papaya\Iterator\Union(
      \Papaya\Iterator\Union::MIT_KEYS_ASSOC,
      new ArrayIterator(),
      new ArrayIterator()
    );
    $this->assertEquals(\Papaya\Iterator\Union::MIT_KEYS_ASSOC, $iterator->getFlags());
    $this->assertEquals(2, $iterator->countIterators());
  }

  /**
  * @covers \Papaya\Iterator\Union::__construct
  * @covers \Papaya\Iterator\Union::setFlags
  * @covers \Papaya\Iterator\Union::getFlags
  */
  public function testConstructorWithOneIterator() {
    $iterator = new \Papaya\Iterator\Union(
      new ArrayIterator()
    );
    $this->assertEquals(1, $iterator->countIterators());
  }

  /**
  * @covers \Papaya\Iterator\Union::attachIterators
  */
  public function testAttachIterators() {
    $iterator = new \Papaya\Iterator\Union();
    $iterator->attachIterators(
      new ArrayIterator(),
      new ArrayIterator()
    );
    $this->assertEquals(2, $iterator->countIterators());
  }

  /**
  * @covers \Papaya\Iterator\Union::attachIterator
  */
  public function testAttachIterator() {
    $iterator = new \Papaya\Iterator\Union();
    $iterator->attachIterator(new ArrayIterator());
    $this->assertEquals(1, $iterator->countIterators());
  }

  /**
  * @covers \Papaya\Iterator\Union::attachIterator
  * @covers \Papaya\Iterator\Union::getIteratorIdentifier
  */
  public function testAttachIteratorWithIteratorAggregate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $iterator = new \Papaya\Iterator\Union();
    $iterator->attachIterator($traversable);
    $this->assertTrue($iterator->containsIterator($traversable));
  }

  /**
  * @covers \Papaya\Iterator\Union::attachIterators
  */
  public function testAttachIteratorsWithTwoIteratorAggregate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversableOne */
    $traversableOne = $this->createMock(IteratorAggregate::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|IteratorAggregate $traversableTwo */
    $traversableTwo = $this->createMock(IteratorAggregate::class);
    $iterator = new \Papaya\Iterator\Union();
    $iterator->attachIterators($traversableOne, $traversableTwo);
    $this->assertEquals(2, $iterator->countIterators());
    $this->assertTrue($iterator->containsIterator($traversableOne));
    $this->assertTrue($iterator->containsIterator($traversableTwo));
  }

  /**
  * @covers \Papaya\Iterator\Union::attachIterator
  */
  public function testAttachIteratorWithArray() {
    $iterator = new \Papaya\Iterator\Union();
    $iterator->attachIterator($array = array());
    $this->assertTrue($iterator->containsIterator($array));
  }

  /**
  * @covers \Papaya\Iterator\Union::containsIterator
  */
  public function testContainsIteratorExpectingTrue() {
    $iterator = new \Papaya\Iterator\Union($innerIterator = new ArrayIterator());
    $this->assertTrue($iterator->containsIterator($innerIterator));
  }

  /**
  * @covers \Papaya\Iterator\Union::containsIterator
  */
  public function testContainsIteratorWithArrayExpectingTrue() {
    $iterator = new \Papaya\Iterator\Union($array = array('foo'));
    $this->assertTrue($iterator->containsIterator($array));
  }

  /**
  * @covers \Papaya\Iterator\Union::containsIterator
  */
  public function testContainsIteratorExpectingFalse() {
    $iterator = new \Papaya\Iterator\Union();
    $innerIterator = new ArrayIterator();
    $this->assertFalse($iterator->containsIterator($innerIterator));
  }

  /**
  * @covers \Papaya\Iterator\Union::containsIterator
  */
  public function testContainsIteratorWithArrayExpectingFalse() {
    $iterator = new \Papaya\Iterator\Union(array('foo'));
    $this->assertFalse($iterator->containsIterator(array('bar')));
  }

  /**
  * @covers \Papaya\Iterator\Union::detachIterator
  */
  public function testDetachIterator() {
    $iterator = new \Papaya\Iterator\Union($innerIterator = new ArrayIterator());
    $iterator->detachIterator($innerIterator);
    $this->assertFalse($iterator->containsIterator($innerIterator));
  }

  /**
  * @covers \Papaya\Iterator\Union::getInnerIterator
  */
  public function testGetInnerIterator() {
    $iterator = new \Papaya\Iterator\Union($innerIterator = new ArrayIterator());
    $this->assertSame($innerIterator, $iterator->getInnerIterator());
  }

  /**
  * @covers \Papaya\Iterator\Union::rewind
  * @covers \Papaya\Iterator\Union::key
  * @covers \Papaya\Iterator\Union::current
  * @covers \Papaya\Iterator\Union::next
  * @covers \Papaya\Iterator\Union::valid
  */
  public function testIteration() {
    $iterator = new \Papaya\Iterator\Union(
      new ArrayIterator(array(21 => 'One')),
      new ArrayIterator(array(42 => 'Two', 84 => 'Three'))
    );
    $this->assertEquals(
      array('One', 'Two', 'Three'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers \Papaya\Iterator\Union::rewind
  * @covers \Papaya\Iterator\Union::key
  * @covers \Papaya\Iterator\Union::current
  * @covers \Papaya\Iterator\Union::next
  * @covers \Papaya\Iterator\Union::valid
  */
  public function testIterationWithKeys() {
    $iterator = new \Papaya\Iterator\Union(
      \Papaya\Iterator\Union::MIT_KEYS_ASSOC,
      new ArrayIterator(array(21 => 'One')),
      new ArrayIterator(array(42 => 'Two', 84 => 'Three'))
    );
    $this->assertEquals(
      array(21 => 'One', 42 => 'Two', 84 => 'Three'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers \Papaya\Iterator\Union::rewind
  * @covers \Papaya\Iterator\Union::key
  * @covers \Papaya\Iterator\Union::current
  * @covers \Papaya\Iterator\Union::next
  * @covers \Papaya\Iterator\Union::valid
  */
  public function testIterationWithTraversable() {
    $traversable = $this->createMock(IteratorAggregate::class);
    $traversable
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array(21 => 'One'))));

    $iterator = new \Papaya\Iterator\Union(
      \Papaya\Iterator\Union::MIT_KEYS_ASSOC,
      $traversable
    );
    $this->assertEquals(
      array(21 => 'One'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers \Papaya\Iterator\Union::rewind
  * @covers \Papaya\Iterator\Union::key
  * @covers \Papaya\Iterator\Union::current
  * @covers \Papaya\Iterator\Union::next
  * @covers \Papaya\Iterator\Union::valid
  */
  public function testWithSecondIteratorIsEmpty() {
    $iterator = new \Papaya\Iterator\Union(
      new ArrayIterator(array(21 => 'One')),
      new ArrayIterator()
    );
    $this->assertEquals(
      array('One'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers \Papaya\Iterator\Union::rewind
  * @covers \Papaya\Iterator\Union::key
  * @covers \Papaya\Iterator\Union::current
  * @covers \Papaya\Iterator\Union::next
  * @covers \Papaya\Iterator\Union::valid
  */
  public function testWithFirstIteratorIsEmpty() {
    $iterator = new \Papaya\Iterator\Union(
      new ArrayIterator(),
      new ArrayIterator(array(21 => 'One'))
    );
    $this->assertEquals(
      array('One'),
      iterator_to_array($iterator, TRUE)
    );
  }

  /**
  * @covers \Papaya\Iterator\Union::countIterators
  */
  public function testCountIterators() {
    $iterator = new \Papaya\Iterator\Union(new ArrayIterator());
    $this->assertEquals(1, $iterator->countIterators());
  }
}
