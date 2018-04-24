<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaResponseHelperTest extends PapayaTestCase {

  /**
  * @covers PapayaResponseHelper::headersSent
  */
  public function testHeadersSent() {
    $helper = new PapayaResponseHelper();
    $this->assertInternalType(
      'boolean',
      $helper->headersSent()
    );
  }
}
