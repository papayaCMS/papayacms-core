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

namespace Papaya\Iterator;

require_once __DIR__.'/../../../bootstrap.php';

class CachingTest extends \Papaya\TestCase {

  /** @var \ArrayObject */
  private $_arrayObject;

  /**
   * @covers \Papaya\Iterator\Caching::__construct
   * @covers \Papaya\Iterator\Caching::setCallback
   */
  public function testConstructor() {
    $iterator = new Caching($innerIterator = new \EmptyIterator());
    $this->assertSame($innerIterator, $iterator->getInnerIterator());
  }

  /**
   * @covers \Papaya\Iterator\Caching::__construct
   * @covers \Papaya\Iterator\Caching::setCallback
   * @covers \Papaya\Iterator\Caching::getCallback
   */
  public function testConstructorWithCallback() {
    $iterator = new Caching(
      $innerIterator = new \EmptyIterator(),
      array($this, 'callbackThrowException')
    );
    $this->assertEquals(
      array($this, 'callbackThrowException'),
      $iterator->getCallback()
    );
  }

  /**
   * @covers \Papaya\Iterator\Caching::__construct
   */
  public function testConstructorWithTraversable() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Traversable $traversable */
    $traversable = $this->createMock(\IteratorAggregate::class);
    $iterator = new Caching(
      $traversable,
      array($this, 'callbackThrowException')
    );
    /** @var \OuterIterator $innerIterator */
    $innerIterator = $iterator->getInnerIterator();
    $this->assertSame($traversable, $innerIterator->getInnerIterator());
  }

  /**
   * @covers \Papaya\Iterator\Caching::getCache
   * @covers \Papaya\Iterator\Caching::rewind
   */
  public function testIterationCallsCallback() {
    $this->_arrayObject = new \ArrayObject();
    $iterator = new Caching(
      $this->_arrayObject,
      array($this, 'callbackFillCache')
    );
    $this->assertEquals(
      array(1, 2, 3),
      iterator_to_array($iterator)
    );
  }

  /**
   * @covers \Papaya\Iterator\Caching::getCache
   * @covers \Papaya\Iterator\Caching::rewind
   */
  public function testIterationCallsCallbackOnlyOnce() {
    $this->_arrayObject = new \ArrayObject();
    $iterator = new Caching(
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
    throw new \LogicException('Constructor should not execute getCache callback.');
  }

  public function callbackFillCache() {
    $this->_arrayObject->append(count($this->_arrayObject) + 1);
    $this->_arrayObject->append(count($this->_arrayObject) + 1);
    $this->_arrayObject->append(count($this->_arrayObject) + 1);
  }
}
