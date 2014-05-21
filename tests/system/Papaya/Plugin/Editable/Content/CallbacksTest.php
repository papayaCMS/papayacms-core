<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaPluginEditableContentCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaPluginEditableContentCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaPluginEditableContentCallbacks();
    $this->assertNull($callbacks->onCreateEditor->defaultReturn);
  }
}