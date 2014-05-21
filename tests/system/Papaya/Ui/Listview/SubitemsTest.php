<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiListviewSubitemsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitems::__construct
  * @covers PapayaUiListviewSubitems::owner
  */
  public function testConstructor() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
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
    $listview = $this->getMock('PapayaUiListview');
    $collection = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
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