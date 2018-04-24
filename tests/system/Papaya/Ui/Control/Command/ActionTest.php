<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCommandActionTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandAction
  */
  public function testDataWithImplicitCreate() {
    $command = new PapayaUiControlCommandAction();
    $command->parameters(new PapayaRequestParameters(array('test' => 'success')));
    $command->callbacks()->getDefinition = array($this, 'callbackGetDefinition');
    $this->assertEquals('success', $command->data()->get('test'));
  }

  /**
  * @covers PapayaUiControlCommandAction
  */
  public function testAppendToWithValidationSuccessful() {
    $command = new PapayaUiControlCommandAction();
    $command->parameters(new PapayaRequestParameters(array('test' => 'success')));
    $command->callbacks()->getDefinition = array($this, 'callbackGetDefinition');
    $command->callbacks()->onValidationSuccessful = array($this, 'callbackValidationSuccessful');
    $this->assertAppendedXmlEqualsXmlFragment(
      '<success>success</success>', $command
    );
  }

  /**
  * @covers PapayaUiControlCommandAction
  */
  public function testAppendToWithValidationFailed() {
    $command = new PapayaUiControlCommandAction();
    $command->parameters(new PapayaRequestParameters());
    $command->callbacks()->getDefinition = array($this, 'callbackGetDefinition');
    $command->callbacks()->onValidationFailed = array($this, 'callbackValidationFailed');
    $this->assertAppendedXmlEqualsXmlFragment(
      '<failed/>', $command
    );
  }

  /**
  * @covers PapayaUiControlCommandAction
  */
  public function testDataGetAfterSet() {
    $validator = $this
      ->getMockBuilder('PapayaRequestParametersValidator')#
      ->disableOriginalConstructor()
      ->getMock();
    $command = new PapayaUiControlCommandAction();
    $command->data($validator);
    $this->assertSame(
      $validator, $command->data()
    );
  }

  public function callbackValidationSuccessful($context, $command, $parent) {
    $parent->appendElement('success', array(), $command->data()->get('test'));
  }

  public function callbackValidationFailed($context, $command, $parent) {
    $parent->appendElement('failed');
  }

  public function callbackGetDefinition() {
    return array(
      array('test', '', new PapayaFilterNotEmpty())
    );
  }

  /**
  * @covers PapayaUiControlCommandAction::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->getMock('PapayaUiControlCommandActionCallbacks');
    $command = new PapayaUiControlCommandAction();
    $this->assertSame($callbacks, $command->callbacks($callbacks));
  }

  /**
  * @covers PapayaUiControlCommandAction::callbacks
  */
  public function testCallbacksGetImplicitCreate() {
    $command = new PapayaUiControlCommandAction();
    $this->assertInstanceOf('PapayaUiControlCommandActionCallbacks', $command->callbacks());
  }
}
