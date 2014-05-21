<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaTemplateSimpleVisitorOutputCallbacksTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleVisitorOutputCallbacks::__construct
   */
  public function testConstructor() {
    $callbacks = new PapayaTemplateSimpleVisitorOutputCallbacks();
    $this->assertNull($callbacks->onGetValue->defaultReturn);
  }
}