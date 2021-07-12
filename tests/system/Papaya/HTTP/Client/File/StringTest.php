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

class StringTest extends \Papaya\TestFramework\TestCase {

  private $_fileContents;

  public function setUp(): void {
    $this->_fileContents = file_get_contents(__DIR__.'/DATA/sample.txt');
  }

  public function testConstructorExpectingError() {
    $this->expectException(\UnexpectedValueException::class);
    new Text('', '', '', '');
  }

  public function testGetSize() {
    $file = new Text(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $this->assertEquals(6, $file->getSize());
    $this->assertEquals(6, $file->getSize());
  }

  public function testSend() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\HTTP\Client\Socket::class);
    $socket
      ->expects($this->at(0))
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket
      ->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo('sample'));
    $file = new Text(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $file->send($socket);
  }

  public function testSendChunked() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket $socket */
    $socket = $this->createMock(\Papaya\HTTP\Client\Socket::class);
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
    $file = new Text(
      'test', 'sample.txt', $this->_fileContents, 'text/plain'
    );
    $file->send($socket, TRUE);
  }
}
