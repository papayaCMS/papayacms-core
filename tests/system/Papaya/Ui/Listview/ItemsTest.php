<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiListviewItemsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewItems::__construct
  * @covers PapayaUiListviewItems::owner
  */
  public function testConstructor() {
    $listview = $this->getMock('PapayaUiListview');
    $items = new PapayaUiListviewItems($listview);
    $this->assertSame(
      $listview, $items->owner()
    );
  }

  /**
  * @covers PapayaUiListviewItems::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaUiReference');
    $listview = $this->getMock('PapayaUiListview');
    $items = new PapayaUiListviewItems($listview);
    $this->assertSame(
      $reference, $items->reference($reference)
    );
  }

  /**
  * @covers PapayaUiListviewItems::reference
  */
  public function testReferenceGetImplicitCreate() {
    $listview = $this->getMock('PapayaUiListview');
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($this->getMock('PapayaUiReference')));
    $items = new PapayaUiListviewItems($listview);
    $this->assertInstanceOf(
      'PapayaUiReference', $items->reference()
    );
  }
}
