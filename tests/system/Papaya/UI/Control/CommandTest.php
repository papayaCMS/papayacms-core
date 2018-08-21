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

namespace Papaya\UI\Control {

  require_once __DIR__.'/../../../../bootstrap.php';
  \Papaya\TestCase::defineConstantDefaults(
    'PAPAYA_DB_TBL_AUTHOPTIONS',
    'PAPAYA_DB_TBL_AUTHUSER',
    'PAPAYA_DB_TBL_AUTHGROUPS',
    'PAPAYA_DB_TBL_AUTHLINK',
    'PAPAYA_DB_TBL_AUTHPERM',
    'PAPAYA_DB_TBL_AUTHMODPERMS',
    'PAPAYA_DB_TBL_AUTHMODPERMLINKS',
    'PAPAYA_DB_TBL_SURFER'
  );

  class CommandTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\UI\Control\Command::condition
     * @covers \Papaya\UI\Control\Command::createCondition
     */
    public function testConditionGetExpectingTrue() {
      $command = new Command_TestProxy();
      $this->assertInstanceOf(Command\Condition\Value::class, $command->condition());
      $this->assertTrue($command->condition()->validate());
    }

    /**
     * @covers \Papaya\UI\Control\Command::condition
     */
    public function testConditionGetAfterSet() {
      $command = new Command_TestProxy();
      $command->condition($condition = $this->createMock(Command\Condition::class));
      $this->assertEquals($condition, $command->condition());
    }

    /**
     * @covers \Papaya\UI\Control\Command::validateCondition
     */
    public function testValidateConditionWithoutConditionExpectingTrue() {
      $command = new Command_TestProxy();
      $this->assertTrue($command->validateCondition());
    }

    /**
     * @covers \Papaya\UI\Control\Command::validateCondition
     */
    public function testValidateConditionWithConditionExpectingTrue() {
      $condition = $this->createMock(Command\Condition::class);
      $condition
        ->expects($this->once())
        ->method('validate')
        ->will($this->returnValue(TRUE));
      $command = new Command_TestProxy();
      $command->condition($condition);
      $this->assertTrue($command->validateCondition());
    }

    /**
     * @covers \Papaya\UI\Control\Command::validateCondition
     */
    public function testValidateConditionWithConditionExpectingFalse() {
      $condition = $this->createMock(Command\Condition::class);
      $condition
        ->expects($this->once())
        ->method('validate')
        ->will($this->returnValue(FALSE));
      $command = new Command_TestProxy();
      $command->condition($condition);
      $this->assertFalse($command->validateCondition());
    }

    /**
     * @covers \Papaya\UI\Control\Command::permission
     */
    public function testPermissionGetExpectingNull() {
      $command = new Command_TestProxy();
      $this->assertNull($command->permission());
    }

    /**
     * @covers \Papaya\UI\Control\Command::permission
     */
    public function testPermissionGetAfterSet() {
      $command = new Command_TestProxy();
      $command->permission(42);
      $this->assertEquals(42, $command->permission());
    }

    /**
     * @covers \Papaya\UI\Control\Command::validatePermission
     */
    public function testValidatePermissionWithoutPermissionExpectingTrue() {
      $command = new Command_TestProxy();
      $this->assertTrue($command->validatePermission());
    }

    /**
     * @covers \Papaya\UI\Control\Command::validatePermission
     */
    public function testValidatePermissionWithInvalidPermissionExpectingException() {
      $command = new Command_TestProxy();
      $command->permission(new \stdClass());
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('UnexpectedValueException: Invalid permission value.');
      $command->validatePermission();
    }

    /**
     * @covers \Papaya\UI\Control\Command::validatePermission
     */
    public function testValidatePermissionWithPermissionExpectingFalse() {
      $user = $this->createMock(\base_auth::class);
      $user
        ->expects($this->once())
        ->method('hasPerm')
        ->with(42)
        ->will($this->returnValue(FALSE));
      $command = new Command_TestProxy();
      $command->papaya(
        $this->mockPapaya()->application(
          array(
            'AdministrationUser' => $user
          )
        )
      );
      $command->permission(42);
      $this->assertFalse($command->validatePermission());
    }

    /**
     * @covers \Papaya\UI\Control\Command::validatePermission
     */
    public function testValidatePermissionWithPermissionExpectingTrue() {
      $user = $this->createMock(\base_auth::class);
      $user
        ->expects($this->once())
        ->method('hasPerm')
        ->with(42)
        ->will($this->returnValue(TRUE));
      $command = new Command_TestProxy();
      $command->papaya(
        $this->mockPapaya()->application(
          array(
            'AdministrationUser' => $user
          )
        )
      );
      $command->permission(42);
      $this->assertTrue($command->validatePermission());
    }

