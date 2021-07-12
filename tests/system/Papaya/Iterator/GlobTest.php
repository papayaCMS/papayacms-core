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

namespace Papaya\Iterator;
require_once __DIR__.'/../../../bootstrap.php';

class GlobTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Iterator\Glob::__construct
   */
  public function testConstructor() {
    $glob = new Glob(__DIR__.'/TestDataGlob/*.*');
    $this->assertStringEndsWith(
      '/TestDataGlob/*.*', $glob->getPath()
    );
  }

  /**
   * @covers \Papaya\Iterator\Glob::__construct
   * @covers \Papaya\Iterator\Glob::setFlags
   * @covers \Papaya\Iterator\Glob::getFlags
   */
  public function testConstructorWithFlags() {
    $glob = new Glob(__DIR__.'/TestDataGlob/*.*', GLOB_NOSORT);
    $this->assertEquals(
      GLOB_NOSORT, $glob->getFlags()
    );
  }

  /**
   * @covers \Papaya\Iterator\Glob::getFilesLazy
   * @covers \Papaya\Iterator\Glob::getIterator
   */
  public function testGetIterator() {
    $glob = new Glob(__DIR__.'/TestDataGlob/*.*');
    $files = iterator_to_array($glob);
    $this->assertStringEndsWith(
      '/TestDataGlob/sampleOne.txt', $files[0]
    );
    $this->assertStringEndsWith(
      '/TestDataGlob/sampleTwo.txt', $files[1]
    );
  }


  /**
   * @covers \Papaya\Iterator\Glob::getFilesLazy
   * @covers \Papaya\Iterator\Glob::getIterator
   */
  public function testGetIteratorInvalidDirectory() {
    $glob = new Glob(__DIR__.'/TestDataGlob/INVALID_DIRECTORY/*.*');
    $this->assertEquals(
      array(), iterator_to_array($glob)
    );
  }

  /**
   * @covers \Papaya\Iterator\Glob::getFilesLazy
   * @covers \Papaya\Iterator\Glob::count
   */
  public function testCount() {
    $glob = new Glob(__DIR__.'/TestDataGlob/*.*');
    $this->assertCount(2, $glob);
  }
}
