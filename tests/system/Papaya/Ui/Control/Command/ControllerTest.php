<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCommandControllerTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandController::__construct
  */
  public function testConstructor() {
    $controller = new PapayaUiControlCommandController('group/sample', 'default');
    $this->assertAttributeEquals(
      new PapayaRequestParametersName(array('group', 'sample')), '_parameterName', $controller
    );
    $this->assertAttributeEquals(
      'default', '_defaultCommand', $controller
    );
  }

  /**
  * @covers PapayaUiControlCommandController::appendTo
  * @covers PapayaUiControlCommandController::getCurrent
  */
  public function testAppendToWithoutCommand() {
    $owner = $this->getMock('PapayaUiControlInteractive', array('parameters', 'appendTo'));
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller->owner($owner);
    $controller->getXml();
  }

  /**
  * @covers PapayaUiControlCommandController::appendTo
  * @covers PapayaUiControlCommandController::getCurrent
  */
  public function testAppendToWithCommandSpecifiedByParameter() {
    $owner = $this->getMock('PapayaUiControlInteractive', array('parameters', 'appendTo'));
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('sample' => 'command'))));
    $command = $this->createMock(PapayaUiControlCommand::class);
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
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'))
      ->will($this->returnValue(NULL));

    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller->owner($owner);
    $controller['command'] = $command;
    $controller->getXml();
  }

  /**
  * @covers PapayaUiControlCommandController::appendTo
  * @covers PapayaUiControlCommandController::getCurrent
  */
  public function testAppendToWithDefaultCommand() {
    $owner = $this->getMock('PapayaUiControlInteractive', array('parameters', 'appendTo'));
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $command = $this->createMock(PapayaUiControlCommand::class);
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
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'))
      ->will($this->returnValue(NULL));

    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller->owner($owner);
    $controller['default'] = $command;
    $controller->getXml();
  }

  /**
  * @covers PapayaUiControlCommandController::appendTo
  * @covers PapayaUiControlCommandController::getCurrent
  */
  public function testAppendToWithDefaultCommandPermissionValidationFailed() {
    $owner = $this->getMock('PapayaUiControlInteractive', array('parameters', 'appendTo'));
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $command = $this->createMock(PapayaUiControlCommand::class);
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

    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller->owner($owner);
    $controller['default'] = $command;
    $controller->getXml();
  }

  /**
  * @covers PapayaUiControlCommandController::appendTo
  * @covers PapayaUiControlCommandController::getCurrent
  */
  public function testAppendToWithDefaultCommandConditionValidationFailed() {
    $owner = $this->getMock('PapayaUiControlInteractive', array('parameters', 'appendTo'));
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $command = $this->createMock(PapayaUiControlCommand::class);
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

    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller->owner($owner);
    $controller['default'] = $command;
    $controller->getXml();
  }

  /**
  * @covers PapayaUiControlCommandController::appendTo
  * @covers PapayaUiControlCommandController::getCurrent
  */
  public function testAppendToWithSelfConditionValidationFailed() {
    $condition = $this->createMock(PapayaUiControlCommandCondition::class);
    $condition
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $owner = $this->getMock('PapayaUiControlInteractive', array('parameters', 'appendTo'));
    $owner
      ->expects($this->never())
      ->method('parameters');
    $command = $this->createMock(PapayaUiControlCommand::class);
    $command
      ->expects($this->never())
      ->method('validateCondition');
    $command
      ->expects($this->never())
      ->method('validatePermission');
    $command
      ->expects($this->never())
      ->method('appendTo');

    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller->owner($owner);
    $controller->condition($condition);
    $controller['default'] = $command;
    $controller->getXml();
  }

  /**
  * @covers PapayaUiControlCommandController::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller['default'] = $this->createMock(PapayaUiControlCommand::class);
    $this->assertTrue(isset($controller['default']));
  }

  /**
  * @covers PapayaUiControlCommandController::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $this->assertFalse(isset($controller['default']));
  }

  /**
  * @covers PapayaUiControlCommandController::offsetSet
  * @covers PapayaUiControlCommandController::offsetGet
  */
  public function testOffsetGetAfterSet() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller['default'] = $command = $this->createMock(PapayaUiControlCommand::class);
    $this->assertSame($command, $controller['default']);
  }

  /**
  * @covers PapayaUiControlCommandController::offsetSet
  */
  public function testOffsetSetWithOwner() {
    $owner = $this->createMock(PapayaUiControlInteractive::class);
    $command = $this->createMock(PapayaUiControlCommand::class);
    $command
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf('PapayaUiControl'));
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller->owner($owner);
    $controller['default'] = $command;
  }

  /**
  * @covers PapayaUiControlCommandController::offsetUnset
  */
  public function testOffsetUnset() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller['default'] = $this->createMock(PapayaUiControlCommand::class);
    unset($controller['default']);
    $this->assertFalse(isset($controller['default']));
  }
  /**
  * @covers PapayaUiControlCommandController::__isset
  */
  public function testMagicMethodIssetExpectingTrue() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    /** @noinspection PhpUndefinedFieldInspection */
    $controller->default = $this->createMock(PapayaUiControlCommand::class);
    $this->assertTrue(isset($controller->default));
  }

  /**
  * @covers PapayaUiControlCommandController::__isset
  */
  public function testMagicMethodIssetExpectingFalse() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $this->assertFalse(isset($controller->default));
  }

  /**
  * @covers PapayaUiControlCommandController::__set
  * @covers PapayaUiControlCommandController::__get
  */
  public function testMagicMethodGetAfterSet() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    /** @noinspection PhpUndefinedFieldInspection */
    $controller->default = $command = $this->createMock(PapayaUiControlCommand::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertSame($command, $controller->default);
  }

  /**
  * @covers PapayaUiControlCommandController::__unset
  */
  public function testMagicMethodUnsetUnset() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    /** @noinspection PhpUndefinedFieldInspection */
    $controller->default = $this->createMock(PapayaUiControlCommand::class);
    unset($controller->default);
    $this->assertFalse(isset($controller->default));
  }

  /**
  * @covers PapayaUiControlCommandController::count
  */
  public function testCountExpectingZero() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $this->assertEquals(0, count($controller));
  }

  /**
  * @covers PapayaUiControlCommandController::count
  */
  public function testCountExpectingTwo() {
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller['one'] = $this->createMock(PapayaUiControlCommand::class);
    $controller['two'] = $this->createMock(PapayaUiControlCommand::class);
    $this->assertEquals(2, count($controller));
  }

  /**
  * @covers PapayaUiControlCommandController::getIterator
  */
  public function testGetIterator() {
    $commands = array(
      'one' => $this->createMock(PapayaUiControlCommand::class),
      'two' => $this->createMock(PapayaUiControlCommand::class)
    );
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller['one'] = $commands['one'];
    $controller['two'] = $commands['two'];
    $iterator = $controller->getIterator();
    $this->assertInstanceOf('ArrayIterator', $iterator);
    $this->assertEquals($commands, $iterator->getArrayCopy());
  }

  /**
  * @covers PapayaUiControlCommandController::owner
  */
  public function testOwner() {
    $owner = $this->createMock(PapayaUiControlInteractive::class);
    $command = $this->createMock(PapayaUiControlCommand::class);
    $command
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $controller = new PapayaUiControlCommandController('sample', 'default');
    $controller['default'] = $command;
    $controller->owner($owner);
  }
}
