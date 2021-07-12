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

namespace Papaya\HTTP\Client\File;
require_once __DIR__.'/../../../../../bootstrap.php';

class StreamTest extends \Papaya\TestFramework\TestCase {

  private $_fileResource;

  public function setUp(): void {
    $this->_fileResource = fopen(__DIR__.'/DATA/sample.txt', 'rb');
  }

  public function tearDown(): void {
    if (is_resource($this->_fileResource)) {
      fclose($this->_fileResource);
    }
  }

  /**
   * @covers \Papaya\HTTP\Client\File\Stream::__construct
   */
  public function testConstructor() {
    $file = new Stream(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $this->assertEquals('test', $file->getName());
    $this->assertEquals('sample.txt', $file->getFileName());
    $this->assertEquals('text/plain', $file->getMimeType());
    $this->assertIsResource($file->getResource());
  }

  /**
   * @covers \Papaya\HTTP\Client\File\Stream::__construct
   */
  public function testConstructorExpectingError() {
    $this->expectException(\UnexpectedValueException::class);
    new Stream('', '', NULL, '');
  }

  /**
   * @covers \Papaya\HTTP\Client\File\Stream::getSize
   */
  public function testGetSize() {
    $file = new Stream(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $this->assertEquals(6, $file->getSize());
    $this->assertEquals(6, $file->getSize());
  }

  /**
   * @covers \Papaya\HTTP\Client\File\Stream::send
   */
  public function testSend() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\HTTP\Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo('sample'));
    $file = new Stream(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket);
  }

  /**
   * @covers \Papaya\HTTP\Client\File\Stream::send
   */
  public function testSendLimited() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\HTTP\Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo('samp'));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo('le'));
    $file = new Stream(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket, FALSE, 4);
  }

  /**
   * @covers \Papaya\HTTP\Client\File\Stream::send
   */
  public function testSendChunked() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\HTTP\Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('writeChunk')
      ->with($this->equalTo('sample'));
    $socket->expects($this->at(2))
      ->method('writeChunk')
      ->with($this->equalTo("\r\n"));
    $file = new Stream(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    $file->send($socket, TRUE);
  }

  /**
   * @covers \Papaya\HTTP\Client\File\Stream::send
   */
  public function testSendInvalidResourceExpectingError() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\HTTP\Client\Socket::class);
    $file = new Stream(
      'test', 'sample.txt', $this->_fileResource, 'text/plain'
    );
    fclose($this->_fileResource);
    $this->expectException(\UnexpectedValueException::class);
    $file->send($socket, TRUE);
  }
}
