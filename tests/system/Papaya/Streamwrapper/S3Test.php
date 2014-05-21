<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaStreamwrapperS3Test extends PapayaTestCase {

  const TEST_FILE =
    's3:KEYID123456789012345:1234567890123456789012345678901234567890@bucketname/objectkey';

  /**
  * @covers PapayaStreamwrapperS3::setHandler
  */
  public function testSetHandler() {
    $client = $this->getMock('PapayaStreamwrapperS3Handler');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($client);
    $this->assertAttributeSame(
      $client, '_handler', $wrapper
    );
  }

  /**
  * @covers PapayaStreamwrapperS3::getHandler
  */
  public function testGetHandler() {
    $client = $this->getMock('PapayaStreamwrapperS3Handler');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($client);
    $this->assertSame(
      $client, $wrapper->getHandler()
    );
  }

  /**
  * @covers PapayaStreamwrapperS3::getHandler
  */
  public function testGetHandlerImplicitCreate() {
    $client = $this->getMock('PapayaStreamwrapperS3Handler');
    $wrapper = new PapayaStreamwrapperS3();
    $this->assertInstanceOf(
      'PapayaStreamwrapperS3Handler', $wrapper->getHandler()
    );
  }

  /**
  * @covers PapayaStreamwrapperS3::parsePath
  * @dataProvider parsePathDataProvider
  */
  public function testParsePath($path, $expected) {
    $wrapper = new PapayaStreamwrapperS3();
    $this->assertEquals(
      $expected,
      $wrapper->parsePath($path, 0)
    );
  }

  /**
  * @covers PapayaStreamwrapperS3::parsePath
  */
  public function testParsePathTriggersError() {
    $wrapper = new PapayaStreamwrapperS3();
    $this->setExpectedException('PHPUnit_Framework_Error_Warning');
    $wrapper->parsePath('INVALID', STREAM_REPORT_ERRORS);
  }

  /**
  * @covers PapayaStreamwrapperS3::parsePath
  */
  public function testParsePathBlockedError() {
    $wrapper = new PapayaStreamwrapperS3();
    $this->assertFalse(
      @$wrapper->parsePath('INVALID', STREAM_REPORT_ERRORS)
    );
  }

  /**
  * @covers PapayaStreamwrapperS3::parsePath
  */
  public function testParsePathSilentError() {
    $wrapper = new PapayaStreamwrapperS3();
    $this->assertFalse(
      $wrapper->parsePath('INVALID', 0)
    );
  }

  /**
  * @covers PapayaStreamwrapperS3::setSecret
  */
  public function testSetSecret() {
    $wrapper = new PapayaStreamwrapperS3();
    $id = 'KEYID123456789012345';
    $secret = '1234567890123456789012345678901234567890';
    $secrets = array($id => $secret);
    $this->assertTrue(
      $wrapper->setSecret(
        $id,
        $secret
      )
    );
    $this->assertAttributeSame(
      $secrets, '_secrets', 'PapayaStreamwrapperS3'
    );
    $wrapper->setSecret($id, NULL);
  }

  /**
  * @covers PapayaStreamwrapperS3::setSecret
  */
  public function testSetSecretUnset() {
    $wrapper = new PapayaStreamwrapperS3();
    $id = 'KEYID123456789012345';
    $secret = '1234567890123456789012345678901234567890';
    $wrapper->setSecret($id, $secret);
    $this->assertFalse(
      $wrapper->setSecret(
        $id,
        NULL
      )
    );
    $this->assertAttributeSame(
      array(), '_secrets', 'PapayaStreamwrapperS3'
    );
  }

  /**
  * @covers PapayaStreamwrapperS3::setSecret
  */
  public function testSetSecretWithInvalidSecret() {
    $wrapper = new PapayaStreamwrapperS3();
    $this->assertFalse(
      $wrapper->setSecret(
        'KEYID123456789012345',
        'INVALID'
      )
    );
    $this->assertAttributeSame(
      array(), '_secrets', 'PapayaStreamwrapperS3'
    );
  }

  /**
  * @covers PapayaStreamwrapperS3::parsePath
  */
  public function testParsePathWithSetSecret() {
    $id = 'KEYID123456789012345';
    $secret = '1234567890123456789012345678901234567890';
    PapayaStreamwrapperS3::setSecret($id, $secret);
    $wrapper = new PapayaStreamwrapperS3();
    $path = 'amazon://'.$id.':@bucketname/object';
    $expected = array(
      'bucket' => 'bucketname',
      'id' => $id,
      'secret' => $secret,
      'object' => 'object'
    );
    $this->assertEquals(
      $expected,
      $wrapper->parsePath($path, STREAM_REPORT_ERRORS)
    );
    $wrapper->setSecret($id, NULL);
  }

  /**
  * @covers PapayaStreamwrapperS3::parsePath
  */
  public function testParsePathForNoSecretFound() {
    $id = 'KEYID123456789012345';
    $wrapper = new PapayaStreamwrapperS3();
    $path = 'amazon://'.$id.':@bucketname/object';
    $this->assertFalse(
      @$wrapper->parsePath($path, STREAM_REPORT_ERRORS)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::url_stat
  */
  public function testUrlStat() {
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue($fileInformation));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertEquals(
      array(
       'dev' => 0,
       'ino' => 0,
       'mode' => 0100006,
       'nlink' => 0,
       'uid' => 0,
       'gid' => 0,
       'rdev' => 0,
       'size' => 23,
       'atime' => 1257167160,
       'mtime' => 1257167160,
       'ctime' => 1257167160,
       'blksize' => 0,
       'blocks' => -1
      ),
      $wrapper->url_stat(self::TEST_FILE, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::url_stat
  */
  public function testUrlStatQuietForNotFound() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations');
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertNull(
      $wrapper->url_stat(self::TEST_FILE, STREAM_URL_STAT_QUIET)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::url_stat
  */
  public function testUrlStatForNotFound() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations');
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertNull(
      @$wrapper->url_stat(self::TEST_FILE, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_open
  */
  public function testStreamOpen() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $this->assertTrue(
      $wrapper->stream_open(
        self::TEST_FILE,
        'r',
        0,
        $openedPath
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_open
  */
  public function testStreamOpenWithInvalidPath() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->never())
      ->method('readFileContent');
    $handler
      ->expects($this->never())
      ->method('openWriteFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $this->assertFalse(
      @$wrapper->stream_open(
        'INVALID',
        'r',
        0,
        $openedPath
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_open
  */
  public function testStreamOpenNotFoundSuppressedWarning() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(NULL));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $this->assertFalse(
      @$wrapper->stream_open(
        self::TEST_FILE,
        'r',
        0,
        $openedPath
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_open
  */
  public function testStreamOpenNotFoundWarning() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(NULL));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $this->setExpectedException('PHPUnit_Framework_Error_Warning');
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_open
  * @dataProvider streamOpenUnsupportedModeDataProvider
  */
  public function testStreamOpenUnsupportedModeSuppressedWarning($mode) {
    $wrapper = new PapayaStreamwrapperS3();
    $openedPath = NULL;
    $this->assertFalse(
      @$wrapper->stream_open(
        self::TEST_FILE,
        $mode,
        STREAM_REPORT_ERRORS,
        $openedPath
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_open
  * @dataProvider streamOpenUnsupportedModeDataProvider
  */
  public function testStreamOpenUnsupportedModeWarning($mode) {
    $wrapper = new PapayaStreamwrapperS3();
    $openedPath = NULL;
    $this->setExpectedException('PHPUnit_Framework_Error_Warning');
    $wrapper->stream_open(
      self::TEST_FILE,
      $mode,
      STREAM_REPORT_ERRORS,
      $openedPath
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_stat
  */
  public function testStreamStat() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertEquals(
      array(
       'dev' => 0,
       'ino' => 0,
       'mode' => 0100006,
       'nlink' => 0,
       'uid' => 0,
       'gid' => 0,
       'rdev' => 0,
       'size' => 23,
       'atime' => 1257167160,
       'mtime' => 1257167160,
       'ctime' => 1257167160,
       'blksize' => 0,
       'blocks' => -1
      ),
      $wrapper->stream_stat()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_stat
  */
  public function testStreamStatWriteable() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $testContent = "testContent";
    $handler
      ->expects($this->once())
      ->method('writeFileContent')
      ->with($this->anything(), $this->equalTo($testContent))
      ->will($this->returnValue(strlen($testContent)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'w',
      STREAM_REPORT_ERRORS,
      $openedPath
    );
    $wrapper->stream_write($testContent);
    $result = $wrapper->stream_stat();
    $this->assertInternalType('array', $result);
    $this->assertEquals($result['size'], strlen($testContent));
    $this->assertLessThan($result['atime'], 0);
    $this->assertLessThan($result['mtime'], 0);
    $this->assertLessThan($result['ctime'], 0);
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_tell
  */
  public function testStreamTell() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertEquals(
      0,
      $wrapper->stream_tell()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_stat
  */
  public function testStreamTellWritten() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $testContent = "testContent";
    $handler
      ->expects($this->once())
      ->method('writeFileContent')
      ->with($this->anything(), $this->equalTo($testContent))
      ->will($this->returnValue(strlen($testContent)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'w',
      STREAM_REPORT_ERRORS,
      $openedPath
    );
    $wrapper->stream_write($testContent);
    $this->assertEquals(
      strlen($testContent),
      $wrapper->stream_tell()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_seek
  */
  public function testStreamSeekSet() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertTrue(
      $wrapper->stream_seek(22)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_seek
  */
  public function testStreamSeekSetExpectingFalse() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertFalse(
      $wrapper->stream_seek(-1)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_seek
  */
  public function testStreamSeekSetAfterEOF() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertTrue(
      $wrapper->stream_seek(42)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_seek
  */
  public function testStreamSeekCurrent() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertTrue(
      $wrapper->stream_seek(22, SEEK_CUR)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_seek
  */
  public function testStreamSeekCurrentExpectingFalse() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertFalse(
      $wrapper->stream_seek(-1, SEEK_CUR)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_seek
  */
  public function testStreamSeekEnd() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertTrue(
      $wrapper->stream_seek(0, SEEK_END)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_seek
  */
  public function testStreamSeekEndExpectingFalse() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertFalse(
      $wrapper->stream_seek(-24, SEEK_END)
    );
  }

  public function testStreamSeekEndWithInvalidWhence() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertFalse(
      $wrapper->stream_seek(0, -1)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_seek
  */
  public function testStreamSeekWriteable() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'w',
      0,
      $openedPath
    );
    $this->assertFalse(
      @$wrapper->stream_seek(0, SEEK_CUR)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_eof
  */
  public function testStreamEOF() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $wrapper->stream_seek(0, SEEK_END);
    $this->assertTrue(
      $wrapper->stream_eof()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_eof
  */
  public function testStreamEOFExpectingFalse() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $wrapper->stream_seek(-1, SEEK_END);
    $this->assertFalse(
      $wrapper->stream_eof()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_read
  */
  public function testStreamRead() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $testContent = "testContent";
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array($testContent, $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertSame(
      $testContent,
      $wrapper->stream_read(23)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_read
  */
  public function testStreamReadNothing() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertSame(
      '',
      $wrapper->stream_read(0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_read
  */
  public function testStreamReadEOF() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $wrapper->stream_seek(0, SEEK_END);
    $this->assertSame(
      '',
      $wrapper->stream_read(1)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_read
  */
  public function testStreamReadEmptyResult() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->exactly(2))
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertSame(
      '',
      $wrapper->stream_read(1)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_read
  */
  public function testStreamReadFromBuffer() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $testContent = "testContent";
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array($testContent, $fileInformation)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertSame(
      substr($testContent, 0, 4),
      $wrapper->stream_read(4)
    );
    $this->assertSame(
      substr($testContent, 4, 4),
      $wrapper->stream_read(4)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_read
  * @covers PapayaStreamWrapperS3::fillBuffer
  */
  public function testStreamReadOverBufferEnd() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $testContent = "testContent";
    $handler
      ->expects($this->exactly(2))
      ->method('readFileContent')
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue(
            array(substr($testContent, 0, 8), $fileInformation)
          ),
          $this->returnValue(
            array(substr($testContent, 8), $fileInformation)
          )
        )
      );
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertSame(
      substr($testContent, 0, 8),
      $wrapper->stream_read(8)
    );
    $this->assertSame(
      substr($testContent, 8),
      $wrapper->stream_read(4)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_read
  * @covers PapayaStreamWrapperS3::fillBuffer
  */
  public function testStreamReadWithFilesSizeBiggerThanBufferSize() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 1024 * 1024 + 1,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $testContent = "testContent";
    $handler
      ->expects($this->exactly(2))
      ->method('readFileContent')
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue(
            array("", $fileInformation)
          ),
          $this->returnValue(
            array($testContent, $fileInformation)
          )
        )
      );
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
    $this->assertSame(
      $testContent,
      $wrapper->stream_read(1024 * 1024 + 1)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::fillBuffer
  */
  public function testFillBufferWithoutSizeNorForce() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->never())
      ->method('readFileContent');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertNull(
      $wrapper->fillBuffer(FALSE)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_opendir
  */
  public function testDirOpen() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array(),
      'moreContent' => FALSE,
      'startMarker' => '',
    );
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->dir_opendir(
        self::TEST_FILE,
        0
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_opendir
  */
  public function testDirOpenExpectingNotFoundWarning() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->setExpectedException('PHPUnit_Framework_Error_Warning');
    $wrapper->dir_opendir(
      self::TEST_FILE,
      0
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_opendir
  */
  public function testDirOpenSuppressedNotFoundWarning() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->dir_opendir(
        self::TEST_FILE,
        0
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_readdir
  */
  public function testDirRead() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array('test'),
      'moreContent' => FALSE,
      'startMarker' => '',
    );
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      0
    );
    $this->assertSame(
      'test',
      $wrapper->dir_readdir()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_readdir
  */
  public function testDirReadMore() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array('test'),
      'moreContent' => TRUE,
      'startMarker' => '',
    );
    $directoryInformation2 = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array('test2'),
      'moreContent' => TRUE,
      'startMarker' => 'test',
    );
    $handler
      ->expects($this->at(0))
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $handler
      ->expects($this->at(1))
      ->method('getDirectoryInformations')
      ->with($this->anything(), $this->anything(), $this->anything(), 'test')
      ->will($this->returnValue($directoryInformation2));
    $handler
      ->expects($this->exactly(2))
      ->method('getDirectoryInformations');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      0
    );
    $wrapper->dir_readdir();
    $this->assertSame(
      'test2',
      $wrapper->dir_readdir()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_readdir
  */
  public function testDirReadNoMore() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array('test'),
      'moreContent' => FALSE,
      'startMarker' => '',
    );
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      0
    );
    $wrapper->dir_readdir();
    $this->assertFalse(
      $wrapper->dir_readdir()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_readdir
  */
  public function testDirReadExpectingNotFoundWarning() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array(),
      'moreContent' => TRUE,
      'startMarker' => '',
    );
    $handler
      ->expects($this->at(0))
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $handler
      ->expects($this->at(1))
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      STREAM_REPORT_ERRORS
    );
    $this->setExpectedException('PHPUnit_Framework_Error_Warning');
    $wrapper->dir_readdir();
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_readdir
  */
  public function testDirReadSuppressedNotFoundWarning() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array(),
      'moreContent' => TRUE,
      'startMarker' => '',
    );
    $handler
      ->expects($this->at(0))
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $handler
      ->expects($this->at(1))
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      STREAM_REPORT_ERRORS
    );
    $this->assertFalse(
      @$wrapper->dir_readdir()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_readdir
  */
  public function testDirReadNotFound() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array(),
      'moreContent' => TRUE,
      'startMarker' => '',
    );
    $handler
      ->expects($this->at(0))
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $handler
      ->expects($this->at(1))
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      0
    );
    $this->assertFalse(
      @$wrapper->dir_readdir()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_rewinddir
  */
  public function testDirRewind() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array('test1', 'test2'),
      'moreContent' => FALSE,
      'startMarker' => '',
    );
    $handler
      ->expects($this->exactly(1))
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      0
    );
    $this->assertSame(
      'test1',
      $wrapper->dir_readdir()
    );
    $this->assertTrue(
      $wrapper->dir_rewinddir()
    );
    $this->assertSame(
      'test1',
      $wrapper->dir_readdir()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::dir_rewinddir
  */
  public function testDirRewindWithReload() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'size' => 0,
      'modified' => 0,
      'mode' => 040006,
      'contents' => array('test1', 'test2'),
      'moreContent' => FALSE,
      'startMarker' => 'test',
    );
    $handler
      ->expects($this->exactly(2))
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      0
    );
    $this->assertSame(
      'test1',
      $wrapper->dir_readdir()
    );
    $this->assertTrue(
      $wrapper->dir_rewinddir()
    );
    $this->assertSame(
      'test1',
      $wrapper->dir_readdir()
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_open
  */
  public function testStreamOpenForWrite() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $this->assertTrue(
      $wrapper->stream_open(
        self::TEST_FILE,
        'w',
        STREAM_REPORT_ERRORS,
        $openedPath
      )
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_write
  */
  public function testStreamWrite() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $testContent = "testContent";
    $handler
      ->expects($this->once())
      ->method('writeFileContent')
      ->with($this->anything(), $this->equalTo($testContent))
      ->will($this->returnValue(strlen($testContent)));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'w',
      STREAM_REPORT_ERRORS,
      $openedPath
    );
    $this->assertSame(
      strlen($testContent),
      $wrapper->stream_write($testContent)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_close
  */
  public function testStreamCloseReadable() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array("", $fileInformation)));
    $handler
      ->expects($this->never())
      ->method('closeWriteFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      STREAM_REPORT_ERRORS,
      $openedPath
    );
    $wrapper->stream_close();
  }

  /**
  * @covers PapayaStreamWrapperS3::stream_close
  */
  public function testStreamCloseWriteable() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('closeWriteFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $wrapper->stream_open(
      self::TEST_FILE,
      'w',
      STREAM_REPORT_ERRORS,
      $openedPath
    );
    $wrapper->stream_close();
  }

  /**
  * @covers PapayaStreamWrapperS3::unlink
  */
  public function testUnlink() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(array()));
    $handler
      ->expects($this->once())
      ->method('removeFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->unlink(self::TEST_FILE)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::unlink
  */
  public function testUnlinkWithNonExistingFile() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->unlink(self::TEST_FILE)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::unlink
  */
  public function testUnlinkWithInvalidPath() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->unlink('INVALID')
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::mkdir
  */
  public function testMakeDirectory() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->exactly(2))
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::mkdir
  */
  public function testMakeDirectoryWithSlash() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->exactly(2))
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $testFile =
      's3:KEYID123456789012345:1234567890123456789012345678901234567890'.
        '@bucketname/objectkey/';
    $this->assertTrue(
      $wrapper->mkdir($testFile, 0, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::mkdir
  */
  public function testMakeDirectoryWithFileExists() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(array()));
    $handler
      ->expects($this->never())
      ->method('openWriteFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::mkdir
  */
  public function testMakeDirectoryWithFailingOpen() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->once())
      ->method('openWriteFile')
      ->will($this->returnValue(FALSE));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      $wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::mkdir
  */
  public function testMakeDirectoryWithFailing2ndOpen() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->exactly(2))
      ->method('openWriteFile')
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue(TRUE),
          $this->returnValue(FALSE)
        )
      );
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      $wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::mkdir
  */
  public function testMakeDirectoryExists() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(array()));
    $handler
      ->expects($this->never())
      ->method('openWriteFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::mkdir
  */
  public function testMakeDirectoryWithInvalidPath() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->never())
      ->method('getFileInformations');
    $handler
      ->expects($this->never())
      ->method('getDirectoryInformations');
    $handler
      ->expects($this->never())
      ->method('openWriteFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->mkdir('INVALID', 0, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::rmdir
  */
  public function testRemoveDirectory() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'moreContent' => FALSE,
      'contents' => array('$'),
    );
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $handler
      ->expects($this->exactly(3))
      ->method('removeFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->rmdir(self::TEST_FILE, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::rmdir
  */
  public function testRemoveDirectoryWithSlash() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'moreContent' => FALSE,
      'contents' => array('$'),
    );
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $handler
      ->expects($this->exactly(3))
      ->method('removeFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->rmdir(self::TEST_FILE.'/', 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::rmdir
  */
  public function testRemoveDirectoryWithContent() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'moreContent' => FALSE,
      'contents' => array('foo', 'bar'),
    );
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->rmdir(self::TEST_FILE, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::rmdir
  */
  public function testRemoveDirectoryWithMoreContent() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $directoryInformation = array(
      'moreContent' => TRUE,
      'contents' => array('$'),
    );
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue($directoryInformation));
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->rmdir(self::TEST_FILE, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::rmdir
  */
  public function testRemoveDirectoryNotExisting() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->rmdir(self::TEST_FILE, 0)
    );
  }

  /**
  * @covers PapayaStreamWrapperS3::rmdir
  */
  public function testRemoveDirectoryWithInvalidPath() {
    $handler = $this->getMock('PapayaStreamwrapperS3Handler');
    $handler
      ->expects($this->never())
      ->method('getDirectoryInformations');
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new PapayaStreamwrapperS3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->rmdir('INVALID', 0)
    );
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function parsePathDataProvider() {
    return array(
      array(
        's3:KEYID123456789012345:1234567890123456789012345678901234567890@bucketname/objectkey',
        array(
          'bucket' => 'bucketname',
          'id' => 'KEYID123456789012345',
          'secret' => '1234567890123456789012345678901234567890',
          'object' => 'objectkey'
        )
      ),
      array(
        'amazon://KEYID123456789012345:1234567890123456789012345678901234567890@bucketname/object',
        array(
          'bucket' => 'bucketname',
          'id' => 'KEYID123456789012345',
          'secret' => '1234567890123456789012345678901234567890',
          'object' => 'object'
        )
      )
    );
  }

  public static function streamOpenUnsupportedModeDataProvider() {
    return array(
      array('r+'),
      array('w+'),
      array('a'),
      array('a+'),
      array('x'),
      array('x+'),
    );
  }
}

