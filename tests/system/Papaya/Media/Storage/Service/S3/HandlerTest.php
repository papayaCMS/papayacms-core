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

namespace Papaya\Media\Storage\Service\S3;
require_once __DIR__.'/../../../../../../bootstrap.php';

class HandlerTest extends \Papaya\TestCase {

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
    $service = new Handler($configuration);
    $service->initHTTPClient();
    $this->assertInstanceOf(\Papaya\HTTP\Client::class, $this->readAttribute($service, '_client'));
  }

  public function testSetHTTPClient() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $configuration = $this->getMockConfigurationObjectFixture();
    $handler = new Handler($configuration);
    $handler->setHTTPClient($client);
    $this->assertAttributeSame(
      $client, '_client', $handler
    );
  }

  /**
   * @dataProvider getSignatureDataDataProvider
   * @param array $headers
   * @param string $url
   * @param string $expected
   */
  public function testGetSignatureData(array $headers, $url, $expected) {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('GET'));
    $client
      ->expects($this->any())
      ->method('getHeader')
      ->withAnyParameters()
      ->willReturnCallback(
        function ($name) use ($headers) {
          if (isset($headers[$name])) {
            return $headers[$name];
          }
          /** @noinspection PhpVoidFunctionResultUsedInspection */
          return $this->fail('Unknown header name value: '.$name);
        }
      );
    $service = new Handler();
    $service->setHTTPClient($client);
    $this->assertEquals(
      $expected,
      $service->getSignatureData($url)
    );
  }

  public function testGetSignatureDataExpectingErrorBucketStart() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('GET'));
    $client->expects($this->any())
      ->method('getHeader')
      ->withAnyParameters()
      ->will($this->returnValue(''));
    $service = new Handler();
    $service->setHTTPClient($client);
    $this->assertEquals(
      '',
      @$service->getSignatureData('')
    );
  }

  public function testGetSignatureDataExpectingErrorBucketEnd() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('GET'));
    $client->expects($this->any())
      ->method('getHeader')
      ->withAnyParameters()
      ->will($this->returnValue(''));
    $service = new Handler();
    $service->setHTTPClient($client);
    $this->assertEquals(
      '',
      @$service->getSignatureData('http://')
    );
  }

  public function testSetConfiguration() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new Handler($configuration);
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
    $handler = new Handler($configuration);
    $url = 'http://sample_bucket.s3.amazonaws.com/sample_group/sample_id';
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
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
    $handler = new Handler($configuration);
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
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
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


