<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFileSystemFactoryTest extends PapayaTestCase {

  /**
   * @covers PapayaFileSystemFactory::getFile
   */
  public function testGetFile() {
    $factory = new PapayaFileSystemFactory();
    $this->assertInstanceOf(PapayaFileSystemFile::class, $factory->getFile('/path/file.txt'));
  }

  /**
   * @covers PapayaFileSystemFactory::getDirectory
   */
  public function testGetDirectory() {
    $factory = new PapayaFileSystemFactory();
    $this->assertInstanceOf(PapayaFileSystemDirectory::class, $factory->getDirectory('/path'));
  }
}
