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
  * @covers \Papaya\Http\Client\File\Resource::__construct
  */
  public function testConstructor() {
    $file = new \Papaya\Http\Client\File\Resource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $this->assertAttributeEquals('test', '_name', $file);
    $this->assertAttributeEquals('sample.txt', '_fileName', $file);
    $this->assertAttributeEquals('text/plain', '_mimeType', $file);
    $this->assertInternalType('resource', $this->readAttribute($file, '_resource'));
  }

  /**
  * @covers \Papaya\Http\Client\File\Resource::__construct
  */
  public function testConstructorExpectingError() {
    $this->expectException(InvalidArgumentException::class);
    new \Papaya\Http\Client\File\Resource('', '', NULL, '');
  }

  /**
  * @covers \Papaya\Http\Client\File\Resource::getSize
  */
  public function testGetSize() {
    $file = new \Papaya\Http\Client\File\Resource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $this->assertEquals(6, $file->getSize());
    $this->assertEquals(6, $file->getSize());
  }

  /**
  * @covers \Papaya\Http\Client\File\Resource::send
  */
  public function testSend() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Http\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\Http\Client\Socket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('write')
           ->with($this->equalTo('sample'));
    $file = new \Papaya\Http\Client\File\Resource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket);
  }

  /**
  * @covers \Papaya\Http\Client\File\Resource::send
  */
  public function testSendLimited() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Http\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\Http\Client\Socket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('write')
           ->with($this->equalTo('samp'));
    $socket->expects($this->at(2))
           ->method('write')
           ->with($this->equalTo('le'));
    $file = new \Papaya\Http\Client\File\Resource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket, FALSE, 4);
  }

  /**
  * @covers \Papaya\Http\Client\File\Resource::send
  */
  public function testSendChunked() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Http\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\Http\Client\Socket::class);
    $socket->expects($this->at(0))
           ->method('isActive')
           ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
           ->method('writeChunk')
           ->with($this->equalTo('sample'));
    $socket->expects($this->at(2))
           ->method('writeChunk')
           ->with($this->equalTo("\r\n"));
    $file = new \Papaya\Http\Client\File\Resource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket, TRUE);
  }

  /**
  * @covers \Papaya\Http\Client\File\Resource::send
  */
  public function testSendInvalidResourceExpectingError() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Http\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\Http\Client\Socket::class);
    $file = new \Papaya\Http\Client\File\Resource(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    fclose($this->_fileResource);
    $this->expectException(UnexpectedValueException::class);
    $file->send($socket, TRUE);
  }
}
