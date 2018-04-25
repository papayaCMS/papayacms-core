<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiControlCommandConditionCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandConditionCallback::__construct
  */
  public function testConstructorExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: provided $callback is not callable.');
    new PapayaUiControlCommandConditionCallback(23);
  }

  /**
  * @covers PapayaUiControlCommandConditionCallback::__construct
  * @covers PapayaUiControlCommandConditionCallback::validate
  */
  public function testValidateExpectingTrue() {
    $condition = new PapayaUiControlCommandConditionCallback(array($this, 'callbackReturnTrue'));
    $this->assertTrue($condition->validate());
  }

  /**
  * @covers PapayaUiControlCommandConditionCallback::__construct
  * @covers PapayaUiControlCommandConditionCallback::validate
  */
  public function testValidateExpectingFalse() {
    $condition = new PapayaUiControlCommandConditionCallback(array($this, 'callbackReturnFalse'));
    $this->assertFalse($condition->validate());
  }

  public function callbackReturnTrue() {
    return TRUE;
  }

  public function callbackReturnFalse() {
    return FALSE;
  }

}
