<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCommandCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandCallback
  */
  public function testWithValidCallback() {
    $command = new PapayaUiControlCommandCallback(array($this, 'callbackAppendTo'));
    $this->assertAppendedXmlEqualsXmlFragment('<success/>', $command);
  }

  public function callbackAppendTo(PapayaXmlElement $parent) {
    return $parent->appendElement('success');
  }

}
