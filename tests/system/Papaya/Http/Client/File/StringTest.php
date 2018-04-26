<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaHttpClientFileStringTest extends PapayaTestCase {

  function setUp() {
    $this->_fileContents = file_get_contents(__DIR__.'/DATA/sample.txt');
  }

  function testConstructor() {
    $fileName = __DIR__;
    $file = new PapayaHttpClientFileString(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $this->assertAttributeEquals('test', '_name', $file);
    $this->assertAttributeEquals('sample.txt', '_fileName', $file);
    $this->assertAttributeEquals('text/plain', '_mimeType', $file);
    $this->assertAttributeEquals($this->_fileContents, '_data', $file);
  }

  function testConstructorExpectingError() {
    $this->expectError(E_WARNING);
    $file = new PapayaHttpClientFileString('', '', '', '');
  }

  function testGetSize() {
    $file = new PapayaHttpClientFileString(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $this->assertEquals(6, $file->getSize());
    $this->assertEquals(6, $file->getSize());
  }

  function testSend() {
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('write')
           ->with($this->equalTo('sample'));
    $file = new PapayaHttpClientFileString(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $file->send($socket);
  }

  function testSendChunked() {
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('writeChunk')
           ->with($this->equalTo('sample'));
    $socket->expects($this->at(2))
           ->method('writeChunk')
           ->with($this->equalTo("\r\n"));
    $file = new PapayaHttpClientFileString(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $file->send($socket, TRUE);
  }
}
