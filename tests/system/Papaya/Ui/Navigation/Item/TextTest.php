<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiNavigationItemTextTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationItemText::appendTo
  */
  public function testAppendTo() {
    $item = new PapayaUiNavigationItemText('sample');
    $item->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertEquals(
      '<link href="http://www.test.tld/index.html">sample</link>',
      $item->getXml()
    );
  }
}