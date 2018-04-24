<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryExceptionInvalidProfileTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryExceptionInvalidProfile
   */
  public function testConstructor() {
    $exception = new PapayaFilterFactoryExceptionInvalidProfile('ExampleProfile');
    $this->assertEquals(
      'Invalid or unknown filter factory profile: "ExampleProfile".',
      $exception->getMessage()
    );
  }

}
