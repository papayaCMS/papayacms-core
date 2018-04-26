<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaIteratorGlobTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorGlob::__construct
  */
  public function testConstructor() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*');
    $this->assertStringEndsWith(
      '/TestDataGlob/*.*', $this->readAttribute($glob, '_path')
    );
  }

  /**
  * @covers PapayaIteratorGlob::__construct
  * @covers PapayaIteratorGlob::setFlags
  * @covers PapayaIteratorGlob::getFlags
  */
  public function testConstructorWithFlags() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*', GLOB_NOSORT);
    $this->assertEquals(
      GLOB_NOSORT, $glob->getFlags()
    );
  }

  /**
  * @covers PapayaIteratorGlob::rewind
  */
  public function testRewind() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*');
    $files = iterator_to_array($glob);
    $glob->rewind();
    $this->assertAttributeSame(
      NULL, '_files', $glob
    );
  }

  /**
  * @covers PapayaIteratorGlob::getFilesLazy
  * @covers PapayaIteratorGlob::getIterator
  */
  public function testGetIterator() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*');
    $files = iterator_to_array($glob);
    $this->assertStringEndsWith(
      '/TestDataGlob/sampleOne.txt', $files[0]
    );
    $this->assertStringEndsWith(
      '/TestDataGlob/sampleTwo.txt', $files[1]
    );
  }


  /**
  * @covers PapayaIteratorGlob::getFilesLazy
  * @covers PapayaIteratorGlob::getIterator
  */
  public function testGetIteratorInvalidDirectory() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/INVALID_DIRECTORY/*.*');
    $this->assertEquals(
      array(), iterator_to_array($glob)
    );
  }

  /**
  * @covers PapayaIteratorGlob::getFilesLazy
  * @covers PapayaIteratorGlob::count
  */
  public function testCount() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*');
    $this->assertCount(2, $glob);
  }
}
