<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaControllerFactoryTest extends PapayaTestCase {

  /**
  * @covers PapayaControllerFactory::createError
  */
  public function testCreateError() {
    $error = PapayaControllerFactory::createError(404, 'Test', 'TEST');
    $this->assertInstanceOf(PapayaControllerError::class, $error);
    $this->assertAttributeEquals(
      404, '_status', $error
    );
    $this->assertAttributeEquals(
      'Test', '_errorMessage', $error
    );
    $this->assertAttributeEquals(
      'TEST', '_errorIdentifier', $error
    );
  }

  /**
  * @covers PapayaControllerFactory::createError
  */
  public function testCreateErrorWithFile() {
    $error = PapayaControllerFactory::createError(
      404, 'Test', 'TEST', dirname(__FILE__).'/Error/TestData/template.txt'
    );
    $this->assertInstanceOf(PapayaControllerErrorFile::class, $error);
    $this->assertAttributeEquals(
      'SAMPLE', '_template', $error
    );
  }

}
