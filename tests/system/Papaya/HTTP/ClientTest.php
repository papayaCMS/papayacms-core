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

namespace Papaya\HTTP;
require_once __DIR__.'/../../../bootstrap.php';

class ClientTest extends \Papaya\TestCase {

  public function testConstructor() {
    $client = new Client('http://www.papaya-cms.com/');
    $this->assertEquals(
      array(
        'scheme' => 'http',
        'host' => 'www.papaya-cms.com',
        'path' => '/'
      ),
      $this->readAttribute($client, '_url')
    );
  }

  public function testSetUrl() {
    $client = new Client();
    $client->setURL('http://www.papaya-cms.com:80/');
    $this->assertEquals(
      array(
        'scheme' => 'http',
        'host' => 'www.papaya-cms.com',
        'port' => '80',
        'path' => '/',
      ),
      $this->readAttribute($client, '_url')
    );
  }

  /**
   * @covers \Papaya\HTTP\Client::setURL
   */
  public function testReplaceUrl() {
    $client = new Client();
    $client->setURL('http://www.papaya-cms.com:80/');
    $client->setURL('http://www.example.com/');
    $this->assertEquals(
      array(
        'scheme' => 'http',
        'host' => 'www.example.com',
        'path' => '/',
      ),
      $this->readAttribute($client, '_url')
    );
  }

  public function testSetURLWithEmptyParameter() {
    $client = new Client();
    $this->expectException(\InvalidArgumentException::class);
    $client->setURL('');
  }

  public function testGetSocket() {
    $client = new Client('http://www.sample.tld');
    $this->assertInstanceOf(Client\Socket::class, $client->getSocket());
  }

  public function testSetSocket() {
    $client = new Client('http://www.sample.tld');
    $client->setSocket($socket = new Client\Socket);
    $this->assertAttributeSame($socket, '_socket', $client);
  }

  /**
   * @covers \Papaya\HTTP\Client::setRedirectLimit
   */
  public function testSetRedirectLimit() {
    $client = new Client('http://www.sample.tld');
    $client->setRedirectLimit(99);
    $this->assertAttributeSame(
      99,
      '_redirectLimit',
      $client
    );
  }

  /**
   * @covers \Papaya\HTTP\Client::getRedirectLimit
   */
  public function testGetRedirectLimit() {
    $client = new Client('http://www.sample.tld');
    $client->setRedirectLimit(99);
    $this->assertSame(
      99,
      $client->getRedirectLimit()
    );
  }

