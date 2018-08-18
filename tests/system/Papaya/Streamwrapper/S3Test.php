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

namespace Papaya\Streamwrapper;
require_once __DIR__.'/../../../bootstrap.php';

class S3Test extends \Papaya\TestCase {

  const TEST_FILE =
    's3:KEYID123456789012345:1234567890123456789012345678901234567890@bucketname/objectkey';

  /**
   * @covers \Papaya\Streamwrapper\S3::setHandler
   */
  public function testSetHandler() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $client */
    $client = $this->createMock(S3\Handler::class);
    $wrapper = new S3();
    $wrapper->setHandler($client);
    $this->assertAttributeSame(
      $client, '_handler', $wrapper
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::getHandler
   */
  public function testGetHandler() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $client */
    $client = $this->createMock(S3\Handler::class);
    $wrapper = new S3();
    $wrapper->setHandler($client);
    $this->assertSame(
      $client, $wrapper->getHandler()
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::getHandler
   */
  public function testGetHandlerImplicitCreate() {
    $this->createMock(S3\Handler::class);
    $wrapper = new S3();
    $this->assertInstanceOf(
      S3\Handler::class, $wrapper->getHandler()
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::parsePath
   * @dataProvider parsePathDataProvider
   * @param string $path
   * @param array|bool $expected
   */
  public function testParsePath($path, $expected) {
    $wrapper = new S3();
    $this->assertEquals(
      $expected,
      $wrapper->parsePath($path, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::parsePath
   */
  public function testParsePathTriggersError() {
    $wrapper = new S3();
    $this->expectError(E_WARNING);
    $wrapper->parsePath('INVALID', STREAM_REPORT_ERRORS);
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::parsePath
   */
  public function testParsePathBlockedError() {
    $wrapper = new S3();
    $this->assertFalse(
      @$wrapper->parsePath('INVALID', STREAM_REPORT_ERRORS)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::parsePath
   */
  public function testParsePathSilentError() {
    $wrapper = new S3();
    $this->assertFalse(
      $wrapper->parsePath('INVALID', 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::setSecret
   */
  public function testSetSecret() {
    $id = 'KEYID123456789012345';
    $secret = '1234567890123456789012345678901234567890';
    $secrets = array($id => $secret);
    $this->assertTrue(
      S3::setSecret(
        $id,
        $secret
      )
    );
    $this->assertAttributeSame(
      $secrets, '_secrets', S3::class
    );
    S3::setSecret($id, NULL);
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::setSecret
   */
  public function testSetSecretUnset() {
    $id = 'KEYID123456789012345';
    $secret = '1234567890123456789012345678901234567890';
    S3::setSecret($id, $secret);
    $this->assertFalse(
      S3::setSecret(
        $id,
        NULL
      )
    );
    $this->assertAttributeSame(
      array(), '_secrets', S3::class
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::setSecret
   */
  public function testSetSecretWithInvalidSecret() {
    $this->assertFalse(
      S3::setSecret(
        'KEYID123456789012345',
        'INVALID'
      )
    );
    $this->assertAttributeSame(
      array(), '_secrets', S3::class
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::parsePath
   */
  public function testParsePathWithSetSecret() {
    $id = 'KEYID123456789012345';
    $secret = '1234567890123456789012345678901234567890';
    S3::setSecret($id, $secret);
    $wrapper = new S3();
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
    S3::setSecret($id, NULL);
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::parsePath
   */
  public function testParsePathForNoSecretFound() {
    $id = 'KEYID123456789012345';
    $wrapper = new S3();
    $path = 'amazon://'.$id.':@bucketname/object';
    $this->assertFalse(
      @$wrapper->parsePath($path, STREAM_REPORT_ERRORS)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::url_stat
   */
  public function testUrlStat() {
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue($fileInformation));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::url_stat
   */
  public function testUrlStatQuietForNotFound() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getFileInformations');
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations');
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertNull(
      $wrapper->url_stat(self::TEST_FILE, STREAM_URL_STAT_QUIET)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::url_stat
   */
  public function testUrlStatForNotFound() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getFileInformations');
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations');
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertNull(
      @$wrapper->url_stat(self::TEST_FILE, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::stream_open
   */
  public function testStreamOpen() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_open
   */
  public function testStreamOpenWithInvalidPath() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->never())
      ->method('readFileContent');
    $handler
      ->expects($this->never())
      ->method('openWriteFile');
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_open
   */
  public function testStreamOpenNotFoundSuppressedWarning() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(NULL));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_open
   */
  public function testStreamOpenNotFoundWarning() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(NULL));
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $openedPath = NULL;
    $this->expectError(E_WARNING);
    $wrapper->stream_open(
      self::TEST_FILE,
      'r',
      0,
      $openedPath
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::stream_open
   * @dataProvider streamOpenUnsupportedModeDataProvider
   * @param string $mode
   */
  public function testStreamOpenUnsupportedModeSuppressedWarning($mode) {
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_open
   * @dataProvider streamOpenUnsupportedModeDataProvider
   * @param string $mode
   */
  public function testStreamOpenUnsupportedModeWarning($mode) {
    $wrapper = new S3();
    $openedPath = NULL;
    $this->expectError(E_WARNING);
    $wrapper->stream_open(
      self::TEST_FILE,
      $mode,
      STREAM_REPORT_ERRORS,
      $openedPath
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::stream_stat
   */
  public function testStreamStat() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_stat
   */
  public function testStreamStatWriteable() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $testContent = 'testContent';
    $handler
      ->expects($this->once())
      ->method('writeFileContent')
      ->with($this->anything(), $this->equalTo($testContent))
      ->will($this->returnValue(strlen($testContent)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_tell
   */
  public function testStreamTell() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_stat
   */
  public function testStreamTellWritten() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $testContent = 'testContent';
    $handler
      ->expects($this->once())
      ->method('writeFileContent')
      ->with($this->anything(), $this->equalTo($testContent))
      ->will($this->returnValue(strlen($testContent)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_seek
   */
  public function testStreamSeekSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_seek
   */
  public function testStreamSeekSetExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_seek
   */
  public function testStreamSeekSetAfterEOF() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_seek
   */
  public function testStreamSeekCurrent() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_seek
   */
  public function testStreamSeekCurrentExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_seek
   */
  public function testStreamSeekEnd() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_seek
   */
  public function testStreamSeekEndExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_seek
   */
  public function testStreamSeekWriteable() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_eof
   */
  public function testStreamEOF() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_eof
   */
  public function testStreamEOFExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_read
   */
  public function testStreamRead() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $testContent = 'testContent';
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array($testContent, $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_read
   */
  public function testStreamReadNothing() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_read
   */
  public function testStreamReadEOF() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_read
   */
  public function testStreamReadEmptyResult() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->exactly(2))
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_read
   */
  public function testStreamReadFromBuffer() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $testContent = 'testContent';
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array($testContent, $fileInformation)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_read
   * @covers \Papaya\Streamwrapper\S3::fillBuffer
   */
  public function testStreamReadOverBufferEnd() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $testContent = 'testContent';
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
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_read
   * @covers \Papaya\Streamwrapper\S3::fillBuffer
   */
  public function testStreamReadWithFilesSizeBiggerThanBufferSize() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 1024 * 1024 + 1,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $testContent = 'testContent';
    $handler
      ->expects($this->exactly(2))
      ->method('readFileContent')
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue(
            array('', $fileInformation)
          ),
          $this->returnValue(
            array($testContent, $fileInformation)
          )
        )
      );
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::fillBuffer
   */
  public function testFillBufferWithoutSizeNorForce() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->never())
      ->method('readFileContent');
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertNull(
      $wrapper->fillBuffer(FALSE)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::dir_opendir
   */
  public function testDirOpen() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->dir_opendir(
        self::TEST_FILE,
        0
      )
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::dir_opendir
   */
  public function testDirOpenExpectingNotFoundWarning() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->expectError(E_WARNING);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      0
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::dir_opendir
   */
  public function testDirOpenSuppressedNotFoundWarning() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->dir_opendir(
        self::TEST_FILE,
        0
      )
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::dir_readdir
   */
  public function testDirRead() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::dir_readdir
   */
  public function testDirReadMore() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::dir_readdir
   */
  public function testDirReadNoMore() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::dir_readdir
   */
  public function testDirReadExpectingNotFoundWarning() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $wrapper->dir_opendir(
      self::TEST_FILE,
      STREAM_REPORT_ERRORS
    );
    $this->expectError(E_WARNING);
    $wrapper->dir_readdir();
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::dir_readdir
   */
  public function testDirReadSuppressedNotFoundWarning() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::dir_readdir
   */
  public function testDirReadNotFound() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::dir_rewinddir
   */
  public function testDirRewind() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::dir_rewinddir
   */
  public function testDirRewindWithReload() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_open
   */
  public function testStreamOpenForWrite() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('openWriteFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_write
   */
  public function testStreamWrite() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $testContent = 'testContent';
    $handler
      ->expects($this->once())
      ->method('writeFileContent')
      ->with($this->anything(), $this->equalTo($testContent))
      ->will($this->returnValue(strlen($testContent)));
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_close
   */
  public function testStreamCloseReadable() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $fileInformation = array(
      'size' => 23,
      'modified' => 1257167160,
      'mode' => 0100006
    );
    $handler
      ->expects($this->once())
      ->method('readFileContent')
      ->will($this->returnValue(array('', $fileInformation)));
    $handler
      ->expects($this->never())
      ->method('closeWriteFile');
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::stream_close
   */
  public function testStreamCloseWriteable() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('closeWriteFile');
    $wrapper = new S3();
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
   * @covers \Papaya\Streamwrapper\S3::unlink
   */
  public function testUnlink() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(array()));
    $handler
      ->expects($this->once())
      ->method('removeFile')
      ->will($this->returnValue(TRUE));
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->unlink(self::TEST_FILE)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::unlink
   */
  public function testUnlinkWithNonExistingFile() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->unlink(self::TEST_FILE)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::unlink
   */
  public function testUnlinkWithInvalidPath() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->unlink('INVALID')
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::mkdir
   */
  public function testMakeDirectory() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::mkdir
   */
  public function testMakeDirectoryWithSlash() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $testFile =
      's3:KEYID123456789012345:1234567890123456789012345678901234567890'.
      '@bucketname/objectkey/';
    $this->assertTrue(
      $wrapper->mkdir($testFile, 0, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::mkdir
   */
  public function testMakeDirectoryWithFileExists() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getFileInformations')
      ->will($this->returnValue(array()));
    $handler
      ->expects($this->never())
      ->method('openWriteFile');
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::mkdir
   */
  public function testMakeDirectoryWithFailingOpen() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      $wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::mkdir
   */
  public function testMakeDirectoryWithFailing2ndOpen() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      $wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::mkdir
   */
  public function testMakeDirectoryExists() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(array()));
    $handler
      ->expects($this->never())
      ->method('openWriteFile');
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->mkdir(self::TEST_FILE, 0, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::mkdir
   */
  public function testMakeDirectoryWithInvalidPath() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->never())
      ->method('getFileInformations');
    $handler
      ->expects($this->never())
      ->method('getDirectoryInformations');
    $handler
      ->expects($this->never())
      ->method('openWriteFile');
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->mkdir('INVALID', 0, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::rmdir
   */
  public function testRemoveDirectory() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->rmdir(self::TEST_FILE, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::rmdir
   */
  public function testRemoveDirectoryWithSlash() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertTrue(
      $wrapper->rmdir(self::TEST_FILE.'/', 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::rmdir
   */
  public function testRemoveDirectoryWithContent() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->rmdir(self::TEST_FILE, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::rmdir
   */
  public function testRemoveDirectoryWithMoreContent() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
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
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->rmdir(self::TEST_FILE, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::rmdir
   */
  public function testRemoveDirectoryNotExisting() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->once())
      ->method('getDirectoryInformations')
      ->will($this->returnValue(NULL));
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new S3();
    $wrapper->setHandler($handler);
    $this->assertFalse(
      @$wrapper->rmdir(self::TEST_FILE, 0)
    );
  }

  /**
   * @covers \Papaya\Streamwrapper\S3::rmdir
   */
  public function testRemoveDirectoryWithInvalidPath() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
    $handler = $this->createMock(S3\Handler::class);
    $handler
      ->expects($this->never())
      ->method('getDirectoryInformations');
    $handler
      ->expects($this->never())
      ->method('removeFile');
    $wrapper = new S3();
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

