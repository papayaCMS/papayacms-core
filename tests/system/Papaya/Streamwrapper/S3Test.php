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

namespace Papaya\Streamwrapper {

  use Papaya\Streamwrapper\S3\S3Exception;
  use Papaya\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Streamwrapper\S3
   */
  class S3Test extends TestCase {

    const TEST_FILE =
      's3:KEYID123456789012345:1234567890123456789012345678901234567890@bucketname/objectkey';

    public function testRegister() {
      S3::register('s3-test');
      S3::register('s3-test');
      $this->assertContains('s3-test', stream_get_wrappers());
      stream_wrapper_unregister('s3-test');
    }

    public function testSetAndGetHandler() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $client */
      $client = $this->createMock(S3\Handler::class);
      $wrapper = new S3();
      $wrapper->setHandler($client);
      $this->assertSame(
        $client, $wrapper->getHandler()
      );
    }

    public function testGetHandlerImplicitCreate() {
      $this->createMock(S3\Handler::class);
      $wrapper = new S3();
      $this->assertInstanceOf(
        S3\Handler::class, $wrapper->getHandler()
      );
    }

    /**
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

    public function testParsePathTriggersError() {
      $wrapper = new S3();
      $this->expectException(S3Exception::class);
      $wrapper->parsePath('INVALID', STREAM_REPORT_ERRORS);
    }

    public function testParsePathSilentError() {
      $wrapper = new S3();
      $this->assertFalse(
        $wrapper->parsePath('INVALID', 0)
      );
    }

    public function testSetSecretWithInvalidSecret() {
      $this->assertFalse(
        S3::setSecret(
          'KEYID123456789012345',
          'INVALID'
        )
      );
    }

    public function testParsePathWithSetSecret() {
      $id = 'KEYID123456789012345';
      $secret = '1234567890123456789012345678901234567890';
      S3::setSecret($id, $secret);
      $wrapper = new S3();
      $path = 'amazon://'.$id.':@bucketname/object';
      $expected = [
        'bucket' => 'bucketname',
        'id' => $id,
        'secret' => $secret,
        'object' => 'object'
      ];
      $this->assertEquals(
        $expected,
        $wrapper->parsePath($path, STREAM_REPORT_ERRORS)
      );
      S3::setSecret($id, NULL);
    }

    public function testUrlStat() {
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->method('getFileInformations')
        ->willReturn($fileInformation);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->assertEquals(
        [
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
        ],
        $wrapper->url_stat(self::TEST_FILE, 0)
      );
    }

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

    public function testStreamOpen() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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
      $this->expectException(S3Exception::class);
      $wrapper->stream_open(
        'INVALID',
        'r',
        0,
        $openedPath
      );
    }

    public function testStreamOpenNotFound() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(NULL);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $openedPath = NULL;
      $this->expectException(S3Exception::class);
      $wrapper->stream_open(
        self::TEST_FILE,
        'r',
        0,
        $openedPath
      );
    }

    /**
     * @dataProvider streamOpenUnsupportedModeDataProvider
     * @param string $mode
     */
    public function testStreamOpenUnsupportedMode($mode) {
      $wrapper = new S3();
      $openedPath = NULL;
      $this->expectException(S3Exception::class);
      $wrapper->stream_open(
        self::TEST_FILE,
        $mode,
        STREAM_REPORT_ERRORS,
        $openedPath
      );
    }

    /**
     * @dataProvider streamOpenUnsupportedModeDataProvider
     * @param string $mode
     */
    public function testStreamOpenUnsupportedModeWarning($mode) {
      $wrapper = new S3();
      $openedPath = NULL;
      $this->expectException(S3Exception::class);
      $wrapper->stream_open(
        self::TEST_FILE,
        $mode,
        STREAM_REPORT_ERRORS,
        $openedPath
      );
    }