  public function testCloseOpenSocket() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->once())
      ->method('close')
      ->will($this->returnValue(TRUE));
    $client = new Client('http://www.sample.tld');
    $client->setSocket($socket);
    $this->assertTrue($client->close());
  }

  public function testCloseWithoutSocket() {
    $client = new Client('http://www.sample.tld');
    $this->assertFalse($client->close());
  }

  public function testSetMethod() {
    $client = new Client('http://www.sample.tld');
    $client->setMethod('Post');
    $this->assertEquals('POST', $this->readAttribute($client, '_method'));
  }

  public function testGetMethod() {
    $client = new Client('http://www.sample.tld');
    $this->assertEquals('GET', $client->getMethod());
  }

  public function testSetProxy() {
    $client = new Client('http://www.sample.tld');
    $client->setProxy('www.proxy.tld', 3128, 'username', 'password');
    $this->assertAttributeEquals(
      array(
        'host' => 'www.proxy.tld',
        'port' => 3128
      ),
      '_proxy',
      $client
    );
    $this->assertAttributeEquals(
      array(
        'user' => 'username',
        'password' => 'password'
      ),
      '_proxyAuthorization',
      $client
    );
  }

  public function testSetProxyHostOnly() {
    $client = new Client('http://www.sample.tld');
    $client->setProxy('www.proxy.tld');
    $this->assertAttributeEquals(
      array(
        'host' => 'www.proxy.tld',
        'port' => 80
      ),
      '_proxy',
      $client
    );
  }

  public function testInvalidSetProxy() {
    $client = new Client('http://www.sample.tld');
    $this->expectException(\InvalidArgumentException::class);
    $client->setProxy('');
  }

  /**
   * @dataProvider getHeaderSamples
   * @param array|NULL|string $header
   * @param array|string|NULL $value
   * @param $expectedHeader
   * @param array|string|NULL $expectedValue
   */
  public function testGetHeader(
    /** @noinspection PhpUnusedParameterInspection */
    $header, $value, $expectedHeader, $expectedValue
  ) {
    $client = new Client('http://www.sample.tld');
    $client->setHeader($header, $value);
    $this->assertSame($expectedValue, $client->getHeader($header));
  }

  public function testGetHeaderWithEmptyName() {
    $client = new Client('http://www.sample.tld');
    $this->assertNull($client->getHeader(''));
  }

  public function testGetHeaderWithNonExistingName() {
    $client = new Client('http://www.sample.tld');
    $this->assertNull($client->getHeader('NONEXISTING_HEADER_NAME'));
  }

  /**
   * @dataProvider getHeaderSamples
   * @param array|string|NULL $header
   * @param array|string|NULL $value
   * @param array|string|NULL $expectedHeader
   * @param array|string|NULL $expectedValue
   */
  public function testSetHeader($header, $value, $expectedHeader, $expectedValue) {
    $client = new Client('http://www.sample.tld');
    $client->setHeader($header, $value);
    $requestHeaders = $client->getRequestHeaders();
    $this->assertEquals($expectedValue, $requestHeaders[$expectedHeader]);
  }

  public function getHeaderSamples() {
    return array(
      'simple' => array(
        'X-Sample',
        'Test',
        'X-Sample',
        'Test',
      ),
      'lowercase' => array(
        'x-sample',
        'Test',
        'X-Sample',
        'Test',
      )
    );
  }

  public function testSetHeaderDuplicates() {
    $client = new Client('http://www.sample.tld');
    $client->setHeader('X-Sample', 'Test1');
    $client->setHeader('X-Sample', 'Test2', TRUE);
    $requestHeaders = $this->readAttribute($client, '_requestHeaders')->toArray();
    $this->assertEquals(array('Test1', 'Test2'), $requestHeaders['X-Sample']);
  }

  public function testSetHeaderEmpty() {
    $client = new Client('http://www.sample.tld');
    $client->setHeader('x-sample', 'Test');
    $requestHeaders = $this->readAttribute($client, '_requestHeaders')->toArray();
    $this->assertEquals('Test', $requestHeaders['X-Sample']);
    $client->setHeader('X-Sample', '');
    $requestHeaders = $this->readAttribute($client, '_requestHeaders')->toArray();
    $this->assertFalse(isset($requestHeaders['X-Sample']));
  }

  public function testSetHeaderInvalid() {
    $client = new Client('http://www.sample.tld');
    $this->assertFalse($client->setHeader(' ', 'Test'));
  }

  public function testSetHeaderEmptyNonExisting() {
    $client = new Client('http://www.sample.tld/');
    $this->assertFalse($client->setHeader('X-sample', ''));
  }

  public function testGetRequestHeaders() {
    $client = new Client('http://www.sample.tld');
    $expected = "GET / HTTP/1.1\r\nHost: www.sample.tld\r\n".
      "Accept: */*\r\nAccept-Charset: utf-8,*\r\nConnection: keep-alive\r\n";
    $this->assertEquals($expected, $client->getRequestHeaderString());
  }

  public function testGetRequestHeadersWithDuplicates() {
    $client = new Client('http://www.sample.tld');
    $client->setHeader('X-Sample', 'Test1');
    $client->setHeader('X-Sample', 'Test2', TRUE);
    $expected =
      "GET / HTTP/1.1\r\nHost: www.sample.tld\r\n".
      "Accept: */*\r\nAccept-Charset: utf-8,*\r\nConnection: keep-alive\r\n".
      "X-Sample: Test1\r\nX-Sample: Test2\r\n";
    $this->assertEquals($expected, $client->getRequestHeaderString());
  }

  public function testGetRequestHeadersWithData() {
    $client = new Client('http://www.sample.tld/');
    $client->addRequestData('foo', 'bar');
    $expected = "GET /?foo=bar HTTP/1.1\r\nHost: www.sample.tld\r\n".
      "Accept: */*\r\nAccept-Charset: utf-8,*\r\nConnection: keep-alive\r\n";
    $this->assertEquals($expected, $client->getRequestHeaderString());
  }

  public function testGetRequestHeadersWithQueryStringAndData() {
    $client = new Client('http://www.sample.tld/?bar=foo');
    $client->addRequestData('foo', 'bar');
    $expected = "GET /?bar=foo&foo=bar HTTP/1.1\r\nHost: www.sample.tld\r\n".
      "Accept: */*\r\nAccept-Charset: utf-8,*\r\nConnection: keep-alive\r\n";
    $this->assertEquals($expected, $client->getRequestHeaderString());
  }

  public function testGetRequestHeadersWithQueryStringToLarge() {
    $largeString = str_repeat('T', 5000);
    $maxString = str_repeat('T', 4043);
    $client = new Client('http://www.sample.tld/');
    $client->addRequestData('foo', $largeString);
    $expected = 'GET /?foo='.$maxString." HTTP/1.1\r\nHost: www.sample.tld\r\n".
      "Accept: */*\r\nAccept-Charset: utf-8,*\r\nConnection: keep-alive\r\n";
    $this->assertEquals($expected, $client->getRequestHeaderString());
  }

  public function testAddRequestData() {
    $client = new Client('http://www.sample.tld/');
    $data = array(
      'foo1' => 'bar1',
      'foo2' => 'bar2',
    );
    $client->addRequestData($data);
    $this->assertEquals($data, $this->readAttribute($client, '_requestData'));
  }

  public function testAddRequestDataRecursive() {
    $client = new Client('http://www.sample.tld/');
    $data = array(
      'foo' => array(1, 2, 3),
      'bar' => array(
        'foo1' => 'bar1',
        'foo2' => array(7, 8, 9)
      )
    );
    $expected = array(
      'foo[0]' => '1',
      'foo[1]' => '2',
      'foo[2]' => '3',
      'bar[foo1]' => 'bar1',
      'bar[foo2][0]' => '7',
      'bar[foo2][1]' => '8',
      'bar[foo2][2]' => '9'
    );
    $client->addRequestData($data);
    $this->assertEquals($expected, $this->readAttribute($client, '_requestData'));
  }

  public function testAddRequestFile() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\File $file */
    $file = $this->createMock(Client\File::class);
    $file->expects($this->once())
      ->method('getName')
      ->will($this->returnValue('test'));
    $client = new Client('http://www.sample.tld/');
    $this->assertTrue($client->addRequestFile($file));
  }

  public function testReadResponseHeaders() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->once())
      ->method('activateReadTimeout')
      ->with(20);
    $socket->expects($this->any())
      ->method('eof')
      ->will($this->returnValue(FALSE));
    $socket->expects($this->exactly(5))
      ->method('readLine')
      ->will(
        $this->onConsecutiveCalls(
          "HTTP/1.1 200 OK\r\n",
          "Date: Fri, 10 Jul 2009 13:32:20 GMT\r\n",
          "X-Sample: Line1\r\n",
          "\t Line2\r\n",
          "\r\n"
        )
      );
    $socket->expects($this->once())
      ->method('setContentLength')
      ->with($this->equalTo(-1));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->readResponseHeaders();
    $this->assertEquals(
      array(
        'Date' => 'Fri, 10 Jul 2009 13:32:20 GMT',
        'X-Sample' => 'Line1Line2'
      ),
      $client->getResponseHeaders()->toArray()
    );
  }

  public function testReadResponseHeadersWithConnectionClose() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->any())
      ->method('eof')
      ->will($this->returnValue(FALSE));
    $socket->expects($this->exactly(3))
      ->method('readLine')
      ->will(
        $this->onConsecutiveCalls(
          "HTTP/1.1 200 OK\r\n",
          "Connection: close\r\n",
          "\r\n"
        )
      );
    $socket->expects($this->once())
      ->method('setKeepAlive')
      ->with($this->equalTo(FALSE));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->readResponseHeaders();
  }

  public function testReadResponseHeadersWithChunkedHeader() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->any())
      ->method('eof')
      ->will($this->returnValue(FALSE));
    $socket->expects($this->exactly(3))
      ->method('readLine')
      ->will(
        $this->onConsecutiveCalls(
          "HTTP/1.1 200 OK\r\n",
          "Transfer-Encoding: chunked\r\n",
          "\r\n"
        )
      );
    $socket->expects($this->once())
      ->method('setContentLength')
      ->with($this->equalTo(-2));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->readResponseHeaders();
  }

  public function testReadResponseHeadersWithContentLength() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->any())
      ->method('eof')
      ->will($this->returnValue(FALSE));
    $socket->expects($this->exactly(3))
      ->method('readLine')
      ->will(
        $this->onConsecutiveCalls(
          "HTTP/1.1 200 OK\r\n",
          "Content-Length: 42\r\n",
          "\r\n"
        )
      );
    $socket->expects($this->once())
      ->method('setContentLength')
      ->with($this->equalTo(42));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->readResponseHeaders();
  }

  public function testReadResponseHeadersFromHeadWithContentLength() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->any())
      ->method('eof')
      ->will($this->returnValue(FALSE));
    $socket->expects($this->exactly(3))
      ->method('readLine')
      ->will(
        $this->onConsecutiveCalls(
          "HTTP/1.1 200 OK\r\n",
          "Content-Length: 42\r\n",
          "\r\n"
        )
      );
    $socket->expects($this->once())
      ->method('setContentLength')
      ->with($this->equalTo(0));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->setMethod('HEAD');
    $client->readResponseHeaders();
  }

  public function testGetResponseStatus() {
    $client = new Client('http://www.sample.tld/');
    $this->assertSame(0, $client->getResponseStatus());
  }

  public function testGetResponseHeader() {
    $client = new Client('http://www.sample.tld/');
    $client->getResponseHeaders()->set('Transfer-Encoding', 'chunked');
    $this->assertSame('chunked', $client->getResponseHeader('transfer-encoding'));
  }

  public function testGetResponseHeaderInvalid() {
    $client = new Client('http://www.sample.tld/');
    $this->assertNull($client->getResponseHeader('transfer-encoding'));
  }

  public function testGetResponseData() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->exactly(2))
      ->method('eof')
      ->will($this->onConsecutiveCalls(FALSE, TRUE));
    $socket->expects($this->once())
      ->method('read')
      ->will($this->returnValue('Hello World!'));
    $socket->expects($this->once())
      ->method('close');
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $this->assertEquals('Hello World!', $client->getResponseData());
  }

  public function testSendGetWithoutConnection() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(FALSE));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $this->assertFalse($client->send());
  }

  public function testSendGet() {
    $genericHeaders =
      "GET / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\n".
      "Accept-Charset: utf-8,*\r\nConnection: keep-alive\r\n";
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($genericHeaders));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo("\r\n"));
    $socket->expects($this->at(3))
      ->method('isActive')
      ->will($this->returnValue(FALSE));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $this->assertTrue($client->send());
  }

  public function testSendGetWithClose() {
    $genericHeaders =
      "GET / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\n".
      "Accept-Charset: utf-8,*\r\nConnection: close\r\n";
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('setKeepAlive')
      ->with($this->equalTo(FALSE));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo($genericHeaders));
    $socket->expects($this->at(3))
      ->method('write')
      ->with($this->equalTo("\r\n"));
    $socket->expects($this->at(4))
      ->method('isActive')
      ->will($this->returnValue(FALSE));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->setHeader('Connection', 'close');
    $this->assertTrue($client->send());
  }

  public function testSendGetUsingProxy() {
    $genericHeaders =
      "GET http://www.sample.tld:8080/ HTTP/1.1\r\n".
      "Host: www.sample.tld\r\n".
      "Proxy-Authorization: basic dXNlcjpwYXNz\r\n".
      "Accept: */*\r\n".
      "Accept-Charset: utf-8,*\r\n".
      "Connection: keep-alive\r\n";
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.proxy.tld'), $this->equalTo(3128))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($genericHeaders));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo("\r\n"));
    $socket->expects($this->at(3))
      ->method('isActive')
      ->will($this->returnValue(FALSE));
    $client = new Client('http://www.sample.tld:8080/');
    $client->setProxy('www.proxy.tld', 3128, 'user', 'pass');
    $client->setSocket($socket);
    $this->assertTrue($client->send());
  }

  public function testSendPost() {
    $genericHeaders =
      "POST / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\n".
      "Accept-Charset: utf-8,*\r\nConnection: keep-alive\r\n";
    $postDataAndHeaders =
      "Content-Type: application/x-www-form-urlencoded\r\n".
      "Content-Length: 10\r\n\r\nfoo=%3Dbar";
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($genericHeaders));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo($postDataAndHeaders));
    $socket->expects($this->at(3))
      ->method('isActive')
      ->will($this->returnValue(FALSE));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->setMethod('POST');
    $client->addRequestData('foo', '=bar');
    $this->assertTrue($client->send());
  }

  /**
   * @covers \Papaya\HTTP\Client::send
   * @covers \Papaya\HTTP\Client::_sendRawPostData
   */
  public function testSendPostRawData() {
    $genericHeaders =
      "POST / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\n".
      "Accept-Charset: utf-8,*\r\nConnection: keep-alive\r\nContent-Type: text/xml\r\n";
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($genericHeaders));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo(/** @lang Text */
        "\r\n<xml>testcontent</xml>\r\n"));
    $socket->expects($this->at(3))
      ->method('isActive')
      ->will($this->returnValue(FALSE));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->setMethod('POST');
    $client->setHeader('Content-Type', 'text/xml');
    $client->addRequestData('content', /** @lang XML */
      '<xml>testcontent</xml>');
    $this->assertTrue($client->send());
  }

  public function testSendPostWithFile() {
    $data = array(
      "POST / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\n".
      "Accept-Charset: utf-8,*\r\nConnection: keep-alive\r\n",
      "(Content-Type: multipart/form-data; boundary=\"-+[a-fA-F\d]{32}\"\r\n)",
      "Content-Length: 206\r\n\r\n",
      "(-+[a-fA-F\d]{32}\r\nContent-Disposition: form-data; name=\"foo\"\r\n\r\n=bar)",
      "(-+[a-fA-F\d]{32}\r\n\r\n)",
      "(-+[a-fA-F\d]{32}--\r\n)"
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($data[0]));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->matchesRegularExpression($data[1]));
    $socket->expects($this->at(3))
      ->method('write')
      ->with($this->equalTo($data[2]));
    $socket->expects($this->at(4))
      ->method('write')
      ->with($this->matchesRegularExpression($data[3]));
    $socket->expects($this->at(5))
      ->method('write')
      ->with($this->matchesRegularExpression($data[4]));
    $socket->expects($this->at(6))
      ->method('write')
      ->with($this->matchesRegularExpression($data[5]));
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\File $file */
    $file = $this->createMock(Client\File::class);
    $file->expects($this->once())
      ->method('getName')
      ->will($this->returnValue('testfile'));
    $file->expects($this->once())
      ->method('getSize')
      ->will($this->returnValue(1));
    $file->expects($this->once())
      ->method('getHeaders')
      ->will($this->returnValue(''));
    $file->expects($this->once())
      ->method('send')
      ->will($this->returnValue(''));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->setMethod('POST');
    $client->addRequestData('foo', '=bar');
    $client->addRequestFile($file);
    $this->assertTrue($client->send());
  }

  public function testSendPostChunkedWithFile() {
    $data = array(
      "POST / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\nAccept-Charset: utf-8,*".
      "\r\nConnection: keep-alive\r\nTransfer-Encoding: chunked\r\n",
      "(Content-Type: multipart/form-data; boundary=\"-+[a-fA-F\d]{32}\"\r\n)",
      "\r\n",
      "(-+[a-fA-F\d]{32}\r\nContent-Disposition: form-data; name=\"foo\"\r\n\r\n=bar)",
      "(-+[a-fA-F\d]{32}\r\n\r\n)",
      "(-+[a-fA-F\d]{32}--\r\n)"
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($data[0]));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->matchesRegularExpression($data[1]));
    $socket->expects($this->at(3))
      ->method('write')
      ->with($this->equalTo($data[2]));
    $socket->expects($this->at(4))
      ->method('writeChunk')
      ->with($this->matchesRegularExpression($data[3]));
    $socket->expects($this->at(5))
      ->method('writeChunk')
      ->with($this->matchesRegularExpression($data[4]));
    $socket->expects($this->at(6))
      ->method('writeChunk')
      ->with($this->matchesRegularExpression($data[5]));
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\File $file */
    $file = $this->createMock(Client\File::class);
    $file->expects($this->once())
      ->method('getName')
      ->will($this->returnValue('testfile'));
    $file->expects($this->once())
      ->method('getSize')
      ->will($this->returnValue(1));
    $file->expects($this->once())
      ->method('getHeaders')
      ->will($this->returnValue(''));
    $file->expects($this->once())
      ->method('send')
      ->will($this->returnValue(''));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->setMethod('POST');
    $client->setHeader('transfer-encoding', 'chunked');
    $client->addRequestData('foo', '=bar');
    $client->addRequestFile($file);
    $this->assertTrue($client->send());
  }

  public function testSendPut() {
    $data = array(
      "PUT / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\n".
      "Accept-Charset: utf-8,*\r\nConnection: keep-alive\r\n",
      "Content-Length: 1\r\n\r\n",
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($data[0]));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo($data[1]));
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\File $file */
    $file = $this->createMock(Client\File::class);
    $file->expects($this->once())
      ->method('getName')
      ->will($this->returnValue('testfile'));
    $file->expects($this->once())
      ->method('getSize')
      ->will($this->returnValue(1));
    $file->expects($this->once())
      ->method('send')
      ->will($this->returnValue(''));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->setMethod('PUT');
    $client->addRequestFile($file);
    $this->assertTrue($client->send());
  }

  public function testSendPutWithoutData() {
    $data = array(
      "PUT / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\n".
      "Accept-Charset: utf-8,*\r\nConnection: keep-alive\r\n",
      "Content-Length: 0\r\n\r\n",
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($data[0]));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo($data[1]));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->setMethod('PUT');
    $this->assertEquals(TRUE, $client->send());
  }

  public function testSendGetWithInternalRedirect() {
    $genericHeaders =
      "GET / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\n".
      "Accept-Charset: utf-8,*\r\nConnection: keep-alive\r\n";
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($genericHeaders));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo("\r\n"));
    $socket->expects($this->at(3))
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(4))
      ->method('activateReadTimeout')
      ->with(20)
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(5))
      ->method('eof')
      ->will($this->returnValue(FALSE));
    $socket->expects($this->at(6))
      ->method('readLine')
      ->will($this->returnValue('Location: http://www.redirected.tld'));
    $socket->expects($this->at(7))
      ->method('eof')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(8))
      ->method('close')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(9))
      ->method('open')
      ->with($this->equalTo('www.redirected.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $this->assertTrue($client->send());
  }

  public function testSendGetReturningRedirect() {
    $genericHeaders =
      "GET / HTTP/1.1\r\nHost: www.sample.tld\r\nAccept: */*\r\n".
      "Accept-Charset: utf-8,*\r\nConnection: keep-alive\r\n";
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client\Socket $socket */
    $socket = $this->createMock(Client\Socket::class);
    $socket->expects($this->at(0))
      ->method('open')
      ->with($this->equalTo('www.sample.tld'), $this->equalTo(80))
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(1))
      ->method('write')
      ->with($this->equalTo($genericHeaders));
    $socket->expects($this->at(2))
      ->method('write')
      ->with($this->equalTo("\r\n"));
    $socket->expects($this->at(3))
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(4))
      ->method('activateReadTimeout')
      ->with(20)
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(5))
      ->method('eof')
      ->will($this->returnValue(FALSE));
    $socket->expects($this->at(6))
      ->method('readLine')
      ->will($this->returnValue('Location: http://www.redirected.tld'));
    $socket->expects($this->at(7))
      ->method('eof')
      ->will($this->returnValue(TRUE));
    $socket->expects($this->at(8))
      ->method('close')
      ->will($this->returnValue(TRUE));
    $client = new Client('http://www.sample.tld/');
    $client->setSocket($socket);
    $client->setRedirectLimit(0);
    $this->assertTrue($client->send());
    $this->assertEquals(
      array(
        'Location' => 'http://www.redirected.tld'
      ),
      $this->readAttribute($client, '_responseHeaders')->toArray()
    );
  }

  public function testReset() {
    $client = new Client('http://www.sample.tld/');
    $originalData = $this->readAttribute($client, '_requestData');
    $client->addRequestData('foo', 'bar');
    $client->setHeader('X-Foo', 'bar');
    $client->reset();
    $this->assertNull($this->readAttribute($client, '_requestHeaders'));
    $this->assertNull($this->readAttribute($client, '_responseHeaders'));
    $this->assertEquals(
      $originalData, $this->readAttribute($client, '_requestData')
    );
  }
}
