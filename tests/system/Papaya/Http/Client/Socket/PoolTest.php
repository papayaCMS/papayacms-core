<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaHttpClientSocketPoolTest extends PapayaTestCase {

  function testSetGetConnection() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'r');
    $pool->putConnection($resource, "example.com", 80);
    $this->assertSame($resource, $pool->getConnection("example.com", 80));
  }

  function testGetConnectionWithEmptyPool() {
    $pool = new PapayaHttpClientSocketPool();
    $this->assertNull($pool->getConnection("example.com", 80));
  }

  function testGetConnectionWithEmptiedPool() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'r');
    $pool->putConnection($resource, "example.com", 80);
    $pool->getConnection("example.com", 80);
    $this->assertNull($pool->getConnection("example.com", 80));
  }

  function testGetConnectionWithDifferentPort() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'r');
    $pool->putConnection($resource, "example.com", 80);
    $this->assertNull($pool->getConnection("example.com", 8080));
  }

  function testGetConnectionWithEof() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'r');
    fread($resource, 4);
    if (!feof($resource)) {
      $this->markTestSkipped('Resource can not be set to EOF');
    }
    $pool->putConnection($resource, "example.com", 80);
    $this->assertNull($pool->getConnection("example.com", 80));
    $this->assertFalse(is_resource($resource));
  }

}


