<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiControlCommandConditionTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandCondition::command
  */
  public function testCommandGetAfterSet() {
    $application = $this->mockPapaya()->application();
    $command = $this->getMock('PapayaUiControlCommand');
    $command
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($application));
    $condition = new PapayaUiControlCommandCondition_TestProxy();
    $condition->papaya();
    $this->assertSame($command, $condition->command($command));
    $this->assertEquals($application, $condition->papaya());
  }

  /**
  * @covers PapayaUiControlCommandCondition::command
  */
  public function testCommandGetExpectingException() {
    $condition = new PapayaUiControlCommandCondition_TestProxy();
    $this->setExpectedException(
      'LogicException',
      'LogicException:'.
        ' Instance of "PapayaUiControlCommandCondition_TestProxy" has no command assigned.'
    );
    $command = $condition->command();
  }

  /**
  * @covers PapayaUiControlCommandCondition::hasCommand
  */
  public function testHascommandExpectingTrue() {
    $command = $this->getMock('PapayaUiControlCommand');
    $condition = new PapayaUiControlCommandCondition_TestProxy();
    $condition->command($command);
    $this->assertTrue($condition->hasCommand());
  }

  /**
  * @covers PapayaUiControlCommandCondition::hasCommand
  */
  public function testHasCommandExpectingFalse() {
    $condition = new PapayaUiControlCommandCondition_TestProxy();
    $this->assertFalse($condition->hasCommand());
  }
}

class PapayaUiControlCommandCondition_TestProxy extends PapayaUiControlCommandCondition {

  public function validate() {

  }
}