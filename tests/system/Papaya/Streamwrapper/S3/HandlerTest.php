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

namespace Papaya\Streamwrapper\S3;
require_once __DIR__.'/../../../../bootstrap.php';

class HandlerTest extends \Papaya\TestCase {

  private static $_testFile = array(
    'bucket' => 'bucketname',
    'id' => 'KEYID123456789012345',
    'secret' => '1234567890123456789012345678901234567890',
    'object' => 'objectkey'
  );

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::setHTTPClient
   */
  public function testSetHTTPClient() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $wrapper = new Handler();
    $wrapper->setHTTPClient($client);
    $this->assertAttributeSame(
      $client, '_client', $wrapper
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getHTTPClient
   */
  public function testGetHTTPClient() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $wrapper = new Handler();
    $wrapper->setHTTPClient($client);
    $this->assertSame(
      $client, $wrapper->getHTTPClient()
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getHTTPClient
   */
  public function testGetHTTPClientImplicitCreate() {
    $wrapper = new Handler();
    $this->assertInstanceOf(
      \Papaya\HTTP\Client::class, $wrapper->getHTTPClient()
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getFileInformations
   * @covers \Papaya\Streamwrapper\S3\Handler::_sendRequest
   */
  public function testGetFileInformation() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
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
    $handler = new Handler();
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
   * @covers \Papaya\Streamwrapper\S3\Handler::getFileInformations
   * @covers \Papaya\Streamwrapper\S3\Handler::_sendRequest
   */
  public function testGetFileInformationsWithDirectory() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
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
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      $handler->getFileInformations(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getFileInformations
   * @covers \Papaya\Streamwrapper\S3\Handler::_sendRequest
   */
  public function testGetFileInformationsWithNotFound() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
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
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      $handler->getFileInformations(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getFileInformations
   * @covers \Papaya\Streamwrapper\S3\Handler::_sendRequest
   */
  public function testGetFileInformationsExpectingWarningPermissionDenied() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->expectError(E_WARNING);
    $handler->getFileInformations(
      self::$_testFile,
      STREAM_REPORT_ERRORS
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getFileInformations
   * @covers \Papaya\Streamwrapper\S3\Handler::_sendRequest
   */
  public function testGetFileInformationsWithSuppressedWarningPermissionDenied() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      @$handler->getFileInformations(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );

  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getFileInformations
   * @covers \Papaya\Streamwrapper\S3\Handler::_sendRequest
   */
  public function testGetFileInformationsExpectingWarningUnexpectedResponse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(0));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->expectError(E_WARNING);
    $handler->getFileInformations(
      self::$_testFile,
      STREAM_REPORT_ERRORS
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getFileInformations
   * @covers \Papaya\Streamwrapper\S3\Handler::_sendRequest
   */
  public function testGetFileInformationsWithSuppressedWarningUnexpectedResponse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(0));
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      @$handler->getFileInformations(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getDirectoryInformations
   * @covers \Papaya\Streamwrapper\S3\Handler::evaluateResult
   * @covers \Papaya\Streamwrapper\S3\Handler::_sendRequest
   */
  public function testGetDirectoryInformations() {
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
    $xmlResponse = /** @lang XML */
      '<?xml version="1.0" encoding="UTF-8"?>
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
    $handler = new Handler();
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
   * @covers \Papaya\Streamwrapper\S3\Handler::getDirectoryInformations
   */
  public function testGetDirectoryInformationsWithHTTPError() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(404));
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      $handler->getDirectoryInformations(
        self::$_testFile,
        0
      )
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getDirectoryInformations
   */
  public function testGetDirectoryInformationsWithEmptyResult() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $xmlResponse = /** @lang XML */
      '<?xml version="1.0" encoding="UTF-8"?>
      <ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
      </ListBucketResult>
    ';
    $client
      ->expects($this->once())
      ->method('getResponseData')
      ->will(
        $this->returnValue($xmlResponse)
      );
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->assertNull(
      $handler->getDirectoryInformations(
        self::$_testFile,
        0
      )
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::getDirectoryInformations
   */
  public function testGetDirectoryInformationsWithSlash() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
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
    $handler = new Handler();
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
   * @covers \Papaya\Streamwrapper\S3\Handler::readFileContent
   */
  public function testReadFileContent() {
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
    $handler = new Handler();
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
   * @covers \Papaya\Streamwrapper\S3\Handler::readFileContent
   */
  public function testReadFileContentWithoutRange() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
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
        $this->returnValue('')
      );
    $client
      ->expects($this->never())
      ->method('getResponseData');
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new Handler();
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
   * @covers \Papaya\Streamwrapper\S3\Handler::readFileContent
   */
  public function testReadFileContentForEmptyResult() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(404));
    $handler = new Handler();
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
   * @covers \Papaya\Streamwrapper\S3\Handler::openWriteFile
   */
  public function testOpenWriteFile() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
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
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->assertTrue(
      $handler->openWriteFile(
        self::$_testFile,
        STREAM_REPORT_ERRORS
      )
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::writeFileContent
   */
  public function testWriteFileContent() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $content = 'testContent';
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    $this->assertSame(
      strlen($content),
      $handler->writeFileContent(STREAM_REPORT_ERRORS, $content)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::closeWriteFile
   */
  public function testCloseWriteFile() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('send');
    $client
      ->expects($this->once())
      ->method('addRequestFile')
      ->with($this->isInstanceOf(\Papaya\HTTP\Client\File\Stream::class));
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    $handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::closeWriteFile
   */
  public function testCloseWriteFileExpectingWarningPermissionDenied() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    $this->expectError(E_WARNING);
    $handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::closeWriteFile
   */
  public function testCloseWriteFileWithSuppressedWarningPermissionDenied() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    @$handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::closeWriteFile
   */
  public function testCloseWriteFileExpectingWarningUnexpectedResponse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(0));
    $client
      ->expects($this->once())
      ->method('close');
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    $this->expectError(E_WARNING);
    $handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::closeWriteFile
   */
  public function testCloseWriteFileWithSuppressedWarningUnexpectedResponse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(0));
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $handler->openWriteFile(self::$_testFile, STREAM_REPORT_ERRORS);
    @$handler->closeWriteFile(STREAM_REPORT_ERRORS);
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::removeFile
   */
  public function testRemoveFile() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
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
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->assertTrue(
      $handler->removeFile(self::$_testFile, STREAM_REPORT_ERRORS)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3\Handler::removeFile
   */
  public function testRemoveFileExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\HTTP\Client $client */
    $client = $this->createMock(\Papaya\HTTP\Client::class);
    $client
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(403));
    $handler = new Handler();
    $handler->setHTTPClient($client);
    $this->assertFalse(
      $handler->removeFile(self::$_testFile, 0)
    );
  }
}


