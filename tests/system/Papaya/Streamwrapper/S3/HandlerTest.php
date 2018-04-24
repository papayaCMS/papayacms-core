<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaStreamwrapperS3HandlerTest extends PapayaTestCase {

  private static $_testFile = array(
    'bucket' => 'bucketname',
    'id' => 'KEYID123456789012345',
    'secret' => '1234567890123456789012345678901234567890',
    'object' => 'objectkey'
  );

  /**
  * @covers PapayaStreamwrapperS3Handler::setHTTPClient
  */
  public function testSetHTTPClient() {
    $client = $this->createMock(PapayaHttpClient::class);
    $wrapper = new PapayaStreamwrapperS3Handler();
    $wrapper->setHTTPClient($client);
    $this->assertAttributeSame(
      $client, '_client', $wrapper
    );
  }

  /**
  * @covers PapayaStreamwrapperS3Handler::getHTTPClient
  */
  public function testGetHTTPClient() {
    $client = $this->createMock(PapayaHttpClient::class);
    $wrapper = new PapayaStreamwrapperS3Handler();
    $wrapper->setHTTPClient($client);
    $this->assertSame(
      $client, $wrapper->getHTTPClient()
    );
  }

  /**
  * @covers PapayaStreamwrapperS3Handler::getHTTPClient
  */
  public function testGetHTTPClientImplicitCreate() {
    $client = $this->createMock(PapayaHttpClient::class);
    $wrapper = new PapayaStreamwrapperS3Handler();
    $this->assertInstanceOf(
      'PapayaHttpClient', $wrapper->getHTTPClient()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getFileInformations
  * @covers PapayaStreamWrapperS3Handler::_sendRequest
  */
  public function testGetFileInformations() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('reset');
    $client
      ->expects($this->once())
      ->method('setMethod')
      ->with($this->equalTo('HEAD'));
    $client
      ->expects($this->exactly(4))
      ->method('setHeader')
      ->with(
        $this->logicalOr(
          'Date',
          'Content-Type',
          'Authorization',
          'Connection'
        ),
        $this->isType('string')
      );
    $client
      ->expects($this->once())
      ->method('send');
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->exactly(3))
      ->method('getResponseHeader')
      ->with(
        $this->logicalOr(
          'Content-Type',
          'Last-Modified',
          'Content-Length'
        )
      )
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue('application/octet-stream'),
          $this->returnValue('23'),
          $this->returnValue('Mon, 02 Nov 2009 13:06:00 +0000')
        )
      );
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertEquals(
      array(
       'size' => 23,
       'modified' => 1257167160,
       'mode' => 0100006,
      ),
      $handler->getFileInformations(
        self::$_testFile,
        0
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getFileInformations
  * @covers PapayaStreamWrapperS3Handler::_sendRequest
  */
  public function testGetFileInformationsWithDirectory() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('send');
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->once())
      ->method('getResponseHeader')
      ->with($this->equalTo('Content-Type'))
      ->will($this->returnValue('application/x-directory'));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      $handler->getFileInformations(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getFileInformations
  * @covers PapayaStreamWrapperS3Handler::_sendRequest
  */
  public function testGetFileInformationsWithNotFound() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('send');
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(404));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      $handler->getFileInformations(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getFileInformations
  * @covers PapayaStreamWrapperS3Handler::_sendRequest
  */
  public function testGetFileInformationsExpectingWarningPermissionDenied() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->expectError(E_WARNING);
    $handler->getFileInformations(
      self::$_testFile,
      STREAM_REPORT_ERRORS
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getFileInformations
  * @covers PapayaStreamWrapperS3Handler::_sendRequest
  */
  public function testGetFileInformationsWithSuppressedWarningPermissionDenied() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      @$handler->getFileInformations(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );

  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getFileInformations
  * @covers PapayaStreamWrapperS3Handler::_sendRequest
  */
  public function testGetFileInformationsExpectingWarningUnexpectedResponse() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(0));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->expectError(E_WARNING);
    $handler->getFileInformations(
      self::$_testFile,
      STREAM_REPORT_ERRORS
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getFileInformations
  * @covers PapayaStreamWrapperS3Handler::_sendRequest
  */
  public function testGetFileInformationsWithSuppressedWarningUnexpectedResponse() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(0));
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      @$handler->getFileInformations(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getDirectoryInformations
  * @covers PapayaStreamWrapperS3Handler::evaluateResult
  * @covers PapayaStreamWrapperS3Handler::_sendRequest
  */
  public function testGetDirectoryInformations() {
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
      ->with($this->stringStartsWith('http://bucketname.'));
    $client
      ->expects($this->exactly(4))
      ->method('setHeader')
      ->with(
        $this->logicalOr(
          'Date',
          'Content-Type',
          'Authorization',
          'Connection'
        ),
        $this->isType('string')
      );
    $client
      ->expects($this->exactly(4))
      ->method('addRequestData')
      ->with(
        $this->logicalOr(
          'prefix',
          'marker',
          'max-keys',
          'delimiter'
        ),
        $this->logicalOr(
          'objectkey/',
          'objectkey/marker',
          '5',
          '/'
        )
      );
    $client
      ->expects($this->once())
      ->method('send');
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>
      <ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
        <Name>bucketname</Name>
        <Prefix>objectkey/</Prefix>
        <Marker>objectkey/marker</Marker>
        <MaxKeys>1</MaxKeys>
        <IsTruncated>true</IsTruncated>
        <Contents>
          <Key>objectkey/test</Key>
        </Contents>
        <Contents>
          <Key>objectkey/testdir</Key>
        </Contents>
        <Contents>
          <Key>objectkey/testing</Key>
        </Contents>
        <Contents>
          <Key>objectkey/$</Key>
        </Contents>
        <CommonPrefixes>
          <Prefix>objectkey/testdir/</Prefix>
        </CommonPrefixes>
      </ListBucketResult>
    ';
    $client
      ->expects($this->once())
      ->method('getResponseData')
      ->will(
        $this->returnValue($xmlResponse)
      );
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertEquals(
      array(
       'size' => 0,
       'modified' => 0,
       'mode' => 040006,
       'contents' => array('test', 'testdir', 'testing'),
       'moreContent' => TRUE,
       'startMarker' => 'marker',
      ),
      $handler->getDirectoryInformations(
        self::$_testFile,
        0,
        5,
        'marker'
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getDirectoryInformations
  */
  public function testGetDirectoryInformationsWithHTTPError() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(404));
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      $handler->getDirectoryInformations(
        self::$_testFile,
        0
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getDirectoryInformations
  */
  public function testGetDirectoryInformationsWithEmptyResult() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>
      <ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
      </ListBucketResult>
    ';
    $client
      ->expects($this->once())
      ->method('getResponseData')
      ->will(
        $this->returnValue($xmlResponse)
      );
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      $handler->getDirectoryInformations(
        self::$_testFile,
        0
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::getDirectoryInformations
  */
  public function testGetDirectoryInformationsWithSlash() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->exactly(4))
      ->method('addRequestData')
      ->with(
        $this->logicalOr(
          'prefix',
          'marker',
          'max-keys',
          'delimiter'
        ),
        $this->logicalOr(
          'objectkey/',
          'objectkey/',
          1,
          '/'
        )
      );
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(404));
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $testFile = array(
      'bucket' => 'bucketname',
      'id' => 'KEYID123456789012345',
      'secret' => '1234567890123456789012345678901234567890',
      'object' => 'objectkey/'
    );
    $this->assertNull(
      $handler->getDirectoryInformations(
        $testFile,
        0
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::readFileContent
  */
  public function testReadFileContent() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('reset');
    $client
      ->expects($this->once())
      ->method('setMethod')
      ->with($this->equalTo('GET'));
    $client
      ->expects($this->exactly(5))
      ->method('setHeader')
      ->with(
        $this->logicalOr(
          'Date',
          'Content-Type',
          'Range',
          'Authorization',
          'Connection'
        ),
        $this->logicalOr(
          $this->stringEndsWith(' +0000'),
          'text/plain',
          'bytes=10-29',
          $this->stringStartsWith('AWS '),
          'keep-alive'
        )
      );
    $client
      ->expects($this->once())
      ->method('send');
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $testContent = 'testfilecontent';
    $testRange = 'bytes 10-29/12345';
    $client
      ->expects($this->once())
      ->method('getResponseData')
      ->will(
        $this->returnValue($testContent)
      );
    $client
      ->expects($this->exactly(2))
      ->method('getResponseHeader')
      ->with(
        $this->logicalOr(
          'Content-Range',
          'Last-Modified'
        )
      )
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue($testRange),
          $this->returnValue('Mon, 02 Nov 2009 13:06:00 +0000')
        )
      );
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $expectedStat = array(
      'size' => 12345,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $this->assertSame(
      array($testContent, $expectedStat),
      $handler->readFileContent(
        self::$_testFile,
        10,
        20,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::readFileContent
  */
  public function testReadFileContentWithoutRange() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->exactly(1))
      ->method('getResponseHeader')
      ->with(
        'Content-Range'
      )
      ->will(
        $this->returnValue("")
      );
    $client
      ->expects($this->never())
      ->method('getResponseData');
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      @$handler->readFileContent(
        self::$_testFile,
        10,
        20,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::readFileContent
  */
  public function testReadFileContentForEmptyResult() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(404));
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      @$handler->readFileContent(
        self::$_testFile,
        10,
        20,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::openWriteFile
  */
  public function testOpenWriteFile() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('reset');
    $client
      ->expects($this->once())
      ->method('setMethod')
      ->with($this->equalTo('PUT'));
    $url = 'http://bucketname.s3.amazonaws.com/objectkey';
    $client
      ->expects($this->once())
      ->method('setUrl')
      ->with($this->equalTo($url));
    $client
      ->expects($this->exactly(4))
      ->method('setHeader')
      ->with(
        $this->logicalOr(
          'Date',
          'Content-Type',
          'Authorization',
          'Connection'
        ),
        $this->isType('string')
      );
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertTrue(
      $handler->openWriteFile(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::writeFileContent
  */
  public function testWriteFileContent() {
    $client = $this->createMock(PapayaHttpClient::class);
    $content = "testContent";
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    $this->assertSame(
      strlen($content),
      $handler->writeFileContent(STREAM_REPORT_ERRORS, $content)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::closeWriteFile
  */
  public function testCloseWriteFile() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('send');
    $client
      ->expects($this->once())
      ->method('addRequestFile')
      ->with($this->isInstanceOf('PapayaHttpClientFileResource'));
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    $handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::closeWriteFile
  */
  public function testCloseWriteFileExpectingWarningPermissionDenied() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    $this->expectError(E_WARNING);
    $handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::closeWriteFile
  */
  public function testCloseWriteFileWithSuppressedWarningPermissionDenied() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    @$handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::closeWriteFile
  */
  public function testCloseWriteFileExpectingWarningUnexpectedResponse() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(0));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    $this->expectError(E_WARNING);
    $handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::closeWriteFile
  */
  public function testCloseWriteFileWithSuppressedWarningUnexpectedResponse() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(0));
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    @$handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::removeFile
  */
  public function testRemoveFile() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('reset');
    $client
      ->expects($this->once())
      ->method('setMethod')
      ->with($this->equalTo('DELETE'));
    $client
      ->expects($this->exactly(4))
      ->method('setHeader')
      ->with(
        $this->logicalOr(
          'Date',
          'Content-Type',
          'Authorization',
          'Connection'
        ),
        $this->isType('string')
      );
    $client
      ->expects($this->once())
      ->method('send');
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(204));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertTrue(
      $handler->removeFile(self::$_testFile, STREAM_REPORT_ERRORS)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3Handler::removeFile
  */
  public function testRemoveFileExpectingFalse() {
    $client = $this->createMock(PapayaHttpClient::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $handler = new PapayaStreamwrapperS3Handler();
    $handler->setHTTPClient($client);
    $this->assertFalse(
      $handler->removeFile(self::$_testFile, 0)
    );
  }
}


