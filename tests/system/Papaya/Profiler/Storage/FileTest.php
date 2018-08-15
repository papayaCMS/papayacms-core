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

class PapayaProfilerStorageFileTest extends \PapayaTestCase {

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  /**
  * @covers \Papaya\Profiler\Storage\File::__construct
  * @covers \Papaya\Profiler\Storage\File::prepareDirectory
  */
  public function testConstructor() {
    $storage = new \Papaya\Profiler\Storage\File(
      $this->createTemporaryDirectory()
    );
    $this->assertAttributeNotEquals('', '_directory', $storage);
  }

  /**
  * @covers \Papaya\Profiler\Storage\File::__construct
  * @covers \Papaya\Profiler\Storage\File::prepareSuffix
  */
  public function testConstructorWithAllParameters() {
    $storage = new \Papaya\Profiler\Storage\File(
      $this->createTemporaryDirectory(),
      'foo'
    );
    $this->assertAttributeEquals('foo', '_suffix', $storage);
  }

  /**
  * @covers \Papaya\Profiler\Storage\File::__construct
  * @covers \Papaya\Profiler\Storage\File::prepareSuffix
  */
  public function testConstructorWithInvalidSuffixExpectingException() {
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid profiling file suffix "-"');
    new \Papaya\Profiler\Storage\File(
      $this->createTemporaryDirectory(),
      '-'
    );
  }

  /**
  * @covers \Papaya\Profiler\Storage\File::saveRun
  * @covers \Papaya\Profiler\Storage\File::getId
  * @covers \Papaya\Profiler\Storage\File::getFilename
  * @covers \Papaya\Profiler\Storage\File::prepareDirectory
  */
  public function testSaveRun() {
    $storage = new \Papaya\Profiler\Storage\File(
      $directory = $this->createTemporaryDirectory().'/',
      'foo'
    );
    $id = $storage->saveRun(array(), 'sample');
    $expectedFile = $directory.$id.'.sample.foo';
    $this->assertFileExists($expectedFile);
  }

  /**
  * @covers \Papaya\Profiler\Storage\File::prepareDirectory
  */
  public function testSaveRunWithEmptyDirectoryExpectingException() {
    $storage = new \Papaya\Profiler\Storage\File('', 'foo');
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('No profiling directory defined.');
    $storage->saveRun(array(), 'sample');
  }

  /**
  * @covers \Papaya\Profiler\Storage\File::prepareDirectory
  */
  public function testSaveRunWithNonWriteableDirectoryExpectingException() {
    $storage = new \Papaya\Profiler\Storage\File('http://localhost/', 'foo');
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Profiling directory "/http:/localhost/" is not writeable.');
    $storage->saveRun(array(), 'sample');
  }
}
