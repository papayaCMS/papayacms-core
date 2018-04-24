<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaDatabaseRecordCallbacks();
    $this->assertTrue($callbacks->onBeforeInsert->defaultReturn);
    $this->assertTrue($callbacks->onBeforeUpdate->defaultReturn);
  }
}
