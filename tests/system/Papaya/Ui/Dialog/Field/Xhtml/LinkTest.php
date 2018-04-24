<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldXhtmlLinkTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldXhtmlLink::__construct
  */
  public function testConstructor() {
    $link = new PapayaUiDialogFieldXhtmlLink('http://www.papaya-cms.com', 'PapayaCMS');
    $this->assertAttributeEquals('http://www.papaya-cms.com', '_url', $link);
    $this->assertAttributeEquals('PapayaCMS', '_urlCaption', $link);
  }

  /**
  * @covers PapayaUiDialogFieldXhtmlLink::appendTo
  */
  public function testAppendTo() {
    $link = new PapayaUiDialogFieldXhtmlLink('http://www.papaya-cms.com', 'PapayaCMS');
    $this->assertEquals(
      '<field class="DialogFieldXhtmlLink" error="no">'.
        '<xhtml><a href="http://www.papaya-cms.com">PapayaCMS</a></xhtml>'.
      '</field>',
      $link->getXml()
    );
  }

}
