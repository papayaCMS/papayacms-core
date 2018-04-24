<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaHttpClientFileNameTest extends PapayaTestCase {

  function setUp() {
    $this->_sampleFile = dirname(__FILE__).'/DATA/sample.txt';
  }

  function testConstructor() {
    $file = new PapayaHttpClientFileName('test', $this->_sampleFile, 'text/plain');
    $this->assertAttributeEquals('test', '_name', $file);
    $this->assertAttributeEquals($this->_sampleFile, '_fileName', $file);
    $this->assertAttributeEquals('text/plain', '_mimeType', $file);
  }

  function testConstructorExpectingError() {
    $this->setExpectedException('LogicException');
    $file = new PapayaHttpClientFileName('', '', '');
  }

  function testGetSize() {
    $file = new PapayaHttpClientFileName('test', $this->_sampleFile, 'text/plain');
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
    $file = new PapayaHttpClientFileName('test', $this->_sampleFile, 'text/plain');
    $file->send($socket);
  }

  function testSendLimited() {
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('write')
           ->with($this->equalTo('samp'));
    $socket->expects($this->at(2))
           ->method('write')
           ->with($this->equalTo('le'));
    $file = new PapayaHttpClientFileName('test', $this->_sampleFile, 'text/plain');
    $file->send($socket, FALSE, 4);
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
    $file = new PapayaHttpClientFileName('test', $this->_sampleFile, 'text/plain');
    $file->send($socket, TRUE);
  }

  function testSendInvalidFileExpectingError() {
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $file = new PapayaHttpClientFileName_TestProxy('test', $this->_sampleFile, 'text/plain');
    $file->_fileName = 'INVALID_FILE';
    $this->setExpectedException('LogicException');
    $file->send($socket, TRUE);
  }
}

class PapayaHttpClientFileName_TestProxy extends PapayaHttpClientFileName {
  public $_fileName = '';
}
