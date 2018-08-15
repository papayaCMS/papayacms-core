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

class PapayaIteratorGeneratorTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Iterator\Generator::__construct
  * @covers \Papaya\Iterator\Generator::getIterator
  * @covers \Papaya\Iterator\Generator::createIterator
  */
  public function testGetIteratorWithoutData() {
    $iterator = new \Papaya\Iterator\Generator(
      array($this, 'callbackReturnArgument')
    );
    $this->assertInstanceOf('EmptyIterator', $iterator->getIterator());
  }

  /**
  * @covers \Papaya\Iterator\Generator::__construct
  * @covers \Papaya\Iterator\Generator::getIterator
  * @covers \Papaya\Iterator\Generator::createIterator
  */
  public function testGetIteratorWithArray() {
    $iterator = new \Papaya\Iterator\Generator(
      array($this, 'callbackReturnArgument'), array(array('foo', 'bar'))
    );
    $this->assertEquals(
      new \ArrayIterator(array('foo', 'bar')), $iterator->getIterator()
    );
  }

  /**
  * @covers \Papaya\Iterator\Generator::__construct
  * @covers \Papaya\Iterator\Generator::getIterator
  * @covers \Papaya\Iterator\Generator::createIterator
  */
  public function testGetIteratorWithIterator() {
    $iterator = new \Papaya\Iterator\Generator(
      array($this, 'callbackReturnArgument'), array($innerIterator = new \EmptyIterator)
    );
    $this->assertSame(
      $innerIterator, $iterator->getIterator()
    );
  }

  /**
  * @covers \Papaya\Iterator\Generator::__construct
  * @covers \Papaya\Iterator\Generator::getIterator
  * @covers \Papaya\Iterator\Generator::createIterator
  */
  public function testGetIteratorWithIteratorAggregate() {
    $wrapper = $this->createMock(IteratorAggregate::class);
    $wrapper
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator(array('foo'))));

    $iterator = new \Papaya\Iterator\Generator(
      array($this, 'callbackReturnArgument'),
      array($wrapper)
    );
    $this->assertEquals(
      array('foo'), iterator_to_array($iterator)
    );
  }

  /**
  * @covers \Papaya\Iterator\Generator::__construct
  * @covers \Papaya\Iterator\Generator::getIterator
  */
  public function testMultipleCallsCreateIteratorOnlyOnce() {
    $iterator = new \Papaya\Iterator\Generator(
      array($this, 'callbackReturnArgument')
    );
    $this->assertInstanceOf('EmptyIterator', $innerIterator = $iterator->getIterator());
    $this->assertSame($innerIterator, $iterator->getIterator());
  }

  public function callbackReturnArgument($traversable = NULL) {
    return $traversable;
  }
}
