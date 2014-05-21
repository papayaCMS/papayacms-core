<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFileSystemFileTest extends PapayaTestCase {

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  /**
   * @covers PapayaFileSystemFile::__construct
   * @covers PapayaFileSystemFile::__toString
   */
  public function testConstructor() {
    $file = new PapayaFileSystemFile('/path/file.txt');
    $this->assertEquals(
      '/path/file.txt', (string)$file
    );
  }

  /**
   * @covers PapayaFileSystemFile::exists
   */
  public function testExistsExpectingTrue() {
    $file = new PapayaFileSystemFile(__DIR__.'/TestData/sample.txt');
    $this->assertTrue($file->exists());
  }

  /**
   * @covers PapayaFileSystemFile::exists
   */
  public function testExistsExpectingFalse() {
    $file = new PapayaFileSystemFile(__DIR__.'/TestData/NON_EXISTING.txt');
    $this->assertFalse($file->exists());
  }

  /**
   * @covers PapayaFileSystemFile::isReadable
   */
  public function testIsReadableExpectingTrue() {
    $file = new PapayaFileSystemFile(__DIR__.'/TestData/sample.txt');
    $this->assertTrue($file->isReadable());
  }

  /**
   * @covers PapayaFileSystemFile::isWriteable
   */
  public function testIsWriteableExpectingTrue() {
    $filename = $this->createTemporaryDirectory().'/sample.txt';
    touch($filename);
    $file = new PapayaFileSystemFile($filename);
    $this->assertTrue($file->isWriteable());
  }

  /**
   * @covers PapayaFileSystemFile::getContents
   */
  public function testGetContents() {
    $file = new PapayaFileSystemFile(__DIR__.'/TestData/sample.txt');
    $this->assertEquals('success', $file->getContents());
  }

  /**
   * @covers PapayaFileSystemFile::putContents
   */
  public function testPutContents() {
    $filename = $this->createTemporaryDirectory().'/sample.txt';
    $file = new PapayaFileSystemFile($filename);
    $file->putContents('success');
    $this->assertEquals('success', file_get_contents($filename));
  }
}
