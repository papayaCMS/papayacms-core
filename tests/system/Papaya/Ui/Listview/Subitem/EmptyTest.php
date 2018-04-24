<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiListviewSubitemEmptyTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitemEmpty::appendTo
  */
  public function testAppendTo() {
    $subitem = new PapayaUiListviewSubitemEmpty();
    $this->assertEquals(
      '<subitem/>',
      $subitem->getXml()
    );
  }
}
