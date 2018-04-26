<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaControllerErrorFileTest extends PapayaTestCase {

  public function testSetTemplateFile() {
    $controller = new PapayaControllerErrorFile();
    $fileName = __DIR__.'/TestData/template.txt';
    $this->assertTrue(
      $controller->setTemplateFile($fileName)
    );
    $this->assertStringEqualsFile(
      $fileName,
      $this->readAttribute($controller, '_template')
    );
  }

  public function testSetTemplateFileWithInvalidArgument() {
    $controller = new PapayaControllerErrorFile();
    $this->assertFalse(
      $controller->setTemplateFile('INVALID_FILENAME.txt')
    );
    $this->assertAttributeNotEquals(
      '', '_template', $controller
    );
  }
}
