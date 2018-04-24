<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldXhtmlTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldXhtml::__construct
  */
  public function testConstructor() {
    $xhtml = new PapayaUiDialogFieldXhtml('<strong>Test</strong>');
    $this->assertEquals(
      '<xhtml><strong>Test</strong></xhtml>',
      $xhtml->content()->saveXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldXhtml::content
  */
  public function testContentGetAfterSet() {
    $dom = new PapayaXmlDocument();
    $content = $dom->appendElement('html');
    $xhtml = new PapayaUiDialogFieldXhtml();
    $this->assertSame($content, $xhtml->content($content));
  }

  /**
  * @covers PapayaUiDialogFieldXhtml::content
  */
  public function testContentGetImplicitCreate() {
    $xhtml = new PapayaUiDialogFieldXhtml();
    $this->assertTrue($xhtml->content('<strong>Test</strong>') instanceof PapayaXmlElement);
  }

  /**
  * @covers PapayaUiDialogFieldXhtml::content
  */
  public function testContentGetExpectingInvalidArgumentException() {
    $xhtml = new PapayaUiDialogFieldXhtml();
    $this->setExpectedException('InvalidArgumentException');
    $xhtml->content(new stdClass());
  }

  /**
  * @covers PapayaUiDialogFieldXhtml::appendTo
  */
  public function testAppendTo() {
    $xhtml = new PapayaUiDialogFieldXhtml('<strong>Test</strong>');
    $this->assertEquals(
      '<field class="DialogFieldXhtml" error="no">'.
        '<xhtml><strong>Test</strong></xhtml>'.
      '</field>',
      $xhtml->getXml()
    );
  }

}
