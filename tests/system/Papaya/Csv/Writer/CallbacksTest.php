<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaCsvWriterCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaCsvWriterCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaCsvWriterCallbacks();
    $this->assertInternalType('array', $callbacks->onMapRow->defaultReturn);
    $this->assertInternalType('array', $callbacks->onMapHeader->defaultReturn);
  }

}
