<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiListviewSubitemsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitems::__construct
  * @covers PapayaUiListviewSubitems::owner
  */
  public function testConstructor() {
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $subitems = new PapayaUiListviewSubitems($item);
    $this->assertSame(
      $item, $subitems->owner()
    );
  }

  /**
  * @covers PapayaUiListviewSubitems::getListview
  */
  public function testGetListview() {
    $listview = $this->createMock(PapayaUiListview::class);
    $collection = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item
      ->expects($this->once())
      ->method('collection')
      ->will($this->returnValue($collection));
    $subitems = new PapayaUiListviewSubitems($item);
    $this->assertSame(
      $listview, $subitems->getListview()
    );

  }
}
