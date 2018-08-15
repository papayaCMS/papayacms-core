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

namespace Papaya\File\System;

require_once __DIR__.'/../../../../bootstrap.php';

class FileTest extends \PapayaTestCase {

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  /**
   * @covers \Papaya\File\System\File::__construct
   * @covers \Papaya\File\System\File::__toString
   */
  public function testConstructor() {
    $file = new File('/path/file.txt');
    $this->assertEquals(
      '/path/file.txt', (string)$file
    );
  }

  /**
   * @covers \Papaya\File\System\File::exists
   */
  public function testExistsExpectingTrue() {
    $file = new File(__DIR__.'/TestData/sample.txt');
    $this->assertTrue($file->exists());
  }

  /**
   * @covers \Papaya\File\System\File::exists
   */
  public function testExistsExpectingFalse() {
    $file = new File(__DIR__.'/TestData/NON_EXISTING.txt');
    $this->assertFalse($file->exists());
  }

  /**
   * @covers \Papaya\File\System\File::isReadable
   */
  public function testIsReadableExpectingTrue() {
    $file = new File(__DIR__.'/TestData/sample.txt');
    $this->assertTrue($file->isReadable());
  }

  /**
   * @covers \Papaya\File\System\File::isWriteable
   */
  public function testIsWriteableExpectingTrue() {
    $filename = $this->createTemporaryDirectory().'/sample.txt';
    touch($filename);
    $file = new File($filename);
    $this->assertTrue($file->isWriteable());
  }

  /**
   * @covers \Papaya\File\System\File::getContents
   */
  public function testGetContents() {
    $file = new File(__DIR__.'/TestData/sample.txt');
    $this->assertEquals('success', $file->getContents());
  }

  /**
   * @covers \Papaya\File\System\File::putContents
   */
  public function testPutContents() {
    $filename = $this->createTemporaryDirectory().'/sample.txt';
    $file = new File($filename);
    $file->putContents('success');
    $this->assertEquals('success', file_get_contents($filename));
  }
}
