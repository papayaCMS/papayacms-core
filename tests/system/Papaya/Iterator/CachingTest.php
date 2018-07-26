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

class PapayaIteratorCachingTest extends PapayaTestCase {

  /** @var ArrayObject */
  private $_arrayObject;

  /**
  * @covers \PapayaIteratorCaching::__construct
  * @covers \PapayaIteratorCaching::setCallback
  */
  public function testConstructor() {
    $iterator = new \PapayaIteratorCaching($innerIterator = new EmptyIterator());
    $this->assertSame($innerIterator, $iterator->getInnerIterator());
  }

  /**
  * @covers \PapayaIteratorCaching::__construct
  * @covers \PapayaIteratorCaching::setCallback
  * @covers \PapayaIteratorCaching::getCallback
  */
  public function testConstructorWithCallback() {
    $iterator = new \PapayaIteratorCaching(
      $innerIterator = new EmptyIterator(),
      array($this, 'callbackThrowException')
    );
    $this->assertEquals(
      array($this, 'callbackThrowException'),
      $iterator->getCallback()
    );
  }

  /**
  * @covers \PapayaIteratorCaching::__construct
  */
  public function testConstructorWithTraversable() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Traversable $traversable */
    $traversable = $this->createMock(IteratorAggregate::class);
    $iterator = new \PapayaIteratorCaching(
      $traversable,
      array($this, 'callbackThrowException')
    );
    /** @var OuterIterator $innerIterator */
    $innerIterator = $iterator->getInnerIterator();
    $this->assertSame($traversable, $innerIterator->getInnerIterator());
  }

  /**
  * @covers \PapayaIteratorCaching::__construct
  * @covers \PapayaIteratorCaching::setCallback
  */
  public function testConstructorWithInvalidCallbackExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Provided callback parameter is not valid.');
    new \PapayaIteratorCaching(
      $innerIterator = new EmptyIterator(),
      new stdClass()
    );
  }

  /**
  * @covers \PapayaIteratorCaching::getCache
  * @covers \PapayaIteratorCaching::rewind
  */
  public function testIterationCallsCallback() {
    $this->_arrayObject = new ArrayObject();
    $iterator = new \PapayaIteratorCaching(
      $this->_arrayObject,
      array($this, 'callbackFillCache')
    );
    $this->assertEquals(
      array(1, 2, 3),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers \PapayaIteratorCaching::getCache
  * @covers \PapayaIteratorCaching::rewind
  */
  public function testIterationCallsCallbackOnlyOnce() {
    $this->_arrayObject = new ArrayObject();
    $iterator = new \PapayaIteratorCaching(
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
