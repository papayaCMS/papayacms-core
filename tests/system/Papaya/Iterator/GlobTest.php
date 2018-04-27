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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaIteratorGlobTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorGlob::__construct
  */
  public function testConstructor() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*');
    $this->assertStringEndsWith(
      '/TestDataGlob/*.*', $this->readAttribute($glob, '_path')
    );
  }

  /**
  * @covers PapayaIteratorGlob::__construct
  * @covers PapayaIteratorGlob::setFlags
  * @covers PapayaIteratorGlob::getFlags
  */
  public function testConstructorWithFlags() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*', GLOB_NOSORT);
    $this->assertEquals(
      GLOB_NOSORT, $glob->getFlags()
    );
  }

  /**
  * @covers PapayaIteratorGlob::rewind
  */
  public function testRewind() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*');
    iterator_to_array($glob);
    $glob->rewind();
    $this->assertAttributeSame(
      NULL, '_files', $glob
    );
  }

  /**
  * @covers PapayaIteratorGlob::getFilesLazy
  * @covers PapayaIteratorGlob::getIterator
  */
  public function testGetIterator() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*');
    $files = iterator_to_array($glob);
    $this->assertStringEndsWith(
      '/TestDataGlob/sampleOne.txt', $files[0]
    );
    $this->assertStringEndsWith(
      '/TestDataGlob/sampleTwo.txt', $files[1]
    );
  }


  /**
  * @covers PapayaIteratorGlob::getFilesLazy
  * @covers PapayaIteratorGlob::getIterator
  */
  public function testGetIteratorInvalidDirectory() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/INVALID_DIRECTORY/*.*');
    $this->assertEquals(
      array(), iterator_to_array($glob)
    );
  }

  /**
  * @covers PapayaIteratorGlob::getFilesLazy
  * @covers PapayaIteratorGlob::count
  */
  public function testCount() {
    $glob = new PapayaIteratorGlob(__DIR__.'/TestDataGlob/*.*');
    $this->assertCount(2, $glob);
  }
}
