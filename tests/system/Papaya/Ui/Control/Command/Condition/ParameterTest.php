<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiControlCommandConditionParameterTest extends PapayaTestCase {

  public function testValidateExpectingTrue() {
    $filter = $this->createMock(PapayaFilter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with('value')
      ->will($this->returnArgument(0));
    $condition = new PapayaUiControlCommandConditionParameter('name', $filter);
    $condition->command($this->getcommandFixture('something'));
    $this->assertTrue($condition->validate());
  }

  public function testValidateExpectingFalse() {
    $filter = $this->createMock(PapayaFilter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with('value')
      ->will($this->returnValue(NULL));
    $condition = new PapayaUiControlCommandConditionParameter('name', $filter);
    $condition->command($this->getcommandFixture('something'));
    $this->assertFalse($condition->validate());
  }

  public function getCommandFixture() {
    $parameters = $this->createMock(PapayaRequestParameters::class);
    $parameters
      ->expects($this->once())
      ->method('get')
      ->with('name')
      ->will($this->returnValue('value'));
    $owner = $this->createMock(PapayaUiControlInteractive::class);
    $owner
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue($parameters));
    $command = $this->createMock(PapayaUiControlCommand::class);
    $command
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($owner));
    return $command;
  }
}
