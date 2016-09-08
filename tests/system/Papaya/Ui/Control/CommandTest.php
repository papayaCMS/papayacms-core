<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');
PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_AUTHOPTIONS',
  'PAPAYA_DB_TBL_AUTHUSER',
  'PAPAYA_DB_TBL_AUTHGROUPS',
  'PAPAYA_DB_TBL_AUTHLINK',
  'PAPAYA_DB_TBL_AUTHPERM',
  'PAPAYA_DB_TBL_AUTHMODPERMS',
  'PAPAYA_DB_TBL_AUTHMODPERMLINKS',
  'PAPAYA_DB_TBL_SURFER'
);

class PapayaUiControlCommandTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommand::condition
  * @covers PapayaUiControlCommand::createCondition
  */
  public function testConditionGetExpectingTrue() {
    $command = new PapayaUiControlCommand_TestProxy();
    $this->assertInstanceOf('PapayaUiControlCommandConditionValue', $command->condition());
    $this->assertTrue($command->condition()->validate());
  }

  /**
  * @covers PapayaUiControlCommand::condition
  */
  public function testConditionGetAfterSet() {
    $command = new PapayaUiControlCommand_TestProxy();
    $command->condition($condition = $this->getMock('PapayaUiControlCommandCondition'));
    $this->assertEquals($condition, $command->condition());
  }

  /**
  * @covers PapayaUiControlCommand::validateCondition
  */
  public function testValidateConditionWithoutConditionExpectingTrue() {
    $command = new PapayaUiControlCommand_TestProxy();
    $this->assertTrue($command->validateCondition());
  }

  /**
  * @covers PapayaUiControlCommand::validateCondition
  */
  public function testValidateConditionWithConditionExpectingTrue() {
    $condition = $this->getMock('PapayaUiControlCommandCondition');
    $condition
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $command = new PapayaUiControlCommand_TestProxy();
    $command->condition($condition);
    $this->assertTrue($command->validateCondition());
  }

  /**
  * @covers PapayaUiControlCommand::validateCondition
  */
  public function testValidateConditionWithConditionExpectingFalse() {
    $condition = $this->getMock('PapayaUiControlCommandCondition');
    $condition
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $command = new PapayaUiControlCommand_TestProxy();
    $command->condition($condition);
    $this->assertFalse($command->validateCondition());
  }

  /**
  * @covers PapayaUiControlCommand::permission
  */
  public function testPermissionGetExpectingNull() {
    $command = new PapayaUiControlCommand_TestProxy();
    $this->assertNull($command->permission());
  }

  /**
  * @covers PapayaUiControlCommand::permission
  */
  public function testPermissionGetAfterSet() {
    $command = new PapayaUiControlCommand_TestProxy();
    $command->permission(42);
    $this->assertEquals(42, $command->permission());
  }

  /**
  * @covers PapayaUiControlCommand::validatePermission
  */
  public function testValidatePermissionWithoutPermissionExpectingTrue() {
    $command = new PapayaUiControlCommand_TestProxy();
    $this->assertTrue($command->validatePermission());
  }

  /**
  * @covers PapayaUiControlCommand::validatePermission
  */
  public function testValidatePermissionWithInvalidPermissionExpectingException() {
    $command = new PapayaUiControlCommand_TestProxy();
    $command->permission(new stdClass());
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: Invalid permission value.'
    );
    $command->validatePermission();
  }

  /**
  * @covers PapayaUiControlCommand::validatePermission
  */
  public function testValidatePermissionWithPermissionExpectingFalse() {
    $user = $this->getMock('base_auth', array('hasPerm'));
    $user
      ->expects($this->once())
      ->method('hasPerm')
      ->with(42)
      ->will($this->returnValue(FALSE));
    $command = new PapayaUiControlCommand_TestProxy();
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
  * @covers PapayaUiControlCommand::validatePermission
  */
  public function testValidatePermissionWithPermissionExpectingTrue() {
    $user = $this->getMock('base_auth', array('hasPerm'));
    $user
      ->expects($this->once())
      ->method('hasPerm')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $command = new PapayaUiControlCommand_TestProxy();
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
  * @covers PapayaUiControlCommand::validatePermission
  */
  public function testValidatePermissionWithModulePermissionExpectingFalse() {
    $user = $this->getMock('base_auth', array('hasPerm'));
    $user
      ->expects($this->once())
      ->method('hasPerm')
      ->with(42, '1234')
      ->will($this->returnValue(FALSE));
    $command = new PapayaUiControlCommand_TestProxy();
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
  * @covers PapayaUiControlCommand::validatePermission
  */
  public function testValidatePermissionWithModulePermissionExpectingTrue() {
    $user = $this->getMock('base_auth', array('hasPerm'));
    $user
      ->expects($this->once())
      ->method('hasPerm')
      ->with(42, '1234')
      ->will($this->returnValue(TRUE));
    $command = new PapayaUiControlCommand_TestProxy();
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
  * @covers PapayaUiControlCommand::owner
  */
  public function testOwnerGetAfterSet() {
    $application = $this->mockPapaya()->application();
    $owner = $this->getMock('PapayaUiControlInteractive', array('papaya', 'appendTo'));
    $owner
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($application));
    $command = new PapayaUiControlCommand_TestProxy();
    $command->papaya();
    $this->assertSame($owner, $command->owner($owner));
    $this->assertEquals($application, $command->papaya());
  }

  /**
  * @covers PapayaUiControlCommand::owner
  */
  public function testOwnerGetExpectingException() {
    $command = new PapayaUiControlCommand_TestProxy();
    $this->setExpectedException(
      'LogicException',
      'LogicException: Instance of "PapayaUiControlCommand_TestProxy" has no owner assigned.'
    );
    $owner = $command->owner();
  }

  /**
  * @covers PapayaUiControlCommand::hasOwner
  */
  public function testHasOwnerExpectingTrue() {
    $owner = $this->getMock('PapayaUiControlInteractive');
    $command = new PapayaUiControlCommand_TestProxy();
    $command->owner($owner);
    $this->assertTrue($command->hasOwner());
  }

  /**
  * @covers PapayaUiControlCommand::hasOwner
  */
  public function testHasOwnerExpectingFalse() {
    $command = new PapayaUiControlCommand_TestProxy();
    $this->assertFalse($command->hasOwner());
  }

  /**
  * @covers PapayaUiControlCommand::parameterMethod
  */
  public function testParameterMethodGetAfterSet() {
    $command = new PapayaUiControlCommand_TestProxy();
    $this->assertEquals(
      PapayaRequestParametersInterface::METHOD_GET,
      $command->parameterMethod(PapayaRequestParametersInterface::METHOD_GET)
    );
  }

  /**
  * @covers PapayaUiControlCommand::parameterMethod
  */
  public function testParameterMethodGetAfterSetWithOwner() {
    $owner = $this->getMock('PapayaUiControlInteractive');
    $owner
      ->expects($this->once())
      ->method('parameterMethod')
      ->with($this->equalTo(PapayaRequestParametersInterface::METHOD_POST))
      ->will($this->returnArgument(0));
    $command = new PapayaUiControlCommand_TestProxy();
    $command->owner($owner);
    $this->assertEquals(
      PapayaRequestParametersInterface::METHOD_POST,
      $command->parameterMethod(PapayaRequestParametersInterface::METHOD_POST)
    );
  }

  /**
  * @covers PapayaUiControlCommand::parameterGroup
  */
  public function testParameterGroupGetAfterSet() {
    $command = new PapayaUiControlCommand_TestProxy();
    $this->assertEquals(
      'group_test',
      $command->parameterGroup('group_test')
    );
  }

  /**
  * @covers PapayaUiControlCommand::parameterGroup
  */
  public function testParameterGroupGetAfterSetWithOwner() {
    $owner = $this->getMock('PapayaUiControlInteractive');
    $owner
      ->expects($this->once())
      ->method('parameterGroup')
      ->with($this->equalTo('group_test'))
      ->will($this->returnArgument(0));
    $command = new PapayaUiControlCommand_TestProxy();
    $command->owner($owner);
    $this->assertEquals(
      'group_test',
      $command->parameterGroup('group_test')
    );
  }

  /**
  * @covers PapayaUiControlCommand::parameters
  */
  public function testParametersGetAfterSet() {
    $parameters = $this->getMock('PapayaRequestParameters');
    $command = new PapayaUiControlCommand_TestProxy();
    $this->assertSame(
      $parameters,
      $command->parameters($parameters)
    );
  }

  /**
  * @covers PapayaUiControlCommand::parameters
  */
  public function testParametersGetAfterSetWithOwner() {
    $parameters = $this->getMock('PapayaRequestParameters');
    $owner = $this->getMock('PapayaUiControlInteractive');
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->with($this->equalTo($parameters))
      ->will($this->returnArgument(0));
    $command = new PapayaUiControlCommand_TestProxy();
    $command->owner($owner);
    $this->assertEquals(
      $parameters,
      $command->parameters($parameters)
    );
  }
}

class PapayaUiControlCommand_TestProxy extends PapayaUiControlCommand {

  public function appendTo(PapayaXmlElement $parent) {
    return NULL;
  }
}