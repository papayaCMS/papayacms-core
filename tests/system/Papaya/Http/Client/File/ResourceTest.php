<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaHttpClientFileResourceTest extends \PapayaTestCase {

  private $_fileResource;

  public function setUp() {
    $this->_fileResource = fopen(__DIR__.'/DATA/sample.txt', 'rb');
  }

  public function tearDown() {
    if (is_resource($this->_fileResource)) {
      fclose($this->_fileResource);
    }
  }

  /**
  * @covers \PapayaHttpClientFileResource::__construct
  */
  public function testConstructor() {
    $file = new \PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $this->assertAttributeEquals('test', '_name', $file);
    $this->assertAttributeEquals('sample.txt', '_fileName', $file);
    $this->assertAttributeEquals('text/plain', '_mimeType', $file);
    $this->assertInternalType('resource', $this->readAttribute($file, '_resource'));
  }

  /**
  * @covers \PapayaHttpClientFileResource::__construct
  */
  public function testConstructorExpectingError() {
    $this->expectException(InvalidArgumentException::class);
    new \PapayaHttpClientFileResource('', '', NULL, '');
  }

  /**
  * @covers \PapayaHttpClientFileResource::getSize
  */
  public function testGetSize() {
    $file = new \PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $this->assertEquals(6, $file->getSize());
    $this->assertEquals(6, $file->getSize());
  }

  /**
  * @covers \PapayaHttpClientFileResource::send
  */
  public function testSend() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaHttpClientSocket $socket */
    $socket = $this->createMock(\PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('write')
           ->with($this->equalTo('sample'));
    $file = new \PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket);
  }

  /**
  * @covers \PapayaHttpClientFileResource::send
  */
  public function testSendLimited() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaHttpClientSocket $socket */
    $socket = $this->createMock(\PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('write')
           ->with($this->equalTo('samp'));
    $socket->expects($this->at(2))
           ->method('write')
           ->with($this->equalTo('le'));
    $file = new \PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket, FALSE, 4);
  }

  /**
  * @covers \PapayaHttpClientFileResource::send
  */
  public function testSendChunked() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaHttpClientSocket $socket */
    $socket = $this->createMock(\PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('writeChunk')
           ->with($this->equalTo('sample'));
    $socket->expects($this->at(2))
           ->method('writeChunk')
           ->with($this->equalTo("\r\n"));
    $file = new \PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket, TRUE);
  }

  /**
  * @covers \PapayaHttpClientFileResource::send
  */
  public function testSendInvalidResourceExpectingError() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaHttpClientSocket $socket */
    $socket = $this->createMock(\PapayaHttpClientSocket::class);
    $file = new \PapayaHttpClientFileResource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    fclose($this->_fileResource);
    $this->expectException(UnexpectedValueException::class);
    $file->send($socket, TRUE);
  }
}
