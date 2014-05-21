<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaControllerErrorFileTest extends PapayaTestCase {

  function testSetTemplateFile() {
    $controller = new PapayaControllerErrorFile();
    $fileName = dirname(__FILE__).'/TestData/template.txt';
    $this->assertTrue(
      $controller->setTemplateFile($fileName)
    );
    $this->assertStringEqualsFile(
      $fileName,
      $this->readAttribute($controller, '_template')
    );
  }

  function testSetTemplateFileWithInvalidArgument() {
    $controller = new PapayaControllerErrorFile();
    $this->assertFalse(
      $controller->setTemplateFile('INVALID_FILENAME.txt')
    );
    $this->assertAttributeNotEquals(
      '', '_template', $controller
    );
  }
}