<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiListviewItemPagingUpTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewItemPagingUp::getPages
  * @dataProvider provideDataForPageCalculations
  */
  public function testGetPages($expected, $currentPage, $itemsPerPage) {
    $item = new PapayaUiListviewItemPagingUp('page', $currentPage, $itemsPerPage);
    $this->assertEquals(
      $expected,
      $item->getPages()
    );
  }

  /**
  * @covers PapayaUiListviewItemPagingUp::getImagePage
  */
  public function testGetImagePage() {
    $item = new PapayaUiListviewItemPagingUp('page', 2, 40);
    $this->assertEquals(
      3,
      $item->getImagePage()
    );
  }

  /**
  * @covers PapayaUiListviewItemPagingUp::getImagePage
  */
  public function testGetImagePageExpectingDefault() {
    $item = new PapayaUiListviewItemPagingUp('page', 8, 50);
    $this->assertEquals(
      5,
      $item->getImagePage()
    );
  }

  /*************************
  * Data Provider
  *************************/

  public static function provideDataForPageCalculations() {
    return array(
      array(
        array(),
        10,
        100
      ),
      array(
        array(),
        5,
        2
      ),
      array(
        array(3, 4),
        2,
        40
      )
    );
  }
}
