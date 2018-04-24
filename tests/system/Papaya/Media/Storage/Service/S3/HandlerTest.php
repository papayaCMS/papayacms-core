<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaMediaStorageServiceS3HandlerTest extends PapayaTestCase {

  private function getMockConfigurationObjectFixture() {
    $configuration = $this->mockPapaya()->options(
      array(
        'PAPAYA_MEDIA_STORAGE_S3_KEYID' => 'sample_key_id',
        'PAPAYA_MEDIA_STORAGE_S3_KEY' => 'sample_key',
      )
    );
    return $configuration;
  }

  public function testInitHTTPClient() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3Handler($configuration);
    $service->initHTTPClient();
    $this->assertInstanceOf('PapayaHttpClient', $this->readAttribute($service, '_client'));
  }

  public function testSetHTTPClient() {
    $client = $this->createMock(PapayaHttpClient::class);
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = new PapayaMediaStorageServiceS3Handler($configuration);
    $handler->setHTTPClient($client);
    $this->assertAttributeSame(
      $client, '_client', $handler
    );
  }

  /**
  * @dataProvider getSignatureDataDataProvider
  */
  public function testGetSignatureData($headers, $url, $expected) {
    $client = $this->createMock(PapayaHttpClient::class);
    $this->expectedHeaders = $headers;
    $client->expects($this->once())
           ->method('getMethod')
           ->will($this->returnValue('GET'));
    $client->expects($this->any())
           ->method('getHeader')
           ->withAnyParameters()
           ->will($this->returnCallback(array($this, 'getHeaderCallback')));
    $service = new PapayaMediaStorageServiceS3Handler();
    $service->setHTTPClient($client);
    $this->assertEquals(
      $expected,
      $service->getSignatureData($url)
    );
  }

  public function testGetSignatureDataExpectingErrorBucketStart() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client->expects($this->once())
           ->method('getMethod')
           ->will($this->returnValue('GET'));
    $client->expects($this->any())
           ->method('getHeader')
           ->withAnyParameters()
           ->will($this->returnValue(''));
    $service = new PapayaMediaStorageServiceS3Handler();
    $service->setHTTPClient($client);
    $this->assertEquals(
      '',
      @$service->getSignatureData('')
    );
  }

  public function testGetSignatureDataExpectingErrorBucketEnd() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client->expects($this->once())
           ->method('getMethod')
           ->will($this->returnValue('GET'));
    $client->expects($this->any())
           ->method('getHeader')
           ->withAnyParameters()
           ->will($this->returnValue(''));
    $service = new PapayaMediaStorageServiceS3Handler();
    $service->setHTTPClient($client);
    $this->assertEquals(
      '',
      @$service->getSignatureData('http://')
    );
  }

  public function testSetConfiguration() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceS3Handler($configuration);
    $this->assertSame(
      'sample_key_id', $this->readAttribute($service, '_storageAccessKeyId')
    );
    $this->assertSame(
      array(
        'inner' => 'EW[FZSi]SO'.str_repeat('6', 54),
        'outer' => "/=1,09\x0379%".str_repeat('\\', 54)
      ),
      $this->readAttribute($service, '_storageAccessKey')
    );
  }

  public function testSetUpRequestWithDefaults() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = new PapayaMediaStorageServiceS3Handler($configuration);
    $url = 'http://sample_bucket.s3.amazonaws.com/sample_group/sample_id';
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('reset');
    $client
      ->expects($this->once())
      ->method('setMethod')
      ->with($this->equalTo('GET'));
    $client
      ->expects($this->once())
      ->method('setUrl')
      ->with($this->equalTo($url));
    $client
      ->expects($this->never())
      ->method('addRequestData');
    $client
      ->expects($this->exactly(2))
      ->method('setHeader')
      ->with(
        $this->logicalOr(
          $this->equalTo('Date'),
          $this->equalTo('Authorization')
        ),
        $this->logicalOr(
          $this->stringEndsWith(' +0000'),
          $this->stringStartsWith('AWS sample_key_id:')
        )
      );
    $handler->setHTTPClient($client);
    $actual = $handler->setUpRequest($url);
    $this->assertSame($client, $actual);
  }

  public function testSetUpRequestWithFullArguments() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = new PapayaMediaStorageServiceS3Handler($configuration);
    $url = 'http://sample_bucket.s3.amazonaws.com/sample_group/sample_id';
    $method = 'HEAD';
    $parameters = array(
      'param1' => 'param1value',
      'param2' => 'param2value',
    );
    $headers = array(
      'Header1' => 'Header1Value',
      'Header2' => 'Header2Value',
    );
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('setMethod')
      ->with($this->equalTo($method));
    $client
      ->expects($this->once())
      ->method('addRequestData')
      ->with($this->identicalTo($parameters));
    $client
      ->expects($this->exactly(4))
      ->method('setHeader')
      ->with(
        $this->logicalOr(
          $this->equalTo('Date'),
          $this->equalTo('Authorization'),
          $this->equalTo('Header1'),
          $this->equalTo('Header2')
        ),
        $this->logicalOr(
          $this->stringEndsWith(' +0000'),
          $this->stringStartsWith('AWS sample_key_id:'),
          $this->equalTo('Header1Value'),
          $this->equalTo('Header2Value')
        )
      );
    $handler->setHTTPClient($client);
    $actual = $handler->setUpRequest($url, $method, $parameters, $headers);
    $this->assertSame($client, $actual);
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

  /**********************
  * Data Provider
  **********************/

  public static function getSignatureDataDataProvider() {
    return array(
      array(
        array(
          'Content-Type' => 'text/plain',
          'Date' => 'Mon, 02 Nov 2009 13:06:00 +0000',
          'x-amz-acl' => 'private',
          'x-amz-copy-source' => '/bucket/path/to/object',
          'x-amz-metadata-directive' => 'REPLACE',
        ),
        'http://sample_bucket.s3.amazonaws.com/o/b/ject',
        "GET\n\ntext/plain\nMon, 02 Nov 2009 13:06:00 +0000".
        "\nx-amz-acl:private".
        "\nx-amz-copy-source:/bucket/path/to/object".
        "\nx-amz-metadata-directive:REPLACE".
        "\n/sample_bucket/o/b/ject"
      ),
      array(
        array(
          'Content-Type' => 'text/plain',
          'Date' => 'Mon, 02 Nov 2009 13:06:00 +0000',
          'x-amz-acl' => '',
          'x-amz-copy-source' => '',
          'x-amz-metadata-directive' => '',
        ),
        'http://sample_bucket.s3.amazonaws.com?foo=bar',
        "GET\n\ntext/plain\nMon, 02 Nov 2009 13:06:00 +0000".
        "\n/sample_bucket"
      ),
      array(
        array(
          'Content-Type' => 'text/plain',
          'Date' => 'Mon, 02 Nov 2009 13:06:00 +0000',
          'x-amz-acl' => '',
          'x-amz-copy-source' => '',
          'x-amz-metadata-directive' => '',
        ),
        'http://sample_bucket.s3.amazonaws.com?acl',
        "GET\n\ntext/plain\nMon, 02 Nov 2009 13:06:00 +0000".
        "\n/sample_bucket?acl"
      ),
      array(
        array(
          'Content-Type' => 'text/plain',
          'Date' => 'Mon, 02 Nov 2009 13:06:00 +0000',
          'x-amz-acl' => '',
          'x-amz-copy-source' => '',
          'x-amz-metadata-directive' => '',
        ),
        'http://sample.bucket.s3.amazonaws.com?acl',
        "GET\n\ntext/plain\nMon, 02 Nov 2009 13:06:00 +0000".
        "\n/sample.bucket?acl"
      )
    );
  }
}


