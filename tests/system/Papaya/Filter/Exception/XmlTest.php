<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterExceptionXmlTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterExceptionXml
   */
  public function testConstructor() {
    $error = new libxmlError();
    $error->code = 23;
    $error->message = 'libxml fatal error sample';
    $error->line = 42;
    $error->column = 21;
    $error->file = '';

    $exception = new PapayaFilterExceptionXml(new PapayaXmlException($error));
    $this->assertNotEmpty($exception->getMessage());
  }
}