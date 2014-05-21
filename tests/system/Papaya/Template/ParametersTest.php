<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaTemplateParametersTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateParameters
   */
  public function testConstructor() {
    $parameters = new PapayaTemplateParameters();
    $this->assertCount(3, $parameters);
  }
}