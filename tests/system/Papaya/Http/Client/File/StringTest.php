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

class PapayaHttpClientFileStringTest extends \PapayaTestCase {

  private $_fileContents;

  public function setUp() {
    $this->_fileContents = file_get_contents(__DIR__.'/DATA/sample.txt');
  }

  public function testConstructor() {
    $file = new \PapayaHttpClientFileString(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $this->assertAttributeEquals('test', '_name', $file);
    $this->assertAttributeEquals('sample.txt', '_fileName', $file);
    $this->assertAttributeEquals('text/plain', '_mimeType', $file);
    $this->assertAttributeEquals($this->_fileContents, '_data', $file);
  }

  public function testConstructorExpectingError() {
    $this->expectError(E_WARNING);
    new \PapayaHttpClientFileString('', '', '', '');
  }

  public function testGetSize() {
    $file = new \PapayaHttpClientFileString(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $this->assertEquals(6, $file->getSize());
    $this->assertEquals(6, $file->getSize());
  }

  public function testSend() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaHttpClientSocket $socket */
    $socket = $this->createMock(\PapayaHttpClientSocket::class);
    $socket
      ->expects($this->at(0))
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket
      ->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo('sample'));
    $file = new \PapayaHttpClientFileString(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $file->send($socket);
  }

  public function testSendChunked() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaHttpClientSocket $socket */
    $socket = $this->createMock(\PapayaHttpClientSocket::class);
    $socket
      ->expects($this->at(0))
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket
      ->expects($this->at(1))
      ->method('writeChunk')
      ->with($this->equalTo('sample'));
    $socket
      ->expects($this->at(2))
      ->method('writeChunk')
      ->with($this->equalTo("\r\n"));
    $file = new \PapayaHttpClientFileString(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $file->send($socket, TRUE);
  }
}
