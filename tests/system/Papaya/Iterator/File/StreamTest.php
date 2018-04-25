<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaIteratorFileStreamTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorFileStream::__construct
  * @covers PapayaIteratorFileStream::setStream
  * @covers PapayaIteratorFileStream::getStream
  */
  public function testConstructor() {
    $iterator = new PapayaIteratorFileStream($this->getStreamFixture());
    $this->assertTrue(is_resource($iterator->getStream()));
  }

  /**
  * @covers PapayaIteratorFileStream::__construct
  * @covers PapayaIteratorFileStream::setStream
  */
  public function testConstructorWithInvaloidStreamExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Provided file stream is invalid');
    $iterator = new PapayaIteratorFileStream(NULL);
  }

  /**
  * @covers PapayaIteratorFileStream::__destruct
  */
  public function testDestructor() {
    $iterator = new PapayaIteratorFileStream($this->getStreamFixture());
    $iterator->__destruct();
    $this->assertFalse(is_resource($iterator->getStream()));
  }

  /**
  * @covers PapayaIteratorFileStream::rewind
  * @covers PapayaIteratorFileStream::next
  * @covers PapayaIteratorFileStream::valid
  * @covers PapayaIteratorFileStream::key
  * @covers PapayaIteratorFileStream::current
  */
  public function testIteration() {
    $iterator = new PapayaIteratorFileStream($this->getStreamFixture());
    $this->assertEquals(
      array("line1\n", "line2\n", "line3"),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorFileStream::rewind
  * @covers PapayaIteratorFileStream::next
  * @covers PapayaIteratorFileStream::valid
  * @covers PapayaIteratorFileStream::key
  * @covers PapayaIteratorFileStream::current
  */
  public function testIterationRemovingLineEnds() {
    $iterator = new PapayaIteratorFileStream(
      $this->getStreamFixture(), PapayaIteratorFileStream::TRIM_RIGHT
    );
    $this->assertEquals(
      array("line1", "line2", "line3"),
      iterator_to_array($iterator)
    );
  }

  public function getStreamFixture() {
    return fopen(
      "data:text/plain,line1\nline2\nline3",
      'r'
    );
  }
}
