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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaHttpClientSocketTest extends \PapayaTestCase {

  public function getMemoryStreamFixture($data = '', $mode = 'wb+') {
    $ms = fopen('php://memory', $mode);
    if (!empty($data)) {
      fwrite($ms, $data);
      fseek($ms, 0);
    }
    return $ms;
  }

  public function testOpen() {
    $host = 'www.papaya-cms.com';
    $port = 80;
    $testResource = @fsockopen($host, $port);
    if (is_resource($testResource)) {
      fclose($testResource);
      $socket = new \Papaya\HTTP\Client\Socket();
      $socket->open($host, $port);
      $this->assertInternalType('resource', $this->readAttribute($socket, '_resource'));
      $this->assertAttributeSame($host, '_host', $socket);
      $this->assertAttributeSame($port, '_port', $socket);
      fclose($this->readAttribute($socket, '_resource'));
    } else {
      $this->markTestSkipped(
        'Can not open connection to '.$host.':'.$port
      );
    }
  }

  public function testOpenWithPool() {
    $host = 'www.papaya-cms.com';
    $port = 80;
    $socket = new \Papaya\HTTP\Client\Socket();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket\Pool $pool */
    $pool = $this->createMock(\Papaya\HTTP\Client\Socket\Pool::class);
    $ms = $this->getMemoryStreamFixture('TEST');
    $pool
      ->expects($this->once())
      ->method('getConnection')
      ->with($this->equalTo($host), $this->equalTo($port))
      ->will($this->returnValue($ms));
    $socket->setPool($pool);
    $socket->open($host, $port);
    $this->assertAttributeSame($ms, '_resource', $socket);
    $this->assertAttributeSame($host, '_host', $socket);
    $this->assertAttributeSame($port, '_port', $socket);
    fclose($this->readAttribute($socket, '_resource'));
  }

  public function testOpenFailure() {
    $host = 'INVALID_HOSTNAME_FOR_TEST';
    $port = 80;
    $socket = new \Papaya\HTTP\Client\Socket();
    $this->assertFalse(
      $socket->open($host, $port)
    );
  }

  public function testRead() {
    $ms = $this->getMemoryStreamFixture('TEST');
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(4);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket\Pool $pool */
    $pool = $this->createMock(\Papaya\HTTP\Client\Socket\Pool::class);
    $pool
      ->expects($this->once())
      ->method('putConnection');
    $socket->setPool($pool);
    $this->assertEquals('TEST', $socket->read());
  }

  public function testReadWithoutContentLength() {
    $ms = $this->getMemoryStreamFixture('TEST');
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $this->assertEquals('TEST', $socket->read(99));
  }

  public function testReadLimited() {
    $ms = $this->getMemoryStreamFixture('TEST');
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(4);
    $this->assertEquals('TE', $socket->read(2));
  }

  public function testReadLine() {
    $ms = $this->getMemoryStreamFixture("TEST1\r\nTEST2");
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $this->assertEquals("TEST1\r\n", $socket->readLine());
  }

  public function testReadChunked() {
    $ms = $this->getMemoryStreamFixture("4\r\nTEST\r\n0\r\n\r\n");
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(-2);
    $this->assertEquals('TEST', $socket->read());
    $this->assertEquals(9, ftell($ms));
  }

  public function testReadChunkedUppercaseSize() {
    $ms = $this->getMemoryStreamFixture("B\r\ntest_test_1\r\n0\r\n\r\n");
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(-2);
    $this->assertEquals('test_test_1', $socket->read());
    $this->assertEquals(16, ftell($ms));
  }

  public function testReadChunkedEmptyChunk() {
    $ms = $this->getMemoryStreamFixture("0\r\n\r\n");
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(-2);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket\Pool $pool */
    $pool = $this->createMock(\Papaya\HTTP\Client\Socket\Pool::class);
    $pool
      ->expects($this->once())
      ->method('putConnection');
    $socket->setPool($pool);
    $this->assertFalse($socket->read());
    $this->assertEquals(5, ftell($ms));
  }

  public function testReadWithInvalidContentLength() {
    $ms = $this->getMemoryStreamFixture("0\r\n\r\n");
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(-99);
    $this->assertFalse($socket->read());
  }

  public function testWrite() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->write('TEST');
    fseek($ms, 0);
    $this->assertEquals('TEST', fread($ms, 2048));
  }

  public function testWriteLinebreak() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->writeLineBreak(5);
    fseek($ms, 0);
    $this->assertEquals("\r\n\r\n\r\n\r\n\r\n", fread($ms, 2048));
  }

  public function testWriteChunk() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->writeChunk('TEST');
    fseek($ms, 0);
    $this->assertEquals("4\r\nTEST\r\n", fread($ms, 2048));
  }

  public function testWriteChunkEnd() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->writeChunkEnd();
    fseek($ms, 0);
    $this->assertEquals("0\r\n\r\n", fread($ms, 2048));
  }

  public function testEofAtStartExpectingFalse() {
    $ms = $this->getMemoryStreamFixture('TEST');
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $this->assertFalse($socket->eof());
  }

  public function testEofAtEndExpectingTrue() {
    $ms = $this->getMemoryStreamFixture();
    fgets($ms);
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $this->assertTrue($socket->eof());
  }

  public function testEofWithoutResourceExpectingTrue() {
    $socket = new \Papaya\HTTP\Client\Socket();
    $this->assertTrue($socket->eof());
  }

  public function testEofWithContentLengthZeroExpectingTrue() {
    $ms = $this->getMemoryStreamFixture('TEST');
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(0);
    $this->assertTrue($socket->eof());
  }

  public function testActivateReadTimeoutOnMemoryStreamExpectingFalse() {
    $ms = $this->getMemoryStreamFixture('TEST');
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $this->assertFalse($socket->activateReadTimeout(40));
  }

  public function testClose() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(0);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket\Pool $pool */
    $pool = $this->createMock(\Papaya\HTTP\Client\Socket\Pool::class);
    $pool
      ->expects($this->once())
      ->method('putConnection');
    $socket->setPool($pool);
    $this->assertTrue($socket->close());
    $this->assertNull($this->readAttribute($socket, '_resource'));
  }

  public function testCloseWithOutstandingData() {
    $ms = $this->getMemoryStreamFixture('TEST');
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(4);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket\Pool $pool */
    $pool = $this->createMock(\Papaya\HTTP\Client\Socket\Pool::class);
    $pool
      ->expects($this->once())
      ->method('putConnection');
    $socket->setPool($pool);
    $this->assertTrue($socket->close());
    $this->assertNull($this->readAttribute($socket, '_resource'));
    $this->assertEquals(4, ftell($ms));
  }

  public function testCloseWithTooMuchOutstandingData() {
    $ms = $this->getMemoryStreamFixture('TEST');
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(10 * 1024 * 1024);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket\Pool $pool */
    $pool = $this->createMock(\Papaya\HTTP\Client\Socket\Pool::class);
    $pool
      ->expects($this->never())
      ->method('putConnection');
    $socket->setPool($pool);
    $this->assertTrue($socket->close());
    $this->assertNull($this->readAttribute($socket, '_resource'));
  }

  public function testCloseWithChunked() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->setContentLength(-1);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket\Pool $pool */
    $pool = $this->createMock(\Papaya\HTTP\Client\Socket\Pool::class);
    $pool
      ->expects($this->never())
      ->method('putConnection');
    $socket->setPool($pool);
    $this->assertTrue($socket->close());
    $this->assertNull($this->readAttribute($socket, '_resource'));
  }

  public function testCloseInvalid() {
    $socket = new \Papaya\HTTP\Client\Socket();
    $this->assertFalse($socket->close());
  }

  public function testCloseWithoutKeepAlive() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setKeepAlive(FALSE);
    $socket->setResource($ms);
    $socket->setContentLength(0);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client\Socket\Pool $pool */
    $pool = $this->createMock(\Papaya\HTTP\Client\Socket\Pool::class);
    $pool
      ->expects($this->never())
      ->method('putConnection');
    $socket->setPool($pool);
    $this->assertTrue($socket->close());
    $this->assertNull($this->readAttribute($socket, '_resource'));
  }

  public function testGetPool() {
    $socket = new \Papaya\HTTP\Client\Socket();
    $this->assertInstanceOf(\Papaya\HTTP\Client\Socket\Pool::class, $socket->getPool());
  }

  public function testSetPool() {
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setPool(new \Papaya\HTTP\Client\Socket\Pool);
    $this->assertAttributeInstanceOf(\Papaya\HTTP\Client\Socket\Pool::class, '_pool', $socket);
  }

  public function testSetKeepAliveWithInvalidType() {
    $socket = new \Papaya\HTTP\Client\Socket();
    $this->assertTrue($socket->setKeepAlive(0));
  }

  public function testSetKeepAliveWithFalse() {
    $socket = new \Papaya\HTTP\Client\Socket();
    $this->assertFalse($socket->setKeepAlive(FALSE));
  }

  public function testSetKeepAliveWithTrue() {
    $socket = new \Papaya\HTTP\Client\Socket();
    $this->assertTrue($socket->setKeepAlive(TRUE));
  }

  /**
  * @covers \Papaya\HTTP\Client\Socket::activateReadTimeout
  */
  public function testActivateReadTimeoutOnMemoryStream() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $this->assertFalse($socket->activateReadTimeout(42));
  }

  /**
  * @covers \Papaya\HTTP\Client\Socket::activateReadTimeout
  */
  public function testActivateReadTimeoutWithoutResource() {
    $socket = new \Papaya\HTTP\Client\Socket();
    $this->assertFalse($socket->activateReadTimeout(42));
  }

  /**
  * @covers \Papaya\HTTP\Client\Socket::hasTimedOut
  */
  public function testHasTimedOutExpectingFalse() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $this->assertFalse($socket->hasTimedOut());
  }

  /**
  * @covers \Papaya\HTTP\Client\Socket::closeOnTimeout
  */
  public function testCloseOnTimeout() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \Papaya\HTTP\Client\Socket();
    $socket->setResource($ms);
    $socket->readLine();
    $this->assertTrue($socket->isActive());
  }

  /**
  * @covers \Papaya\HTTP\Client\Socket::closeOnTimeout
  */
  public function testCloseOnTimeoutResourceClosed() {
    $ms = $this->getMemoryStreamFixture();
    $socket = new \PapayaHttpClientSocket_TestProxyForTimeout();
    $socket->setResource($ms);
    $socket->readLine();
    $this->assertFalse($socket->isActive());
  }
}

class PapayaHttpClientSocket_TestProxyForTimeout extends \Papaya\HTTP\Client\Socket {
  public function hasTimedOut() {
    return TRUE;
  }
}