    public function testStreamStat() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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
        [
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
        ],
        $wrapper->stream_stat()
      );
    }

    public function testStreamStatWriteable() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('openWriteFile')
        ->willReturn(TRUE);
      $testContent = 'testContent';
      $handler
        ->expects($this->once())
        ->method('writeFileContent')
        ->with($this->anything(), $this->equalTo($testContent))
        ->willReturn(strlen($testContent));
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
      $this->assertIsArray($result);
      $this->assertEquals($result['size'], strlen($testContent));
      $this->assertLessThan($result['atime'], 0);
      $this->assertLessThan($result['mtime'], 0);
      $this->assertLessThan($result['ctime'], 0);
    }

    public function testStreamTell() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamTellWritten() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('openWriteFile')
        ->willReturn(TRUE);
      $testContent = 'testContent';
      $handler
        ->expects($this->once())
        ->method('writeFileContent')
        ->with($this->anything(), $this->equalTo($testContent))
        ->willReturn(strlen($testContent));
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

    public function testStreamSeekSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamSeekSetExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamSeekSetAfterEOF() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamSeekCurrent() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamSeekCurrentExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamSeekEnd() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamSeekEndExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamSeekWriteable() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('openWriteFile')
        ->willReturn(TRUE);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $openedPath = NULL;
      $wrapper->stream_open(
        self::TEST_FILE,
        'w',
        0,
        $openedPath
      );
      $this->expectException(S3Exception::class);
      $wrapper->stream_seek(0, SEEK_CUR);
    }

    public function testStreamEOF() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamEOFExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamRead() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $testContent = 'testContent';
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn([$testContent, $fileInformation]);
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

    public function testStreamReadNothing() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamReadEOF() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamReadEmptyResult() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->exactly(2))
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testStreamReadFromBuffer() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $testContent = 'testContent';
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn([$testContent, $fileInformation]);
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

    public function testStreamReadOverBufferEnd() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $testContent = 'testContent';
      $handler
        ->expects($this->exactly(2))
        ->method('readFileContent')
        ->will(
          $this->onConsecutiveCalls(
            $this->returnValue(
              [substr($testContent, 0, 8), $fileInformation]
            ),
            $this->returnValue(
              [substr($testContent, 8), $fileInformation]
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

    public function testStreamReadWithFilesSizeBiggerThanBufferSize() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 1024 * 1024 + 1,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $testContent = 'testContent';
      $handler
        ->expects($this->exactly(2))
        ->method('readFileContent')
        ->will(
          $this->onConsecutiveCalls(
            $this->returnValue(
              ['', $fileInformation]
            ),
            $this->returnValue(
              [$testContent, $fileInformation]
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

    public function testDirOpen() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => [],
        'moreContent' => FALSE,
        'startMarker' => '',
      ];
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->assertTrue(
        $wrapper->dir_opendir(
          self::TEST_FILE,
          0
        )
      );
    }

    public function testDirOpenExpectingNotFoundWarning() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->expectException(S3Exception::class);
      $wrapper->dir_opendir(
        self::TEST_FILE,
        0
      );
    }

    public function testDirOpenSuppressedNotFoundWarning() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->expectException(S3Exception::class);
      $wrapper->dir_opendir(
        self::TEST_FILE,
        0
      );
    }

    public function testDirRead() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => ['test'],
        'moreContent' => FALSE,
        'startMarker' => '',
      ];
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
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

    public function testDirReadMore() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => ['test'],
        'moreContent' => TRUE,
        'startMarker' => '',
      ];
      $directoryInformation2 = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => ['test2'],
        'moreContent' => TRUE,
        'startMarker' => 'test',
      ];
      $handler
        ->expects($this->at(0))
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
      $handler
        ->expects($this->at(1))
        ->method('getDirectoryInformations')
        ->with($this->anything(), $this->anything(), $this->anything(), 'test')
        ->willReturn($directoryInformation2);
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

    public function testDirReadNoMore() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => ['test'],
        'moreContent' => FALSE,
        'startMarker' => '',
      ];
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
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

    public function testDirReadExpectingNotFoundWarning() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => [],
        'moreContent' => TRUE,
        'startMarker' => '',
      ];
      $handler
        ->expects($this->at(0))
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
      $handler
        ->expects($this->at(1))
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $wrapper->dir_opendir(
        self::TEST_FILE,
        STREAM_REPORT_ERRORS
      );
      $this->expectException(S3Exception::class);
      $wrapper->dir_readdir();
    }

    public function testDirReadSuppressedNotFoundWarning() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => [],
        'moreContent' => TRUE,
        'startMarker' => '',
      ];
      $handler
        ->expects($this->at(0))
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
      $handler
        ->expects($this->at(1))
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $wrapper->dir_opendir(
        self::TEST_FILE,
        STREAM_REPORT_ERRORS
      );
      $this->expectException(S3Exception::class);
      $wrapper->dir_readdir();
    }

    public function testDirReadNotFound() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => [],
        'moreContent' => TRUE,
        'startMarker' => '',
      ];
      $handler
        ->expects($this->at(0))
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
      $handler
        ->expects($this->at(1))
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $wrapper->dir_opendir(
        self::TEST_FILE,
        0
      );
      $this->expectException(S3Exception::class);
      $wrapper->dir_readdir();
    }

    public function testDirRewind() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => ['test1', 'test2'],
        'moreContent' => FALSE,
        'startMarker' => '',
      ];
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
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

    public function testDirRewindWithReload() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'size' => 0,
        'modified' => 0,
        'mode' => 040006,
        'contents' => ['test1', 'test2'],
        'moreContent' => FALSE,
        'startMarker' => 'test',
      ];
      $handler
        ->expects($this->exactly(2))
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
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

    public function testStreamOpenForWrite() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('openWriteFile')
        ->willReturn(TRUE);
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

    public function testStreamWrite() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $testContent = 'testContent';
      $handler
        ->expects($this->once())
        ->method('writeFileContent')
        ->with($this->anything(), $this->equalTo($testContent))
        ->willReturn(strlen($testContent));
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

    public function testStreamCloseReadable() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $fileInformation = [
        'size' => 23,
        'modified' => 1257167160,
        'mode' => 0100006
      ];
      $handler
        ->expects($this->once())
        ->method('readFileContent')
        ->willReturn(['', $fileInformation]);
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

    public function testUnlink() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getFileInformations')
        ->willReturn([]);
      $handler
        ->expects($this->once())
        ->method('removeFile')
        ->willReturn(TRUE);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->assertTrue(
        $wrapper->unlink(self::TEST_FILE)
      );
    }

    public function testUnlinkWithNonExistingFile() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getFileInformations')
        ->willReturn(NULL);
      $handler
        ->expects($this->never())
        ->method('removeFile');
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->expectException(S3Exception::class);
      $wrapper->unlink(self::TEST_FILE);
    }

    public function testUnlinkWithInvalidPath() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->never())
        ->method('removeFile');
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->expectException(S3Exception::class);
      $wrapper->unlink('INVALID');
    }

    public function testMakeDirectory() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getFileInformations')
        ->willReturn(NULL);
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
      $handler
        ->expects($this->exactly(2))
        ->method('openWriteFile')
        ->willReturn(TRUE);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->assertTrue(
        $wrapper->mkdir(self::TEST_FILE, 0, 0)
      );
    }

    public function testMakeDirectoryWithSlash() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getFileInformations')
        ->willReturn(NULL);
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
      $handler
        ->expects($this->exactly(2))
        ->method('openWriteFile')
        ->willReturn(TRUE);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $testFile =
        's3:KEYID123456789012345:1234567890123456789012345678901234567890'.
        '@bucketname/objectkey/';
      $this->assertTrue(
        $wrapper->mkdir($testFile, 0, 0)
      );
    }

    public function testMakeDirectoryWithFileExists() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getFileInformations')
        ->willReturn([]);
      $handler
        ->expects($this->never())
        ->method('openWriteFile');
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->expectException(S3Exception::class);
      $wrapper->mkdir(self::TEST_FILE, 0, 0);
    }

    public function testMakeDirectoryWithFailingOpen() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getFileInformations')
        ->willReturn(NULL);
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
      $handler
        ->expects($this->once())
        ->method('openWriteFile')
        ->willReturn(FALSE);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->assertFalse(
        $wrapper->mkdir(self::TEST_FILE, 0, 0)
      );
    }

    public function testMakeDirectoryWithFailing2ndOpen() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getFileInformations')
        ->willReturn(NULL);
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
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

    public function testMakeDirectoryExists() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn([]);
      $handler
        ->expects($this->never())
        ->method('openWriteFile');
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->expectException(S3Exception::class);
      $wrapper->mkdir(self::TEST_FILE, 0, 0);
    }

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
      $this->expectException(S3Exception::class);
      $wrapper->mkdir('INVALID', 0, 0);
    }

    public function testRemoveDirectory() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'moreContent' => FALSE,
        'contents' => ['$'],
      ];
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
      $handler
        ->expects($this->exactly(3))
        ->method('removeFile')
        ->willReturn(TRUE);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->assertTrue(
        $wrapper->rmdir(self::TEST_FILE, 0)
      );
    }

    public function testRemoveDirectoryWithSlash() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'moreContent' => FALSE,
        'contents' => ['$'],
      ];
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
      $handler
        ->expects($this->exactly(3))
        ->method('removeFile')
        ->willReturn(TRUE);
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->assertTrue(
        $wrapper->rmdir(self::TEST_FILE.'/', 0)
      );
    }

    public function testRemoveDirectoryWithContent() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'moreContent' => FALSE,
        'contents' => ['foo', 'bar'],
      ];
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
      $handler
        ->expects($this->never())
        ->method('removeFile');
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->expectException(S3Exception::class);
      $wrapper->rmdir(self::TEST_FILE, 0);
    }

    public function testRemoveDirectoryWithMoreContent() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $directoryInformation = [
        'moreContent' => TRUE,
        'contents' => ['$'],
      ];
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn($directoryInformation);
      $handler
        ->expects($this->never())
        ->method('removeFile');
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->expectException(S3Exception::class);
      $wrapper->rmdir(self::TEST_FILE, 0);
    }

    public function testRemoveDirectoryNotExisting() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|S3\Handler $handler */
      $handler = $this->createMock(S3\Handler::class);
      $handler
        ->expects($this->once())
        ->method('getDirectoryInformations')
        ->willReturn(NULL);
      $handler
        ->expects($this->never())
        ->method('removeFile');
      $wrapper = new S3();
      $wrapper->setHandler($handler);
      $this->expectException(S3Exception::class);
      $wrapper->rmdir(self::TEST_FILE, 0);
    }

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
      $this->expectException(S3Exception::class);
      $wrapper->rmdir('INVALID', 0);
    }

    /*********************************
     * Data Provider
     *********************************/

    public static function parsePathDataProvider() {
      return [
        [
          's3:KEYID123456789012345:1234567890123456789012345678901234567890@bucketname/objectkey',
          [
            'bucket' => 'bucketname',
            'id' => 'KEYID123456789012345',
            'secret' => '1234567890123456789012345678901234567890',
            'object' => 'objectkey'
          ]
        ],
        [
          'amazon://KEYID123456789012345:1234567890123456789012345678901234567890@bucketname/object',
          [
            'bucket' => 'bucketname',
            'id' => 'KEYID123456789012345',
            'secret' => '1234567890123456789012345678901234567890',
            'object' => 'object'
          ]
        ]
      ];
    }

    public static function streamOpenUnsupportedModeDataProvider() {
      return [
        ['r+'],
        ['w+'],
        ['a'],
        ['a+'],
        ['x'],
        ['x+'],
      ];
    }
  }
}
