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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFileSystemFileTest extends PapayaTestCase {

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  /**
   * @covers \PapayaFileSystemFile::__construct
   * @covers \PapayaFileSystemFile::__toString
   */
  public function testConstructor() {
    $file = new \PapayaFileSystemFile('/path/file.txt');
    $this->assertEquals(
      '/path/file.txt', (string)$file
    );
  }

  /**
   * @covers \PapayaFileSystemFile::exists
   */
  public function testExistsExpectingTrue() {
    $file = new \PapayaFileSystemFile(__DIR__.'/TestData/sample.txt');
    $this->assertTrue($file->exists());
  }

  /**
   * @covers \PapayaFileSystemFile::exists
   */
  public function testExistsExpectingFalse() {
    $file = new \PapayaFileSystemFile(__DIR__.'/TestData/NON_EXISTING.txt');
    $this->assertFalse($file->exists());
  }

  /**
   * @covers \PapayaFileSystemFile::isReadable
   */
  public function testIsReadableExpectingTrue() {
    $file = new \PapayaFileSystemFile(__DIR__.'/TestData/sample.txt');
    $this->assertTrue($file->isReadable());
  }

  /**
   * @covers \PapayaFileSystemFile::isWriteable
   */
  public function testIsWriteableExpectingTrue() {
    $filename = $this->createTemporaryDirectory().'/sample.txt';
    touch($filename);
    $file = new \PapayaFileSystemFile($filename);
    $this->assertTrue($file->isWriteable());
  }

  /**
   * @covers \PapayaFileSystemFile::getContents
   */
  public function testGetContents() {
    $file = new \PapayaFileSystemFile(__DIR__.'/TestData/sample.txt');
    $this->assertEquals('success', $file->getContents());
  }

  /**
   * @covers \PapayaFileSystemFile::putContents
   */
  public function testPutContents() {
    $filename = $this->createTemporaryDirectory().'/sample.txt';
    $file = new \PapayaFileSystemFile($filename);
    $file->putContents('success');
    $this->assertEquals('success', file_get_contents($filename));
  }
}
