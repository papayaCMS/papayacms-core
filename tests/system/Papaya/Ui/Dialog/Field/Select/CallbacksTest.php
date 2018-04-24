<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldSelectCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiDialogFieldSelectCallbacks();
    $this->assertNull($callbacks->getOptionCaption->defaultReturn);
  }
}
