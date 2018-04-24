<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogDatabaseCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogDatabaseCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiDialogDatabaseCallbacks();
    $this->assertTrue($callbacks->onBeforeDelete->defaultReturn);
    $this->assertTrue($callbacks->onBeforeSave->defaultReturn);
  }
}
