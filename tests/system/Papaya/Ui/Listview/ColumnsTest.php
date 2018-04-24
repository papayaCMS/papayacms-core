<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiListviewColumnsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewColumns::__construct
  * @covers PapayaUiListviewColumns::owner
  */
  public function testConstructor() {
    $listview = $this->createMock(PapayaUiListview::class);
    $columns = new PapayaUiListviewColumns($listview);
    $this->assertSame(
      $listview, $columns->owner()
    );
  }
}
