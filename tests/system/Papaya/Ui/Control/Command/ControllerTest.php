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

class ControllerTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Control\Command\Controller::__construct
   */
  public function testConstructor() {
    $controller = new Controller('group/sample', 'default');
    $this->assertAttributeEquals(
      new \Papaya\Request\Parameters\Name(array('group', 'sample')), '_parameterName', $controller
    );
    $this->assertAttributeEquals(
      'default', '_defaultCommand', $controller
    );
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::appendTo
   * @covers \Papaya\UI\Control\Command\Controller::getCurrent
   */
  public function testAppendToWithoutCommand() {
    $owner = $this->createMock(\Papaya\UI\Control\Interactive::class);
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
    $controller = new Controller('sample', 'default');
    $controller->owner($owner);
    $controller->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::appendTo
   * @covers \Papaya\UI\Control\Command\Controller::getCurrent
   */
  public function testAppendToWithCommandSpecifiedByParameter() {
    $owner = $this->createMock(\Papaya\UI\Control\Interactive::class);
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('sample' => 'command'))));
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
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class))
      ->will($this->returnValue(NULL));

    $controller = new Controller('sample', 'default');
    $controller->owner($owner);
    $controller['command'] = $command;
    $controller->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::appendTo
   * @covers \Papaya\UI\Control\Command\Controller::getCurrent
   */
  public function testAppendToWithDefaultCommand() {
    $owner = $this->createMock(\Papaya\UI\Control\Interactive::class);
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
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
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class))
      ->will($this->returnValue(NULL));

    $controller = new Controller('sample', 'default');
    $controller->owner($owner);
    $controller['default'] = $command;
    $controller->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::appendTo
   * @covers \Papaya\UI\Control\Command\Controller::getCurrent
   */
  public function testAppendToWithDefaultCommandPermissionValidationFailed() {
    $owner = $this->createMock(\Papaya\UI\Control\Interactive::class);
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
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

    $controller = new Controller('sample', 'default');
    $controller->owner($owner);
    $controller['default'] = $command;
    $controller->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::appendTo
   * @covers \Papaya\UI\Control\Command\Controller::getCurrent
   */
  public function testAppendToWithDefaultCommandConditionValidationFailed() {
    $owner = $this->createMock(\Papaya\UI\Control\Interactive::class);
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
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

    $controller = new Controller('sample', 'default');
    $controller->owner($owner);
    $controller['default'] = $command;
    $controller->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::appendTo
   * @covers \Papaya\UI\Control\Command\Controller::getCurrent
   */
  public function testAppendToWithSelfConditionValidationFailed() {
    $condition = $this->createMock(\Papaya\UI\Control\Command\Condition::class);
    $condition
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $owner = $this->createMock(\Papaya\UI\Control\Interactive::class);
    $owner
      ->expects($this->never())
      ->method('parameters');
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $command
      ->expects($this->never())
      ->method('validateCondition');
    $command
      ->expects($this->never())
      ->method('validatePermission');
    $command
      ->expects($this->never())
      ->method('appendTo');

    $controller = new Controller('sample', 'default');
    $controller->owner($owner);
    $controller->condition($condition);
    $controller['default'] = $command;
    $controller->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::offsetExists
   */
  public function testOffsetExistsExpectingTrue() {
    $controller = new Controller('sample', 'default');
    $controller['default'] = $this->createMock(\Papaya\UI\Control\Command::class);
    $this->assertTrue(isset($controller['default']));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::offsetExists
   */
  public function testOffsetExistsExpectingFalse() {
    $controller = new Controller('sample', 'default');
    $this->assertFalse(isset($controller['default']));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::offsetSet
   * @covers \Papaya\UI\Control\Command\Controller::offsetGet
   */
  public function testOffsetGetAfterSet() {
    $controller = new Controller('sample', 'default');
    $controller['default'] = $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $this->assertSame($command, $controller['default']);
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::offsetSet
   */
  public function testOffsetSetWithOwner() {
    $owner = $this->createMock(\Papaya\UI\Control\Interactive::class);
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $command
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf(\Papaya\UI\Control::class));
    $controller = new Controller('sample', 'default');
    $controller->owner($owner);
    $controller['default'] = $command;
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::offsetUnset
   */
  public function testOffsetUnset() {
    $controller = new Controller('sample', 'default');
    $controller['default'] = $this->createMock(\Papaya\UI\Control\Command::class);
    unset($controller['default']);
    $this->assertFalse(isset($controller['default']));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::__isset
   */
  public function testMagicMethodIssetExpectingTrue() {
    $controller = new Controller('sample', 'default');
    /** @noinspection PhpUndefinedFieldInspection */
    $controller->default = $this->createMock(\Papaya\UI\Control\Command::class);
    $this->assertTrue(isset($controller->default));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::__isset
   */
  public function testMagicMethodIssetExpectingFalse() {
    $controller = new Controller('sample', 'default');
    $this->assertFalse(isset($controller->default));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::__set
   * @covers \Papaya\UI\Control\Command\Controller::__get
   */
  public function testMagicMethodGetAfterSet() {
    $controller = new Controller('sample', 'default');
    /** @noinspection PhpUndefinedFieldInspection */
    $controller->default = $command = $this->createMock(\Papaya\UI\Control\Command::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertSame($command, $controller->default);
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::__unset
   */
  public function testMagicMethodUnsetUnset() {
    $controller = new Controller('sample', 'default');
    /** @noinspection PhpUndefinedFieldInspection */
    $controller->default = $this->createMock(\Papaya\UI\Control\Command::class);
    unset($controller->default);
    $this->assertFalse(isset($controller->default));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::count
   */
  public function testCountExpectingZero() {
    $controller = new Controller('sample', 'default');
    $this->assertCount(0, $controller);
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::count
   */
  public function testCountExpectingTwo() {
    $controller = new Controller('sample', 'default');
    $controller['one'] = $this->createMock(\Papaya\UI\Control\Command::class);
    $controller['two'] = $this->createMock(\Papaya\UI\Control\Command::class);
    $this->assertCount(2, $controller);
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::getIterator
   */
  public function testGetIterator() {
    $commands = array(
      'one' => $this->createMock(\Papaya\UI\Control\Command::class),
      'two' => $this->createMock(\Papaya\UI\Control\Command::class)
    );
    $controller = new Controller('sample', 'default');
    $controller['one'] = $commands['one'];
    $controller['two'] = $commands['two'];
    $iterator = $controller->getIterator();
    $this->assertInstanceOf('ArrayIterator', $iterator);
    $this->assertEquals($commands, $iterator->getArrayCopy());
  }

  /**
   * @covers \Papaya\UI\Control\Command\Controller::owner
   */
  public function testOwner() {
    $owner = $this->createMock(\Papaya\UI\Control\Interactive::class);
    $command = $this->createMock(\Papaya\UI\Control\Command::class);
    $command
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $controller = new Controller('sample', 'default');
    $controller['default'] = $command;
    $controller->owner($owner);
  }
}
