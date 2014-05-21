<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiControlCommandListTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandList::__construct
  */
  public function testConstructor() {
    $command = $this->getMock('PapayaUiControlCommand');
    $list = new PapayaUiControlCommandList($command);
    $this->assertSame(
      $command, $list[0]
    );
  }

  /**
  * @covers PapayaUiControlCommandList::appendTo
  */
  public function testAppendToWithOneValidCommand() {
    $command = $this->getMock('PapayaUiControlCommand');
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
    $list = new PapayaUiControlCommandList($command);
    $list->getXml();
  }

  /**
  * @covers PapayaUiControlCommandList::appendTo
  */
  public function testAppendToWithOneCommandInvalidPermission() {
    $command = $this->getMock('PapayaUiControlCommand');
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
    $list = new PapayaUiControlCommandList($command);
    $list->getXml();
  }

  /**
  * @covers PapayaUiControlCommandList::appendTo
  */
  public function testAppendToWithOneCommandInvalidCondition() {
    $command = $this->getMock('PapayaUiControlCommand');
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
    $list = new PapayaUiControlCommandList($command);
    $list->getXml();
  }

  /**
  * @covers PapayaUiControlCommandList::appendTo
  */
  public function testAppendToWithTwoCommandsFirstCommandBlocked() {
    $commandBlocked = $this->getMock('PapayaUiControlCommand');
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
    $commandOk = $this->getMock('PapayaUiControlCommand');
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
    $list = new PapayaUiControlCommandList($commandBlocked, $commandOk);
    $list->getXml();
  }

  /**
  * @covers PapayaUiControlCommandList::appendTo
  */
  public function testAppendToWithTwoCommandsSecondCommandBlocked() {
    $commandBlocked = $this->getMock('PapayaUiControlCommand');
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
    $commandOk = $this->getMock('PapayaUiControlCommand');
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
    $list = new PapayaUiControlCommandList($commandOk, $commandBlocked);
    $list->getXml();
  }

  /**
  * @covers PapayaUiControlCommandList::owner
  */
  public function testOwner() {
    $owner = $this->getMock('PapayaUiControlInteractive');
    $command = $this->getMock('PapayaUiControlCommand');
    $command
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $list = new PapayaUiControlCommandList($command);
    $list->owner($owner);
  }

  /**
  * @covers PapayaUiControlCommandList::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $command = $this->getMock('PapayaUiControlCommand');
    $list = new PapayaUiControlCommandList($command);
    $this->assertTrue(isset($list[0]));
  }

  /**
  * @covers PapayaUiControlCommandList::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $command = $this->getMock('PapayaUiControlCommand');
    $list = new PapayaUiControlCommandList($command);
    $this->assertFalse(isset($list[99]));
  }

  /**
  * @covers PapayaUiControlCommandList::offsetGet
  * @covers PapayaUiControlCommandList::offsetSet
  */
  public function testOffsetGetAfterSet() {
    $command = $this->getMock('PapayaUiControlCommand');
    $list = new PapayaUiControlCommandList();
    $list[] = $command;
    $this->assertSame(
      $command, $list[0]
    );
  }

  /**
  * @covers PapayaUiControlCommandList::offsetSet
  */
  public function testOffsetSetWithInvalidCommandExpectingException() {
    $list = new PapayaUiControlCommandList();
    $this->setExpectedException(
      'UnexpectedValueException',
      'Expected instance of "PapayaUiControlCommand" but "string" was given.'
    );
    $list[] = 'INVALID';
  }

  /**
  * @covers PapayaUiControlCommandList::offsetUnset
  */
  public function testOffsetUnset() {
    $command = $this->getMock('PapayaUiControlCommand');
    $list = new PapayaUiControlCommandList($command);
    unset($list[0]);
    $this->assertFalse(isset($list[0]));
  }

  /**
  * @covers PapayaUiControlCommandList::count
  */
  public function testCountExpectingZero() {
    $list = new PapayaUiControlCommandList();
    $this->assertEquals(0, count($list));
  }

  /**
  * @covers PapayaUiControlCommandList::count
  */
  public function testCountExpectingTwo() {
    $list = new PapayaUiControlCommandList(
      $this->getMock('PapayaUiControlCommand'),
      $this->getMock('PapayaUiControlCommand')
    );
    $this->assertEquals(2, count($list));
  }

  /**
  * @covers PapayaUiControlCommandList::getIterator
  */
  public function testGetIterator() {
    $list = new PapayaUiControlCommandList(
      $one = $this->getMock('PapayaUiControlCommand'),
      $two = $this->getMock('PapayaUiControlCommand')
    );
    $iterator = $list->getIterator();
    $this->assertSame(
      array($one, $two),
      $iterator->getArrayCopy()
    );
  }
}