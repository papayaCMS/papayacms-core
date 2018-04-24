<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaProfilerStorageFileTest extends PapayaTestCase {

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  /**
  * @covers PapayaProfilerStorageFile::__construct
  * @covers PapayaProfilerStorageFile::prepareDirectory
  */
  public function testConstructor() {
    $storage = new PapayaProfilerStorageFile(
      $this->createTemporaryDirectory()
    );
    $this->assertAttributeNotEquals('', '_directory', $storage);
  }

  /**
  * @covers PapayaProfilerStorageFile::__construct
  * @covers PapayaProfilerStorageFile::prepareSuffix
  */
  public function testConstructorWithAllParameters() {
    $storage = new PapayaProfilerStorageFile(
      $this->createTemporaryDirectory(),
      'foo'
    );
    $this->assertAttributeEquals('foo', '_suffix', $storage);
  }

  /**
  * @covers PapayaProfilerStorageFile::__construct
  * @covers PapayaProfilerStorageFile::prepareSuffix
  */
  public function testConstructorWithInvalidSuffixExpectingException() {
    $this->setExpectedException(
      'UnexpectedValueException',
      'Invalid profiling file suffix "-"'
    );
    $storage = new PapayaProfilerStorageFile(
      $this->createTemporaryDirectory(),
      '-'
    );
  }

  /**
  * @covers PapayaProfilerStorageFile::saveRun
  * @covers PapayaProfilerStorageFile::getId
  * @covers PapayaProfilerStorageFile::getFilename
  * @covers PapayaProfilerStorageFile::prepareDirectory
  */
  public function testSaveRun() {
    $storage = new PapayaProfilerStorageFile(
      $directory = $this->createTemporaryDirectory().'/',
      'foo'
    );
    $id = $storage->saveRun(array(), 'sample');
    $expectedFile = $directory.$id.'.sample.foo';
    $this->assertTrue(file_exists($expectedFile));
  }

  /**
  * @covers PapayaProfilerStorageFile::prepareDirectory
  */
  public function testSaveRunWithEmptyDirectoryExpectingException() {
    $storage = new PapayaProfilerStorageFile('', 'foo');
    $this->setExpectedException(
      'UnexpectedValueException',
      'No profiling directory defined.'
    );
    $storage->saveRun(array(), 'sample');
  }

  /**
  * @covers PapayaProfilerStorageFile::prepareDirectory
  */
  public function testSaveRunWithNonWriteableDirectoryExpectingException() {
    $storage = new PapayaProfilerStorageFile('http://localhost/', 'foo');
    $this->setExpectedException(
      'UnexpectedValueException',
      'Profiling directory "/http:/localhost/" is not writeable.'
    );
    $storage->saveRun(array(), 'sample');
  }
}
