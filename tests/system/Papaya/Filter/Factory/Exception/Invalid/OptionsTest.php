<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaFilterFactoryExceptionInvalidOptionsTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryExceptionInvalidOptions
   */
  public function testConstructor() {
    $exception = new PapayaFilterFactoryExceptionInvalidOptions('ExampleProfile');
    $this->assertEquals(
      'Invalid options in filter profile class: "ExampleProfile".',
      $exception->getMessage()
    );
  }

}