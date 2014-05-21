<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiControlCommandActionCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandActionCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiControlCommandActionCallbacks();
    $this->assertSame(array(), $callbacks->getDefinition->defaultReturn);
    $this->assertNull($callbacks->onValidationSuccessful->defaultReturn);
    $this->assertNull($callbacks->onValidationFailed->defaultReturn);
  }
}