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

namespace Papaya\UI\Control\Command;
require_once __DIR__.'/../../../../../bootstrap.php';

class CollectionTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Control\Command\Collection::__construct
   */
  public function testConstructor() {
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $list = new Collection($command);
    $this->assertSame(
      $command, $list[0]
    );
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::appendTo
   */
  public function testAppendToWithOneValidCommand() {
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $command
      ->expects($this->once())
      ->method('validateCondition')
      ->will($this->returnValue(TRUE));
    $command
      ->expects($this->once())
      ->method('validatePermission')
      ->will($this->returnValue(TRUE));
    $command
      ->expects($this->once())
      ->method('appendTo');
    $list = new Collection($command);
    $list->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::appendTo
   */
  public function testAppendToWithOneCommandInvalidPermission() {
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $command
      ->expects($this->once())
      ->method('validateCondition')
      ->will($this->returnValue(TRUE));
    $command
      ->expects($this->once())
      ->method('validatePermission')
      ->will($this->returnValue(FALSE));
    $command
      ->expects($this->never())
      ->method('appendTo');
    $list = new Collection($command);
    $list->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::appendTo
   */
  public function testAppendToWithOneCommandInvalidCondition() {
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $command
      ->expects($this->once())
      ->method('validateCondition')
      ->will($this->returnValue(FALSE));
    $command
      ->expects($this->never())
      ->method('validatePermission');
    $command
      ->expects($this->never())
      ->method('appendTo');
    $list = new Collection($command);
    $list->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::appendTo
   */
  public function testAppendToWithTwoCommandsFirstCommandBlocked() {
    $commandBlocked = $this->createMock(\Papaya\UI\Control\Command::class);
    $commandBlocked
      ->expects($this->once())
      ->method('validateCondition')
      ->will($this->returnValue(FALSE));
    $commandBlocked
      ->expects($this->never())
      ->method('validatePermission');
    $commandBlocked
      ->expects($this->never())
      ->method('appendTo');
    $commandOk = $this->createMock(\Papaya\UI\Control\Command::class);
    $commandOk
      ->expects($this->once())
      ->method('validateCondition')
      ->will($this->returnValue(TRUE));
    $commandOk
      ->expects($this->once())
      ->method('validatePermission')
      ->will($this->returnValue(TRUE));
    $commandOk
      ->expects($this->once())
      ->method('appendTo');
    $list = new Collection($commandBlocked, $commandOk);
    $list->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::appendTo
   */
  public function testAppendToWithTwoCommandsSecondCommandBlocked() {
    $commandBlocked = $this->createMock(\Papaya\UI\Control\Command::class);
    $commandBlocked
      ->expects($this->once())
      ->method('validateCondition')
      ->will($this->returnValue(FALSE));
    $commandBlocked
      ->expects($this->never())
      ->method('validatePermission');
    $commandBlocked
      ->expects($this->never())
      ->method('appendTo');
    $commandOk = $this->createMock(\Papaya\UI\Control\Command::class);
    $commandOk
      ->expects($this->once())
      ->method('validateCondition')
      ->will($this->returnValue(TRUE));
    $commandOk
      ->expects($this->once())
      ->method('validatePermission')
      ->will($this->returnValue(TRUE));
    $commandOk
      ->expects($this->once())
      ->method('appendTo');
    $list = new Collection($commandOk, $commandBlocked);
    $list->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::owner
   */
  public function testOwner() {
    $owner = $this->createMock(\Papaya\UI\Control\Interactive::class);
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $command
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $list = new Collection($command);
    $list->owner($owner);
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::offsetExists
   */
  public function testOffsetExistsExpectingTrue() {
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $list = new Collection($command);
    $this->assertTrue(isset($list[0]));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::offsetExists
   */
  public function testOffsetExistsExpectingFalse() {
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $list = new Collection($command);
    $this->assertFalse(isset($list[99]));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::offsetGet
   * @covers \Papaya\UI\Control\Command\Collection::offsetSet
   */
  public function testOffsetGetAfterSet() {
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $list = new Collection();
    $list[] = $command;
    $this->assertSame(
      $command, $list[0]
    );
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::offsetSet
   */
  public function testOffsetSetWithInvalidCommandExpectingException() {
    $list = new Collection();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Expected instance of "Papaya\UI\Control\Command" but "string" was given.');
    $list[] = 'INVALID';
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::offsetUnset
   */
  public function testOffsetUnset() {
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $list = new Collection($command);
    unset($list[0]);
    $this->assertFalse(isset($list[0]));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::count
   */
  public function testCountExpectingZero() {
    $list = new Collection();
    $this->assertCount(0, $list);
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::count
   */
  public function testCountExpectingTwo() {
    $list = new Collection(
      $this->createMock(\Papaya\UI\Control\Command::class),
      $this->createMock(\Papaya\UI\Control\Command::class)
    );
    $this->assertCount(2, $list);
  }

  /**
   * @covers \Papaya\UI\Control\Command\Collection::getIterator
   */
  public function testGetIterator() {
    $list = new Collection(
      $one = $this->createMock(\Papaya\UI\Control\Command::class),
      $two = $this->createMock(\Papaya\UI\Control\Command::class)
    );
    $iterator = $list->getIterator();
    $this->assertSame(
      array($one, $two),
      $iterator->getArrayCopy()
    );
  }
}
