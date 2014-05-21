<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiNavigationBuilderCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationBuilderCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiNavigationBuilderCallbacks();
    $this->assertNull($callbacks->onBeforeAppend->defaultReturn);
    $this->assertNull($callbacks->onAfterAppend->defaultReturn);
    $this->assertNull($callbacks->onCreateItem->defaultReturn);
    $this->assertNull($callbacks->onAfterAppendItem->defaultReturn);
  }
}