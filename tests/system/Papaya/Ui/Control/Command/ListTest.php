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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCommandListTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::__construct
  */
  public function testConstructor() {
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
    $list = new \Papaya\Ui\Control\Command\Collection($command);
    $this->assertSame(
      $command, $list[0]
    );
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::appendTo
  */
  public function testAppendToWithOneValidCommand() {
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
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
    $list = new \Papaya\Ui\Control\Command\Collection($command);
    $list->getXml();
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::appendTo
  */
  public function testAppendToWithOneCommandInvalidPermission() {
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
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
    $list = new \Papaya\Ui\Control\Command\Collection($command);
    $list->getXml();
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::appendTo
  */
  public function testAppendToWithOneCommandInvalidCondition() {
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
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
    $list = new \Papaya\Ui\Control\Command\Collection($command);
    $list->getXml();
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::appendTo
  */
  public function testAppendToWithTwoCommandsFirstCommandBlocked() {
    $commandBlocked = $this->createMock(\Papaya\Ui\Control\Command::class);
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
    $commandOk = $this->createMock(\Papaya\Ui\Control\Command::class);
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
    $list = new \Papaya\Ui\Control\Command\Collection($commandBlocked, $commandOk);
    $list->getXml();
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::appendTo
  */
  public function testAppendToWithTwoCommandsSecondCommandBlocked() {
    $commandBlocked = $this->createMock(\Papaya\Ui\Control\Command::class);
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
    $commandOk = $this->createMock(\Papaya\Ui\Control\Command::class);
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
    $list = new \Papaya\Ui\Control\Command\Collection($commandOk, $commandBlocked);
    $list->getXml();
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::owner
  */
  public function testOwner() {
    $owner = $this->createMock(\Papaya\Ui\Control\Interactive::class);
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
    $command
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $list = new \Papaya\Ui\Control\Command\Collection($command);
    $list->owner($owner);
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
    $list = new \Papaya\Ui\Control\Command\Collection($command);
    $this->assertTrue(isset($list[0]));
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
    $list = new \Papaya\Ui\Control\Command\Collection($command);
    $this->assertFalse(isset($list[99]));
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::offsetGet
  * @covers \Papaya\Ui\Control\Command\Collection::offsetSet
  */
  public function testOffsetGetAfterSet() {
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
    $list = new \Papaya\Ui\Control\Command\Collection();
    $list[] = $command;
    $this->assertSame(
      $command, $list[0]
    );
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::offsetSet
  */
  public function testOffsetSetWithInvalidCommandExpectingException() {
    $list = new \Papaya\Ui\Control\Command\Collection();
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Expected instance of "Papaya\Ui\Control\PapayaUiControlCommand" but "string" was given.');
    $list[] = 'INVALID';
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::offsetUnset
  */
  public function testOffsetUnset() {
    $command = $this->createMock(\Papaya\Ui\Control\Command::class);
    $list = new \Papaya\Ui\Control\Command\Collection($command);
    unset($list[0]);
    $this->assertFalse(isset($list[0]));
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::count
  */
  public function testCountExpectingZero() {
    $list = new \Papaya\Ui\Control\Command\Collection();
    $this->assertCount(0, $list);
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::count
  */
  public function testCountExpectingTwo() {
    $list = new \Papaya\Ui\Control\Command\Collection(
      $this->createMock(\Papaya\Ui\Control\Command::class),
      $this->createMock(\Papaya\Ui\Control\Command::class)
    );
    $this->assertCount(2, $list);
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Collection::getIterator
  */
  public function testGetIterator() {
    $list = new \Papaya\Ui\Control\Command\Collection(
      $one = $this->createMock(\Papaya\Ui\Control\Command::class),
      $two = $this->createMock(\Papaya\Ui\Control\Command::class)
    );
    $iterator = $list->getIterator();
    $this->assertSame(
      array($one, $two),
      $iterator->getArrayCopy()
    );
  }
}
