<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldListviewTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldListview::__construct
  * @covers PapayaUiDialogFieldListview::listview
  */
  public function testConstructor() {
    $listview = $this->createMock(PapayaUiListview::class);
    $field = new PapayaUiDialogFieldListview($listview);
    $this->assertSame(
      $listview, $field->listview()
    );
  }

  /**
  * @covers PapayaUiDialogFieldListview::appendTo
  */
  public function testAppendTo() {
    $listview = $this->createMock(PapayaUiListview::class);
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $field = new PapayaUiDialogFieldListview($listview);
    $this->assertEquals(
      '<field class="DialogFieldListview" error="no"/>',
      $field->getXml()
    );
  }
}
