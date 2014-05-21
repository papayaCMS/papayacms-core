<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogFieldSelectCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiDialogFieldSelectCallbacks();
    $this->assertNull($callbacks->getOptionCaption->defaultReturn);
  }
}