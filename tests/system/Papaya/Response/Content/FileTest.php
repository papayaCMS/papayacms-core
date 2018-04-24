<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaResponseContentFileTest extends PapayaTestCase {

  /**
  * @covers PapayaResponseContentFile::__construct
  */
  public function testConstructor() {
    $content = new PapayaResponseContentFile(__DIR__.'/TestData/data.txt');
    $this->assertStringEndsWith(
      '/TestData/data.txt', $this->readAttribute($content, '_filename')
    );
  }

  /**
  * @covers PapayaResponseContentFile::length
  */
  public function testLength() {
    $content = new PapayaResponseContentFile(__DIR__.'/TestData/data.txt');
    $this->assertEquals(4, $content->length());
  }

  /**
  * @covers PapayaResponseContentFile::output
  */
  public function testOutput() {
    $content = new PapayaResponseContentFile(__DIR__.'/TestData/data.txt');
    ob_start();
    $content->output();
    $this->assertEquals('DATA', ob_get_clean());
  }

  /**
  * @covers PapayaResponseContentFile::__toString
  */
  public function testMagicMethodToString() {
    $content = new PapayaResponseContentFile(__DIR__.'/TestData/data.txt');
    $this->assertEquals('DATA', (string)$content);
  }

}
