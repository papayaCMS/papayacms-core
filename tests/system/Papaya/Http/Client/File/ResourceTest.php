<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaHttpClientFileResourceTest extends PapayaTestCase {

  function setUp() {
    $this->_fileResource = fopen(dirname(__FILE__).'/DATA/sample.txt', 'r');
  }

  function tearDown() {
    if (is_resource($this->_fileResource)) {
      fclose($this->_fileResource);
    }
  }

  /**
  * @covers PapayaHttpClientFileResource::__construct
  */
  function testConstructor() {
    $fileName = dirname(__FILE__);
    $file = new PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $this->assertAttributeEquals('test', '_name', $file);
    $this->assertAttributeEquals('sample.txt', '_fileName', $file);
    $this->assertAttributeEquals('text/plain', '_mimeType', $file);
    $this->assertTrue(is_resource($this->readAttribute($file, '_resource')));
  }

  /**
  * @covers PapayaHttpClientFileResource::__construct
  */
  function testConstructorExpectingError() {
    $this->setExpectedException('InvalidArgumentException');
    new PapayaHttpClientFileResource('', '', '', '');
  }

  /**
  * @covers PapayaHttpClientFileResource::getSize
  */
  function testGetSize() {
    $file = new PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $this->assertEquals(6, $file->getSize());
    $this->assertEquals(6, $file->getSize());
  }

  /**
  * @covers PapayaHttpClientFileResource::send
  */
  function testSend() {
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('write')
           ->with($this->equalTo('sample'));
    $file = new PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket);
  }

  /**
  * @covers PapayaHttpClientFileResource::send
  */
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
    $file = new PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket, FALSE, 4);
  }

  /**
  * @covers PapayaHttpClientFileResource::send
  */
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
    $file = new PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket, TRUE);
  }

  /**
  * @covers PapayaHttpClientFileResource::send
  */
  function testSendInvalidResourceExpectingError() {
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $file = new PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    fclose($this->_fileResource);
    $this->setExpectedException('UnexpectedValueException');
    $file->send($socket, TRUE);
  }
}
