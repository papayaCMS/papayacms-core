<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiListviewItemsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewItems::__construct
  * @covers PapayaUiListviewItems::owner
  */
  public function testConstructor() {
    $listview = $this->createMock(PapayaUiListview::class);
    $items = new PapayaUiListviewItems($listview);
    $this->assertSame(
      $listview, $items->owner()
    );
  }

  /**
  * @covers PapayaUiListviewItems::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(PapayaUiReference::class);
    $listview = $this->createMock(PapayaUiListview::class);
    $items = new PapayaUiListviewItems($listview);
    $this->assertSame(
      $reference, $items->reference($reference)
    );
  }

  /**
  * @covers PapayaUiListviewItems::reference
  */
  public function testReferenceGetImplicitCreate() {
    $listview = $this->createMock(PapayaUiListview::class);
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($this->createMock(PapayaUiReference::class)));
    $items = new PapayaUiListviewItems($listview);
    $this->assertInstanceOf(
      PapayaUiReference::class, $items->reference()
    );
  }
}
