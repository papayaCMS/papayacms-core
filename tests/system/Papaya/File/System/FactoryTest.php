<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFileSystemFactoryTest extends PapayaTestCase {

  /**
   * @covers PapayaFileSystemFactory::getFile
   */
  public function testGetFile() {
    $factory = new PapayaFileSystemFactory();
    $this->assertInstanceOf('PapayaFileSystemFile', $factory->getFile('/path/file.txt'));
  }

  /**
   * @covers PapayaFileSystemFactory::getDirectory
   */
  public function testGetDirectory() {
    $factory = new PapayaFileSystemFactory();
    $this->assertInstanceOf('PapayaFileSystemDirectory', $factory->getDirectory('/path'));
  }
}