    /**
     * @covers \Papaya\UI\Control\Command::validatePermission
     */
    public function testValidatePermissionWithModulePermissionExpectingFalse() {
      $user = $this->createMock(\base_auth::class);
      $user
        ->expects($this->once())
        ->method('hasPerm')
        ->with(42, '1234')
        ->will($this->returnValue(FALSE));
      $command = new Command_TestProxy();
      $command->papaya(
        $this->mockPapaya()->application(
          array(
            'AdministrationUser' => $user
          )
        )
      );
      $command->permission(array('1234', 42));
      $this->assertFalse($command->validatePermission());
    }

    /**
     * @covers \Papaya\UI\Control\Command::validatePermission
     */
    public function testValidatePermissionWithModulePermissionExpectingTrue() {
      $user = $this->createMock(\base_auth::class);
      $user
        ->expects($this->once())
        ->method('hasPerm')
        ->with(42, '1234')
        ->will($this->returnValue(TRUE));
      $command = new Command_TestProxy();
      $command->papaya(
        $this->mockPapaya()->application(
          array(
            'AdministrationUser' => $user
          )
        )
      );
      $command->permission(array('1234', 42));
      $this->assertTrue($command->validatePermission());
    }

    /**
     * @covers \Papaya\UI\Control\Command::owner
     */
    public function testOwnerGetAfterSet() {
      $application = $this->mockPapaya()->application();
      $owner = $this->createMock(Interactive::class);
      $owner
        ->expects($this->once())
        ->method('papaya')
        ->will($this->returnValue($application));
      $command = new Command_TestProxy();
      $command->papaya();
      $this->assertSame($owner, $command->owner($owner));
      $this->assertEquals($application, $command->papaya());
    }

    /**
     * @covers \Papaya\UI\Control\Command::owner
     */
    public function testOwnerGetExpectingException() {
      $command = new Command_TestProxy();
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('LogicException: Instance of "Papaya\UI\Control\Command_TestProxy" has no owner assigned.');
      $command->owner();
    }

    /**
     * @covers \Papaya\UI\Control\Command::hasOwner
     */
    public function testHasOwnerExpectingTrue() {
      $owner = $this->createMock(Interactive::class);
      $command = new Command_TestProxy();
      $command->owner($owner);
      $this->assertTrue($command->hasOwner());
    }

    /**
     * @covers \Papaya\UI\Control\Command::hasOwner
     */
    public function testHasOwnerExpectingFalse() {
      $command = new Command_TestProxy();
      $this->assertFalse($command->hasOwner());
    }

    /**
     * @covers \Papaya\UI\Control\Command::parameterMethod
     */
    public function testParameterMethodGetAfterSet() {
      $command = new Command_TestProxy();
      $this->assertEquals(
        \Papaya\Request\Parameters\Access::METHOD_GET,
        $command->parameterMethod(\Papaya\Request\Parameters\Access::METHOD_GET)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Command::parameterMethod
     */
    public function testParameterMethodGetAfterSetWithOwner() {
      $owner = $this->createMock(Interactive::class);
      $owner
        ->expects($this->once())
        ->method('parameterMethod')
        ->with($this->equalTo(\Papaya\Request\Parameters\Access::METHOD_POST))
        ->will($this->returnArgument(0));
      $command = new Command_TestProxy();
      $command->owner($owner);
      $this->assertEquals(
        \Papaya\Request\Parameters\Access::METHOD_POST,
        $command->parameterMethod(\Papaya\Request\Parameters\Access::METHOD_POST)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Command::parameterGroup
     */
    public function testParameterGroupGetAfterSet() {
      $command = new Command_TestProxy();
      $this->assertEquals(
        'group_test',
        $command->parameterGroup('group_test')
      );
    }

    /**
     * @covers \Papaya\UI\Control\Command::parameterGroup
     */
    public function testParameterGroupGetAfterSetWithOwner() {
      $owner = $this->createMock(Interactive::class);
      $owner
        ->expects($this->once())
        ->method('parameterGroup')
        ->with($this->equalTo('group_test'))
        ->will($this->returnArgument(0));
      $command = new Command_TestProxy();
      $command->owner($owner);
      $this->assertEquals(
        'group_test',
        $command->parameterGroup('group_test')
      );
    }

    /**
     * @covers \Papaya\UI\Control\Command::parameters
     */
    public function testParametersGetAfterSet() {
      $parameters = $this->createMock(\Papaya\Request\Parameters::class);
      $command = new Command_TestProxy();
      $this->assertSame(
        $parameters,
        $command->parameters($parameters)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Command::parameters
     */
    public function testParametersGetAfterSetWithOwner() {
      $parameters = $this->createMock(\Papaya\Request\Parameters::class);
      $owner = $this->createMock(Interactive::class);
      $owner
        ->expects($this->once())
        ->method('parameters')
        ->with($this->equalTo($parameters))
        ->will($this->returnArgument(0));
      $command = new Command_TestProxy();
      $command->owner($owner);
      $this->assertEquals(
        $parameters,
        $command->parameters($parameters)
      );
    }
  }

  class Command_TestProxy extends Command {

    public function appendTo(\Papaya\XML\Element $parent) {
      return NULL;
    }
  }
}