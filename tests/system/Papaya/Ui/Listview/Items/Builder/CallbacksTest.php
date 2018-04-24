<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiListviewItemsBuilderCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewItemsBuilderCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiListviewItemsBuilderCallbacks();
    $this->assertFalse($callbacks->onBeforeFill->defaultReturn);
    $this->assertNull($callbacks->onCreateItem->defaultReturn);
    $this->assertNull($callbacks->onAfterFill->defaultReturn);
  }
}
