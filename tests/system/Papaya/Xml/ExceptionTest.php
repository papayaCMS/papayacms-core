<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaXmlExceptionTest extends PapayaTestCase {

  /**
  * @covers PapayaXmlException::__construct
  */
  public function testConstructor() {
    $exception = new PapayaXmlException($this->getLibxmlErrorFixture());
    $this->assertEquals(
      'Libxml processing error 23 at line 42 char 21: libxml fatal error sample',
      $exception->getMessage()
    );
  }

  /**
  * @covers PapayaXmlException::getError
  */
  public function testGetError() {
    $exception = new PapayaXmlException($error = $this->getLibxmlErrorFixture());
    $this->assertSame($error, $exception->getError());
  }

  /**
  * @covers PapayaXmlException::getErrorCode
  */
  public function testGetErrorCode() {
    $exception = new PapayaXmlException($this->getLibxmlErrorFixture());
    $this->assertEquals(23, $exception->getErrorCode());
  }

  /**
  * @covers PapayaXmlException::getErrorMessage
  */
  public function testGetErrorMessage() {
    $exception = new PapayaXmlException($this->getLibxmlErrorFixture());
    $this->assertEquals('libxml fatal error sample', $exception->getErrorMessage());
  }
  /**
  * @covers PapayaXmlException::getContextLine
  */
  public function testGetContextLine() {
    $exception = new PapayaXmlException($this->getLibxmlErrorFixture());
    $this->assertEquals(42, $exception->getContextLine());
  }

  /**
  * @covers PapayaXmlException::getContextColumn
  */
  public function testGetContextColumn() {
    $exception = new PapayaXmlException($this->getLibxmlErrorFixture());
    $this->assertEquals(21, $exception->getContextColumn());
  }

  /**
  * @covers PapayaXmlException::getContextFile
  */
  public function testGetContextFileExpectingEmptyString() {
    $exception = new PapayaXmlException($this->getLibxmlErrorFixture());
    $this->assertEquals('', $exception->getContextFile());
  }

  /**
  * @covers PapayaXmlException::getContextFile
  */
  public function testGetContextFileExpectingString() {
    $error = $this->getLibxmlErrorFixture();
    $error->file = '/path/sample.xml';
    $exception = new PapayaXmlException($error);
    $this->assertEquals('/path/sample.xml', $exception->getContextFile());
  }


  /******************************
  * Fixtures
  ******************************/

  public function getLibxmlErrorFixture() {
    $error = new libxmlError();
    $error->code = 23;
    $error->message = 'libxml fatal error sample';
    $error->line = 42;
    $error->column = 21;
    $error->file = '';
    return $error;
  }
}