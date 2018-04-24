<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldCallback
  */
  public function testConstructorWithAllArguments() {
    $xhtml = new PapayaUiDialogFieldCallback(
      'Caption', 'name', array($this, 'callbackGetFieldString'), 42, $this->getMock('PapayaFilter')
    );
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldCallback" error="no">'.
        '<select/>'.
      '</field>',
      $xhtml->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldCallback
  */
  public function testAppendToWithCallbackReturningString() {
    $xhtml = new PapayaUiDialogFieldCallback(
      'Caption', 'name', array($this, 'callbackGetFieldString')
    );
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldCallback" error="no">'.
        '<select/>'.
      '</field>',
      $xhtml->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldCallback
  */
  public function testAppendToWithCallbackReturningDomElement() {
    $xhtml = new PapayaUiDialogFieldCallback(
      'Caption', 'name', array($this, 'callbackGetFieldDomElement')
    );
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldCallback" error="no">'.
        '<select/>'.
      '</field>',
      $xhtml->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldCallback
  */
  public function testAppendToWithCallbackReturningPapayaXmlAppendable() {
    $xhtml = new PapayaUiDialogFieldCallback(
      'Caption', 'name', array($this, 'callbackGetFieldPapayaXmlAppendable')
    );
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldCallback" error="no"/>',
      $xhtml->getXml()
    );
  }

  public function callbackGetFieldString($name, $field, $data) {
    return '<select/>';
  }

  public function callbackGetFieldDomElement($name, $field, $data) {
    $dom = new DOMDocument();
    return $dom->createElement('select');
  }

  public function callbackGetFieldPapayaXmlAppendable($name, $field, $data) {
    $result = $this->getMock('PapayaXmlAppendable');
    $result
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    return $result;
  }
}
