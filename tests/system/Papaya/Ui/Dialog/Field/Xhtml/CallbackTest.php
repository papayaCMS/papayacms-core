<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldXhtmlCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldXhtmlCallback
  * @covers PapayaUiDialogFieldCallback::appendTo
  */
  public function testAppendTo() {
    $xhtml = new PapayaUiDialogFieldXhtmlCallback(
      'Caption', 'name', array($this, 'callbackGetFieldString')
    );
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldXhtmlCallback" error="no">'.
        '<xhtml><select/></xhtml>'.
      '</field>',
      $xhtml->getXml()
    );
  }

  public function callbackGetFieldString($name, $field, $data) {
    return '<select/>';
  }

}
