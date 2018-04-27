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

class PapayaHttpClientSocketPoolTest extends PapayaTestCase {

  public function testSetGetConnection() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'rb');
    $pool->putConnection($resource, 'example.com', 80);
    $this->assertSame($resource, $pool->getConnection('example.com', 80));
  }

  public function testGetConnectionWithEmptyPool() {
    $pool = new PapayaHttpClientSocketPool();
    $this->assertNull($pool->getConnection('example.com', 80));
  }

  public function testGetConnectionWithEmptiedPool() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'rb');
    $pool->putConnection($resource, 'example.com', 80);
    $pool->getConnection('example.com', 80);
    $this->assertNull($pool->getConnection('example.com', 80));
  }

  public function testGetConnectionWithDifferentPort() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'rb');
    $pool->putConnection($resource, 'example.com', 80);
    $this->assertNull($pool->getConnection('example.com', 8080));
  }

  public function testGetConnectionWithEof() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'rb');
    fread($resource, 4);
    if (!feof($resource)) {
      $this->markTestSkipped('Resource can not be set to EOF');
    }
    $pool->putConnection($resource, 'example.com', 80);
    $this->assertNull($pool->getConnection('example.com', 80));
    $this->assertNotInternalType('resource', $resource);
  }

}


