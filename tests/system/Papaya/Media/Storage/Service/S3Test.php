<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaMediaStorageServiceS3Test extends PapayaTestCase {

  private function getMockConfigurationObjectFixture() {
    $configuration = $this->mockPapaya()->options(
      array(
        'PAPAYA_MEDIA_STORAGE_S3_BUCKET' => 'sample_bucket',
        'PAPAYA_MEDIA_STORAGE_S3_KEYID' => 'sample_key_id',
        'PAPAYA_MEDIA_STORAGE_S3_KEY' => 'sample_key',
        'PAPAYA_MEDIA_STORAGE_SUBDIRECTORY' => 'd/i/rectory',
        'PAPAYA_MEDIA_STORAGE_DIRECTORY_DEPTH' => 0
      )
    );
    return $configuration;
  }

  private function getMockHTTPClient($response, $status = 200) {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('send');
    if (is_int($status)) {
      $client
        ->expects($this->once())
        ->method('getResponseStatus')
        ->will($this->returnValue($status));
    } else {
      $client
        ->expects($this->never())
        ->method('getResponseStatus');
    }
    if (is_string($response)) {
      $client
        ->expects($this->once())
        ->method('getResponseData')
        ->will($this->returnValue($response));
    } else {
      $client
        ->expects($this->never())
        ->method('getResponseData');
    }
    if (is_object($response)) {
      $client
        ->expects($this->once())
        ->method('getSocket')
        ->will($this->returnValue($response));
    } else {
      $client
        ->expects($this->never())
        ->method('getSocket');
    }
    return $client;
  }

  public function testSetHandler() {
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertAttributeSame(
      $handler, '_handler', $service
    );
  }

  public function testSetHTTPClient() {
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $client = new PapayaHttpClient;
    $handler
      ->expects($this->once())
      ->method('setHTTPClient')
      ->with($this->equalTo($client));
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $service->setHTTPClient($client);
  }

  public function testSetConfiguration() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
    $this->assertSame(
      'sample_bucket', $this->readAttribute($service, '_storageBucket')
    );
    $this->assertSame(
      'd/i/rectory/', $this->readAttribute($service, '_storageDirectory')
    );
    $this->assertSame(
      0, $this->readAttribute($service, '_storageDirectoryDepth')
    );
  }

  public function testSetConfigurationWithSlash() {
    $configuration = $this->mockPapaya()->options(
      array(
        'PAPAYA_MEDIA_STORAGE_S3_BUCKET' => 'sample_bucket',
        'PAPAYA_MEDIA_STORAGE_S3_KEYID' => 'sample_key_id',
        'PAPAYA_MEDIA_STORAGE_S3_KEY' => 'sample_key',
        'PAPAYA_MEDIA_STORAGE_SUBDIRECTORY' => 'd/i/rectory/',
        'PAPAYA_MEDIA_STORAGE_DIRECTORY_DEPTH' => 0
      )
    );
    $service = new PapayaMediaStorageServiceS3($configuration);
    $this->assertSame(
      'd/i/rectory/', $this->readAttribute($service, '_storageDirectory')
    );
  }

  public function testSetConfigurationWithEmptyDirectory() {
    $configuration = $this->mockPapaya()->options(
      array(
        'PAPAYA_MEDIA_STORAGE_S3_BUCKET' => 'sample_bucket',
        'PAPAYA_MEDIA_STORAGE_S3_KEYID' => 'sample_key_id',
        'PAPAYA_MEDIA_STORAGE_S3_KEY' => 'sample_key',
        'PAPAYA_MEDIA_STORAGE_SUBDIRECTORY' => '',
        'PAPAYA_MEDIA_STORAGE_DIRECTORY_DEPTH' => 0
      )
    );
    $service = new PapayaMediaStorageServiceS3($configuration);
    $this->assertSame(
      '', $this->readAttribute($service, '_storageDirectory')
    );
  }

  public function testBrowse() {
    $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".
      '<ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">'.
        '<Name>sample_bucket</Name>'.
        '<Prefix></Prefix>'.
        '<IsTruncated>false</IsTruncated>'.
        '<Contents>'.
          '<Key>sample_group/sample_file</Key>'.
        '</Contents>'.
      '</ListBucketResult>';
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com'),
        $this->equalTo('GET'),
        array('prefix' => 'sample_group/')
      )
      ->will($this->returnValue($this->getMockHTTPClient($xmlResponse)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertSame(
      array(
        'sample_file'
      ),
      $service->browse('sample_group')
    );
  }

  public function testBrowseWithStartString() {
    $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".
      '<ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">'.
        '<Name>sample_bucket</Name>'.
        '<Prefix>sample</Prefix>'.
        '<IsTruncated>false</IsTruncated>'.
        '<Contents>'.
          '<Key>sample_group/sample_file</Key>'.
        '</Contents>'.
      '</ListBucketResult>';
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com'),
        $this->equalTo('GET'),
        array('prefix' => 'sample_group/sample')
      )
      ->will($this->returnValue($this->getMockHTTPClient($xmlResponse)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertSame(
      array(
        'sample_file'
      ),
      $service->browse('sample_group', 'sample')
    );
  }

  public function testBrowseWithHttpError() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com'),
        $this->equalTo('GET'),
        array('prefix' => 'sample_group/')
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL, 404)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertSame(
      array(),
      $service->browse('sample_group')
    );
  }

  public function testGet() {
    $dataResponse = 'SAMPLE_DATA';
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
      )
      ->will($this->returnValue($this->getMockHTTPClient($dataResponse)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertSame(
      'SAMPLE_DATA',
      $service->get('sample_group', 'sample_id')
    );
  }

  public function testGetWithHttpError() {
    $dataResponse = 'SAMPLE_DATA';
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL, 404)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertNull(
      $service->get('sample_group', 'sample_id')
    );
  }

  public function testGetWithDirectoryDepth() {
    $dataResponse = 'SAMPLE_DATA';
    $configuration = $this->mockPapaya()->options(
      array(
        'PAPAYA_MEDIA_STORAGE_S3_BUCKET' => 'sample_bucket',
        'PAPAYA_MEDIA_STORAGE_S3_KEYID' => 'sample_key_id',
        'PAPAYA_MEDIA_STORAGE_S3_KEY' => 'sample_key',
        'PAPAYA_MEDIA_STORAGE_SUBDIRECTORY' => 'd/i/rectory',
        'PAPAYA_MEDIA_STORAGE_DIRECTORY_DEPTH' => 3
      )
    );
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo(
          'http://sample_bucket.s3.amazonaws.com/'.
            'd/i/rectory/sample_group/s/a/m/sample_id'
        )
      )
      ->will($this->returnValue($this->getMockHTTPClient($dataResponse)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertSame(
      'SAMPLE_DATA',
      $service->get('sample_group', 'sample_id')
    );
  }

  public function testGetLocalFile() {
    $dataResponse = 'SAMPLE_DATA';
    $configuration = $this->getMockConfigurationObjectFixture();
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $socket->expects($this->exactly(2))
           ->method('eof')
           ->will(
             $this->onConsecutiveCalls(
               $this->returnValue(FALSE),
               $this->returnValue(TRUE)
             )
           );
    $socket->expects($this->once())
           ->method('read')
           ->will($this->returnValue($dataResponse));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
      )
      ->will($this->returnValue($this->getMockHTTPClient($socket)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $localFile = $service->getLocalFile('sample_group', 'sample_id');
    $this->assertSame('SAMPLE_DATA', file_get_contents($localFile['filename']));
    unlink($localFile['filename']);
    $this->assertTrue($localFile['is_temporary']);
  }

  public function testGetLocalFileExpectingFalse() {
    $dataResponse = 'SAMPLE_DATA';
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL, 404)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertFalse($service->getLocalFile('sample_group', 'sample_id'));
  }

  public function testOutput() {
    $dataResponse = 'SAMPLE_DATA';
    $configuration = $this->getMockConfigurationObjectFixture();
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('eof')
           ->will($this->returnValue(FALSE));
    $socket->expects($this->at(1))
           ->method('read')
           ->will($this->returnValue($dataResponse));
    $socket->expects($this->at(2))
           ->method('eof')
           ->will($this->returnValue(TRUE));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
      )
      ->will($this->returnValue($this->getMockHTTPClient($socket)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    ob_start();
    $service->output('sample_group', 'sample_id');
    $this->assertSame('SAMPLE_DATA', ob_get_clean());
  }

  public function testOutputWithHttpError() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL, 404)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    ob_start();
    $service->output('sample_group', 'sample_id');
    $this->assertSame('', ob_get_clean());
  }

  public function testOutputWithRangeFrom() {
    $dataResponse = 'SAMPLE_DATA';
    $configuration = $this->getMockConfigurationObjectFixture();
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('eof')
           ->will($this->returnValue(FALSE));
    $socket->expects($this->at(1))
           ->method('read')
           ->will($this->returnValue($dataResponse));
    $socket->expects($this->at(2))
           ->method('eof')
           ->will($this->returnValue(TRUE));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
      )
      ->will($this->returnValue($this->getMockHTTPClient($socket, 206)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    ob_start();
    $service->output('sample_group', 'sample_id', 5);
    $this->assertSame('SAMPLE_DATA', ob_get_clean());
  }

  public function testOutputWithRangeFromAndTo() {
    $dataResponse = 'SAMPLE_DATA';
    $configuration = $this->getMockConfigurationObjectFixture();
    $socket = $this->createMock(PapayaHttpClientSocket::class);
    $socket->expects($this->at(0))
           ->method('eof')
           ->will($this->returnValue(FALSE));
    $socket->expects($this->at(1))
           ->method('read')
           ->will($this->returnValue($dataResponse));
    $socket->expects($this->at(2))
           ->method('eof')
           ->will($this->returnValue(TRUE));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id')
      )
      ->will($this->returnValue($this->getMockHTTPClient($socket, 206)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    ob_start();
    $service->output('sample_group', 'sample_id', 5, 10);
    $this->assertSame('SAMPLE_DATA', ob_get_clean());
  }

  public function testStoreWithStringAndPrivateStatus() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('PUT'),
        $this->equalTo(array()),
        $this->equalTo(
          array(
            'Content-Type' => 'application/octet-stream',
            'x-amz-acl' => 'private',
          )
        )
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertTrue($service->store('sample_group', 'sample_id', 'SAMPLE_DATA'));
  }

  public function testStoreWithResourceAndPublicStatus() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('PUT'),
        $this->equalTo(array()),
        $this->equalTo(
          array(
            'Content-Type' => 'application/octet-stream',
            'x-amz-acl' => 'public-read',
          )
        )
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertTrue(
      $service->store(
        'sample_group',
        'sample_id',
        fopen('data://text/plain,SAMPLE_DATA', 'r'),
        'application/octet-stream',
        TRUE
      )
    );
  }

  public function testStoreLocalFileWithPrivateStatus() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('PUT'),
        $this->equalTo(array()),
        $this->equalTo(
          array(
            'Content-Type' => 'application/octet-stream',
            'x-amz-acl' => 'private',
          )
        )
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL)));
    $service = new PapayaMediaStorageServiceS3($configuration);
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
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('PUT'),
        $this->equalTo(array()),
        $this->equalTo(
          array(
            'Content-Type' => 'application/octet-stream',
            'x-amz-acl' => 'public-read',
          )
        )
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL)));
    $service = new PapayaMediaStorageServiceS3($configuration);
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
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('DELETE')
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL, 204)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertTrue($service->remove('sample_group', 'sample_id'));
  }

  public function testExists() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('HEAD')
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertTrue($service->exists('sample_group', 'sample_id'));
  }

  public function testExistsForNonexistingFile() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('HEAD')
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL, 404)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->setHandler($handler);
    $this->assertFalse($service->exists('sample_group', 'sample_id'));
  }

  public function testIsPublicWithPublicFile() {
    $responseXML = '<?xml version="1.0" encoding="UTF-8"?>'.
      '<AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">'.
      '  <AccessControlList>'.
      '    <Grant>'.
      '      <Grantee xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="Group">'.
      '        <URI>http://acs.amazonaws.com/groups/global/AllUsers</URI>'.
      '      </Grantee>'.
      '      <Permission>READ</Permission>'.
      '    </Grant>'.
      '  </AccessControlList>'.
      '</AccessControlPolicy>';
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->exactly(2))
      ->method('send');
    $client
      ->expects($this->exactly(2))
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->once())
      ->method('getResponseHeader')
      ->with($this->equalTo('Content-Type'))
      ->will($this->returnValue('image/gif'));
    $client
      ->expects($this->once())
      ->method('getResponseData')
      ->will($this->returnValue($responseXML));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
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
      ->will($this->returnValue($client));
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
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
    $cache = $this->createMock(PapayaCacheService::class);
    $cache
      ->expects($this->once())
      ->method('read')
      ->with('mediastatus', 'sample_group', array('sample_id', 'image/gif'), 86400, NULL)
      ->will($this->returnValue('public'));
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
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
    $responseXML = '<?xml version="1.0" encoding="UTF-8"?>'.
      '<AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">'.
      '  <AccessControlList>'.
      '    <Grant>'.
      '      <Grantee xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="Group">'.
      '        <URI>http://acs.amazonaws.com/groups/global/AllUsers</URI>'.
      '      </Grantee>'.
      '      <Permission>READ</Permission>'.
      '    </Grant>'.
      '  </AccessControlList>'.
      '</AccessControlPolicy>';
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->exactly(2))
      ->method('send');
    $client
      ->expects($this->exactly(2))
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->once())
      ->method('getResponseHeader')
      ->with($this->equalTo('Content-Type'))
      ->will($this->returnValue('image/gif'));
    $client
      ->expects($this->once())
      ->method('getResponseData')
      ->will($this->returnValue($responseXML));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
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
      ->will($this->returnValue($client));
    $cache = $this->createMock(PapayaCacheService::class);
    $cache
      ->expects($this->once())
      ->method('read')
      ->with('mediastatus', 'sample_group', array('sample_id', 'image/gif'), 86400, NULL)
      ->will($this->returnValue(NULL));
    $cache
      ->expects($this->once())
      ->method('write')
      ->with('mediastatus', 'sample_group', array('sample_id', 'image/gif'), 'public', 86400);
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
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
    $responseXML = '<?xml version="1.0" encoding="UTF-8"?>'.
      '<AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">'.
      '  <AccessControlList>'.
      '  </AccessControlList>'.
      '</AccessControlPolicy>';
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->exactly(2))
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->once())
      ->method('getResponseHeader')
      ->with($this->equalTo('Content-Type'))
      ->will($this->returnValue('image/gif'));
    $client
      ->expects($this->once())
      ->method('getResponseData')
      ->will($this->returnValue($responseXML));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->exactly(2))
      ->method('setUpRequest')
      ->will($this->returnValue($client));
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
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
    $cache = $this->createMock(PapayaCacheService::class);
    $cache
      ->expects($this->once())
      ->method('read')
      ->with('mediastatus', 'sample_group', array('sample_id', 'image/gif'), 86400, NULL)
      ->will($this->returnValue('private'));
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
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
    $configuration = $this->getMockConfigurationObjectFixture();
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(404));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->will($this->returnValue($client));
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
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
    $configuration = $this->getMockConfigurationObjectFixture();
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->once())
      ->method('getResponseHeader')
      ->with($this->equalTo('Content-Type'))
      ->will($this->returnValue('text/plain'));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->will($this->returnValue($client));
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
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
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('PUT'),
        $this->equalTo(array()),
        $this->equalTo(
          array(
            'Content-Type' => 'image/gif',
            'x-amz-acl' => 'public-read',
            'x-amz-copy-source' => '/sample_bucket/d/i/rectory/sample_group/sample_id',
            'x-amz-metadata-directive' => 'REPLACE',
          )
        )
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->papaya($this->mockPapaya()->application());
    $service->setHandler($handler);
    $this->assertTrue(
      $service->setPublic('sample_group', 'sample_id', TRUE, 'image/gif')
    );
  }

  public function testSetPublicWithStatusTrueWritesCache() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('PUT'),
        $this->equalTo(array()),
        $this->equalTo(
          array(
            'Content-Type' => 'image/gif',
            'x-amz-acl' => 'public-read',
            'x-amz-copy-source' => '/sample_bucket/d/i/rectory/sample_group/sample_id',
            'x-amz-metadata-directive' => 'REPLACE',
          )
        )
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL)));
    $cache = $this->createMock(PapayaCacheService::class);
    $cache
      ->expects($this->once())
      ->method('write')
      ->with('mediastatus', 'sample_group', array('sample_id', 'image/gif'), 'public', 86400);
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->papaya($this->mockPapaya()->application());
    $service->cache($cache);
    $service->setHandler($handler);
    $this->assertTrue(
      $service->setPublic('sample_group', 'sample_id', TRUE, 'image/gif')
    );
  }

  public function testSetPublicWithStatusFalse() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('PUT'),
        $this->equalTo(array()),
        $this->equalTo(
          array(
            'Content-Type' => 'image/gif',
            'x-amz-acl' => 'private',
            'x-amz-copy-source' => '/sample_bucket/d/i/rectory/sample_group/sample_id',
            'x-amz-metadata-directive' => 'REPLACE',
          )
        )
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL)));
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->papaya($this->mockPapaya()->application());
    $service->setHandler($handler);
    $this->assertTrue(
      $service->setPublic('sample_group', 'sample_id', FALSE, 'image/gif')
    );
  }

  public function testSetPublicWithStatusFalseWriteCache() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->once())
      ->method('setUpRequest')
      ->with(
        $this->equalTo('http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id'),
        $this->equalTo('PUT'),
        $this->equalTo(array()),
        $this->equalTo(
          array(
            'Content-Type' => 'image/gif',
            'x-amz-acl' => 'private',
            'x-amz-copy-source' => '/sample_bucket/d/i/rectory/sample_group/sample_id',
            'x-amz-metadata-directive' => 'REPLACE',
          )
        )
      )
      ->will($this->returnValue($this->getMockHTTPClient(NULL)));
    $cache = $this->createMock(PapayaCacheService::class);
    $cache
      ->expects($this->once())
      ->method('write')
      ->with('mediastatus', 'sample_group', array('sample_id', 'image/gif'), 'private', 86400);
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->papaya($this->mockPapaya()->application());
    $service->cache($cache);
    $service->setHandler($handler);
    $this->assertTrue(
      $service->setPublic('sample_group', 'sample_id', FALSE, 'image/gif')
    );
  }

  public function testGetUrlForPublicFile() {
    $responseXML = '<?xml version="1.0" encoding="UTF-8"?>'.
      '<AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">'.
      '  <AccessControlList>'.
      '    <Grant>'.
      '      <Grantee xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="Group">'.
      '        <URI>http://acs.amazonaws.com/groups/global/AllUsers</URI>'.
      '      </Grantee>'.
      '      <Permission>READ</Permission>'.
      '    </Grant>'.
      '  </AccessControlList>'.
      '</AccessControlPolicy>';
    $configuration = $this->getMockConfigurationObjectFixture();
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->exactly(2))
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->once())
      ->method('getResponseHeader')
      ->with($this->equalTo('Content-Type'))
      ->will($this->returnValue('image/gif'));
    $client
      ->expects($this->once())
      ->method('getResponseData')
      ->will($this->returnValue($responseXML));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->exactly(2))
      ->method('setUpRequest')
      ->will($this->returnValue($client));
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->papaya($this->mockPapaya()->application());
    $service->setHandler($handler);
    $this->assertSame(
      'http://sample_bucket.s3.amazonaws.com/d/i/rectory/sample_group/sample_id',
      $service->getUrl('sample_group', 'sample_id', 'image/gif')
    );
  }

  public function testGetUrlForPrivateFile() {
    $responseXML = '<?xml version="1.0" encoding="UTF-8"?>'.
      '<AccessControlPolicy xmlns="http://s3.amazonaws.com/doc/2006-03-01/">'.
      '  <AccessControlList>'.
      '  </AccessControlList>'.
      '</AccessControlPolicy>';
    $configuration = $this->getMockConfigurationObjectFixture();
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->exactly(2))
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->once())
      ->method('getResponseHeader')
      ->with($this->equalTo('Content-Type'))
      ->will($this->returnValue('image/gif'));
    $client
      ->expects($this->once())
      ->method('getResponseData')
      ->will($this->returnValue($responseXML));
    $handler = $this->createMock(PapayaMediaStorageServiceS3Handler::class);
    $handler
      ->expects($this->exactly(2))
      ->method('setUpRequest')
      ->will($this->returnValue($client));
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3($configuration);
    $service->papaya($this->mockPapaya()->application());
    $service->setHandler($handler);
    $this->assertNull(
      $service->getUrl('sample_group', 'sample_id', 'image/gif')
    );
  }

  /**********************
  * Mock Callbacks
  **********************/

  /**
   * @param $name
   * @return mixed
   */
  public function getHeaderCallback($name) {
    if (isset($this->expectedHeaders[$name])) {
      return $this->expectedHeaders[$name];
    }
    /** @noinspection PhpVoidFunctionResultUsedInspection */
    return $this->fail('Unknown header name value: '.$name);
  }

}


