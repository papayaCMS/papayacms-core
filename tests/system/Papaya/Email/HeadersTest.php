<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaEmailHeadersTest extends PapayaTestCase {

  /**
  * @covers PapayaEmailHeaders
  */
  public function testConstruct() {
    $object = new PapayaEmailHeaders();
    $this->assertInstanceOf('PapayaHttpHeaders', $object);
  }

}
