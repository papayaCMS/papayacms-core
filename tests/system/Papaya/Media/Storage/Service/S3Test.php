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

namespace Papaya\Media\Storage\Service {

  use Papaya\Cache\Service as CacheService;
  use Papaya\Configuration;
  use Papaya\HTTP\Client as HTTPClient;
  use Papaya\HTTP\Client\Socket;
  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\Media\Storage\Service\S3
   */
  class S3Test extends TestCase {

    /**
     * @return Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockConfigurationObjectFixture() {
      $configuration = $this->mockPapaya()->options(
        [
          'PAPAYA_MEDIA_STORAGE_S3_BUCKET' => 'sample_bucket',
          'PAPAYA_MEDIA_STORAGE_S3_KEYID' => 'sample_key_id',
          'PAPAYA_MEDIA_STORAGE_S3_KEY' => 'sample_key',
          'PAPAYA_MEDIA_STORAGE_SUBDIRECTORY' => 'd/i/rectory',
          'PAPAYA_MEDIA_STORAGE_DIRECTORY_DEPTH' => 0
        ]
      );
      return $configuration;
    }

    /**
     * @param $response
     * @param int $status
     * @return \PHPUnit_Framework_MockObject_MockObject|HTTPClient
     */
    private function getMockHTTPClient($response, $status = 200) {
      $client = $this->createMock(HTTPClient::class);
      $client
        ->expects($this->once())
        ->method('send');
      if (is_int($status)) {
        $client
          ->expects($this->once())
          ->method('getResponseStatus')
          ->willReturn($status);
      } else {
        $client
          ->expects($this->never())
          ->method('getResponseStatus');
      }
      if (is_string($response)) {
        $client
          ->expects($this->once())
          ->method('getResponseData')
          ->willReturn($response);
      } else {
        $client
          ->expects($this->never())
          ->method('getResponseData');
      }
      if (is_object($response)) {
        $client
          ->expects($this->once())
          ->method('getSocket')
          ->willReturn($response);
      } else {
        $client
          ->expects($this->never())
          ->method('getSocket');
      }
      return $client;
    }

    public function testAllowPublic() {
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $this->assertTrue($service->allowPublic());
    }

    public function testSetHandler() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertSame(
        $handler, $service->getHandler()
      );
    }

