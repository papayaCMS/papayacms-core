<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiNavigationItemNamedTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationItemNamed::appendTo
  */
  public function testAppendTo() {
    $item = new PapayaUiNavigationItemNamed('sample');
    $item->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertEquals(
      '<link href="http://www.test.tld/index.html" name="sample"/>',
      $item->getXml()
    );
  }
}