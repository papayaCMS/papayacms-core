<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaEmailHeadersTest extends PapayaTestCase {

  /**
  * @covers PapayaEmailHeaders
  */
  public function testConstruct() {
    $object = new PapayaEmailHeaders();
    $this->assertInstanceOf('PapayaHttpHeaders', $object);
  }

}