    public function testSetHTTPClient() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $client = new HTTPClient();
      $handler
        ->expects($this->once())
        ->method('setHTTPClient')
        ->with($this->equalTo($client));
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->setHandler($handler);
      $service->setHTTPClient($client);
    }

    public function testBrowse() {
      $xmlResponse = /** @lang XML */
        '<?xml version="1.0" encoding="UTF-8"?>
      <ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
        <Name>sample_bucket</Name>
        <Prefix/>
        <IsTruncated>false</IsTruncated>
        <Contents>
          <Key>sample_group/sample_file</Key>
        </Contents>
      </ListBucketResult>';
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com'),
          $this->equalTo('GET'),
          ['prefix' => 'sample_group/']
        )
        ->willReturn($this->getMockHTTPClient($xmlResponse));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertSame(
        [
          'sample_file'
        ],
        $service->browse('sample_group')
      );
    }

    public function testBrowseWithStartString() {
      $xmlResponse = /** @lang XML */
        '<?xml version="1.0" encoding="UTF-8"?>
      <ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
        <Name>sample_bucket</Name>
        <Prefix>sample</Prefix>
        <IsTruncated>false</IsTruncated>
        <Contents>
          <Key>sample_group/sample_file</Key>
        </Contents>
      </ListBucketResult>';
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com'),
          $this->equalTo('GET'),
          ['prefix' => 'sample_group/sample']
        )
        ->willReturn($this->getMockHTTPClient($xmlResponse));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertSame(
        [
          'sample_file'
        ],
        $service->browse('sample_group', 'sample')
      );
    }

    public function testBrowseWithHttpError() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com'),
          $this->equalTo('GET'),
          ['prefix' => 'sample_group/']
        )
        ->willReturn($this->getMockHTTPClient(NULL, 404));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertSame(
        [],
        $service->browse('sample_group')
      );
    }

    public function testGet() {
      $dataResponse = 'SAMPLE_DATA';
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
        )
        ->willReturn($this->getMockHTTPClient($dataResponse));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertSame(
        'SAMPLE_DATA',
        $service->get('sample_group', 'sample_id')
      );
    }

    public function testGetWithHttpError() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
        )
        ->willReturn($this->getMockHTTPClient(NULL, 404));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertNull(
        $service->get('sample_group', 'sample_id')
      );
    }

    public function testGetWithDirectoryDepth() {
      $dataResponse = 'SAMPLE_DATA';
      $configuration = $this->mockPapaya()->options(
        [
          'PAPAYA_MEDIA_STORAGE_S3_BUCKET' => 'sample_bucket',
          'PAPAYA_MEDIA_STORAGE_S3_KEYID' => 'sample_key_id',
          'PAPAYA_MEDIA_STORAGE_S3_KEY' => 'sample_key',
          'PAPAYA_MEDIA_STORAGE_SUBDIRECTORY' => 'd/i/rectory',
          'PAPAYA_MEDIA_STORAGE_DIRECTORY_DEPTH' => 3
        ]
      );
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo(
            'http://sample_bucket.s3.amazonaws.com/'.
            'd/i/rectory/sample_group/s/a/m/sample_id'
          )
        )
        ->willReturn($this->getMockHTTPClient($dataResponse));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertSame(
        'SAMPLE_DATA',
        $service->get('sample_group', 'sample_id')
      );
    }

    public function testGetLocalFile() {
      $dataResponse = 'SAMPLE_DATA';
      $configuration = $this->getMockConfigurationObjectFixture();
      $socket = $this->createMock(Socket::class);
      $socket->expects($this->exactly(2))
        ->method('eof')
        ->will(
          $this->onConsecutiveCalls(
            $this->returnValue(FALSE),
            $this->returnValue(TRUE)
          )
        );
      $socket
        ->expects($this->once())
        ->method('read')
        ->willReturn($dataResponse);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
        )
        ->willReturn($this->getMockHTTPClient($socket));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $localFile = $service->getLocalFile('sample_group', 'sample_id');
      $this->assertSame('SAMPLE_DATA', file_get_contents($localFile['filename']));
      unlink($localFile['filename']);
      $this->assertTrue($localFile['is_temporary']);
    }

    public function testGetLocalFileExpectingFalse() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
        )
        ->willReturn($this->getMockHTTPClient(NULL, 404));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertFalse($service->getLocalFile('sample_group', 'sample_id'));
    }

    public function testOutput() {
      $dataResponse = 'SAMPLE_DATA';
      $configuration = $this->getMockConfigurationObjectFixture();
      $socket = $this->createMock(Socket::class);
      $socket
        ->expects($this->at(0))
        ->method('eof')
        ->willReturn(FALSE);
      $socket
        ->expects($this->at(1))
        ->method('read')
        ->willReturn($dataResponse);
      $socket
        ->expects($this->at(2))
        ->method('eof')
        ->willReturn(TRUE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
        )
        ->willReturn($this->getMockHTTPClient($socket));
      $service = new S3($configuration);
      $service->setHandler($handler);
      ob_start();
      $service->output('sample_group', 'sample_id');
      $this->assertSame('SAMPLE_DATA', ob_get_clean());
    }

    public function testOutputWithHttpError() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
        )
        ->willReturn($this->getMockHTTPClient(NULL, 404));
      $service = new S3($configuration);
      $service->setHandler($handler);
      ob_start();
      $service->output('sample_group', 'sample_id');
      $this->assertSame('', ob_get_clean());
    }

    public function testOutputWithRangeFrom() {
      $dataResponse = 'SAMPLE_DATA';
      $configuration = $this->getMockConfigurationObjectFixture();
      $socket = $this->createMock(Socket::class);
      $socket
        ->expects($this->at(0))
        ->method('eof')
        ->willReturn(FALSE);
      $socket
        ->expects($this->at(1))
        ->method('read')
        ->willReturn($dataResponse);
      $socket
        ->expects($this->at(2))
        ->method('eof')
        ->willReturn(TRUE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
        )
        ->willReturn($this->getMockHTTPClient($socket, 206));
      $service = new S3($configuration);
      $service->setHandler($handler);
      ob_start();
      $service->output('sample_group', 'sample_id', 5);
      $this->assertSame('SAMPLE_DATA', ob_get_clean());
    }

    public function testOutputWithRangeFromAndTo() {
      $dataResponse = 'SAMPLE_DATA';
      $configuration = $this->getMockConfigurationObjectFixture();
      $socket = $this->createMock(Socket::class);
      $socket
        ->expects($this->at(0))
        ->method('eof')
        ->willReturn(FALSE);
      $socket
        ->expects($this->at(1))
        ->method('read')
        ->willReturn($dataResponse);
      $socket
        ->expects($this->at(2))
        ->method('eof')
        ->willReturn(TRUE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
        )
        ->willReturn($this->getMockHTTPClient($socket, 206));
      $service = new S3($configuration);
      $service->setHandler($handler);
      ob_start();
      $service->output('sample_group', 'sample_id', 5, 10);
      $this->assertSame('SAMPLE_DATA', ob_get_clean());
    }

    public function testStoreWithStringAndPrivateStatus() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('PUT'),
          $this->equalTo([]),
          $this->equalTo(
            [
              'Content-Type' => 'application/octet-stream',
              'x-amz-acl' => 'private',
            ]
          )
        )
        ->willReturn($this->getMockHTTPClient(NULL));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertTrue($service->store('sample_group', 'sample_id', 'SAMPLE_DATA'));
    }

    public function testStoreWithResourceAndPublicStatus() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('PUT'),
          $this->equalTo([]),
          $this->equalTo(
            [
              'Content-Type' => 'application/octet-stream',
              'x-amz-acl' => 'public-read',
            ]
          )
        )
        ->willReturn($this->getMockHTTPClient(NULL));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertTrue(
        $service->store(
          'sample_group',
          'sample_id',
          fopen('data://text/plain,SAMPLE_DATA', 'rb'),
          'application/octet-stream',
          TRUE
        )
      );
    }

    public function testStoreLocalFileWithPrivateStatus() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('PUT'),
          $this->equalTo([]),
          $this->equalTo(
            [
              'Content-Type' => 'application/octet-stream',
              'x-amz-acl' => 'private',
            ]
          )
        )
        ->willReturn($this->getMockHTTPClient(NULL));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertTrue(
        $service->storeLocalFile(
          'sample_group',
          'sample_id',
          __FILE__
        )
      );
    }

    public function testStoreLocalFileWithPublicStatus() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('PUT'),
          $this->equalTo([]),
          $this->equalTo(
            [
              'Content-Type' => 'application/octet-stream',
              'x-amz-acl' => 'public-read',
            ]
          )
        )
        ->willReturn($this->getMockHTTPClient(NULL));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertTrue(
        $service->storeLocalFile(
          'sample_group',
          'sample_id',
          __FILE__,
          'application/octet-stream',
          TRUE
        )
      );
    }

    public function testRemove() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('DELETE')
        )
        ->willReturn($this->getMockHTTPClient(NULL, 204));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertTrue($service->remove('sample_group', 'sample_id'));
    }

    public function testExists() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('HEAD')
        )
        ->willReturn($this->getMockHTTPClient(NULL));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertTrue($service->exists('sample_group', 'sample_id'));
    }

    public function testExistsForNonexistingFile() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('HEAD')
        )
        ->willReturn($this->getMockHTTPClient(NULL, 404));
      $service = new S3($configuration);
      $service->setHandler($handler);
      $this->assertFalse($service->exists('sample_group', 'sample_id'));
    }

    public function testIsPublicWithPublicFile() {
      $responseXML =
        /** @lang Text */
        '<?xml version="1.0" encoding="UTF-8"?>
      <AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
        <AccessControlList>
          <Grant>
            <Grantee xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="Group">
              <URI>http://acs.amazonaws.com/groups/global/AllUsers</URI>
            </Grantee>
            <Permission>READ</Permission>
          </Grant>
        </AccessControlList>
      </AccessControlPolicy>';
      $client = $this->createMock(HTTPClient::class);
      $client
        ->expects($this->exactly(2))
        ->method('send');
      $client
        ->expects($this->exactly(2))
        ->method('getResponseStatus')
        ->willReturn(200);
      $client
        ->expects($this->once())
        ->method('getResponseHeader')
        ->with($this->equalTo('Content-Type'))
        ->willReturn('image/gif');
      $client
        ->expects($this->once())
        ->method('getResponseData')
        ->willReturn($responseXML);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->exactly(2))
        ->method('setUpRequest')
        ->with(
          $this->logicalOr(
            'http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id',
            'http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id?acl'
          ),
          $this->logicalOr('HEAD', 'GET')
        )
        ->willReturn($client);
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->setHandler($handler);
      $this->assertTrue(
        $service->isPublic(
          'sample_group',
          'sample_id',
          'image/gif'
        )
      );
    }

    public function testIsPublicWithPublicFileReadingCache() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|CacheService $cache */
      $cache = $this->createMock(CacheService::class);
      $cache
        ->expects($this->once())
        ->method('read')
        ->with('mediastatus', 'sample_group', ['sample_id', 'image/gif'], 86400, NULL)
        ->willReturn('public');
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->cache($cache);
      $this->assertTrue(
        $service->isPublic(
          'sample_group',
          'sample_id',
          'image/gif'
        )
      );
    }

    public function testIsPublicWithPublicFileWritingCache() {
      $responseXML =
        /** @lang Text */
        '<?xml version="1.0" encoding="UTF-8"?>
      <AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
        <AccessControlList>
          <Grant>
            <Grantee xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="Group">
              <URI>http://acs.amazonaws.com/groups/global/AllUsers</URI>
            </Grantee>
            <Permission>READ</Permission>
          </Grant>
        </AccessControlList>
      </AccessControlPolicy>';
      $client = $this->createMock(HTTPClient::class);
      $client
        ->expects($this->exactly(2))
        ->method('send');
      $client
        ->expects($this->exactly(2))
        ->method('getResponseStatus')
        ->willReturn(200);
      $client
        ->expects($this->once())
        ->method('getResponseHeader')
        ->with($this->equalTo('Content-Type'))
        ->willReturn('image/gif');
      $client
        ->expects($this->once())
        ->method('getResponseData')
        ->willReturn($responseXML);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->exactly(2))
        ->method('setUpRequest')
        ->with(
          $this->logicalOr(
            'http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id',
            'http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id?acl'
          ),
          $this->logicalOr('HEAD', 'GET')
        )
        ->willReturn($client);
      /** @var \PHPUnit_Framework_MockObject_MockObject|CacheService $cache */
      $cache = $this->createMock(CacheService::class);
      $cache
        ->expects($this->once())
        ->method('read')
        ->with('mediastatus', 'sample_group', ['sample_id', 'image/gif'], 86400, NULL)
        ->willReturn(NULL);
      $cache
        ->expects($this->once())
        ->method('write')
        ->with('mediastatus', 'sample_group', ['sample_id', 'image/gif'], 'public', 86400);
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->cache($cache);
      $service->setHandler($handler);
      $this->assertTrue(
        $service->isPublic(
          'sample_group',
          'sample_id',
          'image/gif'
        )
      );
    }

    public function testIsPublicWithPrivateFile() {
      $responseXML = /** @lang XML */
        '<?xml version="1.0" encoding="UTF-8"?>
      <AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
        <AccessControlList>
        </AccessControlList>
      </AccessControlPolicy>';
      $client = $this->createMock(HTTPClient::class);
      $client
        ->expects($this->exactly(2))
        ->method('getResponseStatus')
        ->willReturn(200);
      $client
        ->expects($this->once())
        ->method('getResponseHeader')
        ->with($this->equalTo('Content-Type'))
        ->willReturn('image/gif');
      $client
        ->expects($this->once())
        ->method('getResponseData')
        ->willReturn($responseXML);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->exactly(2))
        ->method('setUpRequest')
        ->willReturn($client);
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->setHandler($handler);
      $this->assertFalse(
        $service->isPublic(
          'sample_group',
          'sample_id',
          'image/gif'
        )
      );
    }

    public function testIsPublicWithPrivateFileReadingCache() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|CacheService $cache */
      $cache = $this->createMock(CacheService::class);
      $cache
        ->expects($this->once())
        ->method('read')
        ->with('mediastatus', 'sample_group', ['sample_id', 'image/gif'], 86400, NULL)
        ->willReturn('private');
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->cache($cache);
      $this->assertFalse(
        $service->isPublic(
          'sample_group',
          'sample_id',
          'image/gif'
        )
      );
    }

    public function testIsPublicWithFileNotFound() {
      $client = $this->createMock(HTTPClient::class);
      $client
        ->expects($this->once())
        ->method('getResponseStatus')
        ->willReturn(404);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->willReturn($client);
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->setHandler($handler);
      $this->assertFalse(
        $service->isPublic(
          'sample_group',
          'sample_id',
          'image/gif'
        )
      );
    }

    public function testIsPublicWithDifferentMimetype() {
      $client = $this->createMock(HTTPClient::class);
      $client
        ->expects($this->once())
        ->method('getResponseStatus')
        ->willReturn(200);
      $client
        ->expects($this->once())
        ->method('getResponseHeader')
        ->with($this->equalTo('Content-Type'))
        ->willReturn('text/plain');
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->willReturn($client);
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->setHandler($handler);
      $this->assertFalse(
        $service->isPublic(
          'sample_group',
          'sample_id',
          'image/gif'
        )
      );
    }

    public function testSetPublicWithStatusTrue() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('PUT'),
          $this->equalTo([]),
          $this->equalTo(
            [
              'Content-Type' => 'image/gif',
              'x-amz-acl' => 'public-read',
              'x-amz-copy-source' => '/sample_bucket/d/i/rectory/sample_group/sample_id',
              'x-amz-metadata-directive' => 'REPLACE',
            ]
          )
        )
        ->willReturn($this->getMockHTTPClient(NULL));
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->setHandler($handler);
      $this->assertTrue(
        $service->setPublic('sample_group', 'sample_id', TRUE, 'image/gif')
      );
    }

    public function testSetPublicWithStatusTrueWritesCache() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('PUT'),
          $this->equalTo([]),
          $this->equalTo(
            [
              'Content-Type' => 'image/gif',
              'x-amz-acl' => 'public-read',
              'x-amz-copy-source' => '/sample_bucket/d/i/rectory/sample_group/sample_id',
              'x-amz-metadata-directive' => 'REPLACE',
            ]
          )
        )
        ->willReturn($this->getMockHTTPClient(NULL));
      /** @var \PHPUnit_Framework_MockObject_MockObject|CacheService $cache */
      $cache = $this->createMock(CacheService::class);
      $cache
        ->expects($this->once())
        ->method('write')
        ->with('mediastatus', 'sample_group', ['sample_id', 'image/gif'], 'public', 86400);
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->cache($cache);
      $service->setHandler($handler);
      $this->assertTrue(
        $service->setPublic('sample_group', 'sample_id', TRUE, 'image/gif')
      );
    }

    public function testSetPublicWithStatusFalse() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('PUT'),
          $this->equalTo([]),
          $this->equalTo(
            [
              'Content-Type' => 'image/gif',
              'x-amz-acl' => 'private',
              'x-amz-copy-source' => '/sample_bucket/d/i/rectory/sample_group/sample_id',
              'x-amz-metadata-directive' => 'REPLACE',
            ]
          )
        )
        ->willReturn($this->getMockHTTPClient(NULL));
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->setHandler($handler);
      $this->assertTrue(
        $service->setPublic('sample_group', 'sample_id', FALSE, 'image/gif')
      );
    }

    public function testSetPublicWithStatusFalseWriteCache() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('PUT'),
          $this->equalTo([]),
          $this->equalTo(
            [
              'Content-Type' => 'image/gif',
              'x-amz-acl' => 'private',
              'x-amz-copy-source' => '/sample_bucket/d/i/rectory/sample_group/sample_id',
              'x-amz-metadata-directive' => 'REPLACE',
            ]
          )
        )
        ->willReturn($this->getMockHTTPClient(NULL));
      /** @var \PHPUnit_Framework_MockObject_MockObject|CacheService $cache */
      $cache = $this->createMock(CacheService::class);
      $cache
        ->expects($this->once())
        ->method('write')
        ->with('mediastatus', 'sample_group', ['sample_id', 'image/gif'], 'private', 86400);
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->cache($cache);
      $service->setHandler($handler);
      $this->assertTrue(
        $service->setPublic('sample_group', 'sample_id', FALSE, 'image/gif')
      );
    }

    public function testSetPublicFailureWithStatusFalse() {
      $configuration = $this->getMockConfigurationObjectFixture();
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('setUpRequest')
        ->with(
          $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
          $this->equalTo('PUT'),
          $this->equalTo([]),
          $this->equalTo(
            [
              'Content-Type' => 'image/gif',
              'x-amz-acl' => 'private',
              'x-amz-copy-source' => '/sample_bucket/d/i/rectory/sample_group/sample_id',
              'x-amz-metadata-directive' => 'REPLACE',
            ]
          )
        )
        ->willReturn($this->getMockHTTPClient(NULL, 500));
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->setHandler($handler);
      $this->assertFalse(
        $service->setPublic('sample_group', 'sample_id', FALSE, 'image/gif')
      );
    }

    public function testGetUrlForPublicFile() {
      $responseXML =
        /** @lang Text */
        '<?xml version="1.0" encoding="UTF-8"?>
      <AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
        <AccessControlList>
          <Grant>
            <Grantee xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="Group">
              <URI>http://acs.amazonaws.com/groups/global/AllUsers</URI>
            </Grantee>
            <Permission>READ</Permission>
          </Grant>
        </AccessControlList>
      </AccessControlPolicy>';
      $client = $this->createMock(HTTPClient::class);
      $client
        ->expects($this->exactly(2))
        ->method('getResponseStatus')
        ->willReturn(200);
      $client
        ->expects($this->once())
        ->method('getResponseHeader')
        ->with($this->equalTo('Content-Type'))
        ->willReturn('image/gif');
      $client
        ->expects($this->once())
        ->method('getResponseData')
        ->willReturn($responseXML);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->exactly(2))
        ->method('setUpRequest')
        ->willReturn($client);
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->setHandler($handler);
      $this->assertSame(
        'http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id',
        $service->getURL('sample_group', 'sample_id', 'image/gif')
      );
    }

    public function testGetUrlForPrivateFile() {
      $responseXML = /** @lang XML */
        '<?xml version="1.0" encoding="UTF-8"?>
      <AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
        <AccessControlList>
        </AccessControlList>
      </AccessControlPolicy>';
      $client = $this->createMock(HTTPClient::class);
      $client
        ->expects($this->exactly(2))
        ->method('getResponseStatus')
        ->willReturn(200);
      $client
        ->expects($this->once())
        ->method('getResponseHeader')
        ->with($this->equalTo('Content-Type'))
        ->willReturn('image/gif');
      $client
        ->expects($this->once())
        ->method('getResponseData')
        ->willReturn($responseXML);
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->exactly(2))
        ->method('setUpRequest')
        ->willReturn($client);
      $configuration = $this->getMockConfigurationObjectFixture();
      $service = new S3($configuration);
      $service->papaya($this->mockPapaya()->application());
      $service->setHandler($handler);
      $this->assertNull(
        $service->getURL('sample_group', 'sample_id', 'image/gif')
      );
    }

  }
}


