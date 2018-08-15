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

class PapayaIteratorFileStreamTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Iterator\File\Stream::__construct
  * @covers \Papaya\Iterator\File\Stream::setStream
  * @covers \Papaya\Iterator\File\Stream::getStream
  */
  public function testConstructor() {
    $iterator = new \Papaya\Iterator\File\Stream($this->getStreamFixture());
    $this->assertInternalType('resource', $iterator->getStream());
  }

  /**
  * @covers \Papaya\Iterator\File\Stream::__construct
  * @covers \Papaya\Iterator\File\Stream::setStream
  */
  public function testConstructorWithInvaloidStreamExpectingException() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Provided file stream is invalid');
    new \Papaya\Iterator\File\Stream(NULL);
  }

  /**
  * @covers \Papaya\Iterator\File\Stream::__destruct
  */
  public function testDestructor() {
    $iterator = new \Papaya\Iterator\File\Stream($this->getStreamFixture());
    $iterator->__destruct();
    $this->assertNull($iterator->getStream());
  }

  /**
  * @covers \Papaya\Iterator\File\Stream::rewind
  * @covers \Papaya\Iterator\File\Stream::next
  * @covers \Papaya\Iterator\File\Stream::valid
  * @covers \Papaya\Iterator\File\Stream::key
  * @covers \Papaya\Iterator\File\Stream::current
  */
  public function testIteration() {
    $iterator = new \Papaya\Iterator\File\Stream($this->getStreamFixture());
    $this->assertEquals(
      array("line1\n", "line2\n", 'line3'),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers \Papaya\Iterator\File\Stream::rewind
  * @covers \Papaya\Iterator\File\Stream::next
  * @covers \Papaya\Iterator\File\Stream::valid
  * @covers \Papaya\Iterator\File\Stream::key
  * @covers \Papaya\Iterator\File\Stream::current
  */
  public function testIterationRemovingLineEnds() {
    $iterator = new \Papaya\Iterator\File\Stream(
      $this->getStreamFixture(), \Papaya\Iterator\File\Stream::TRIM_RIGHT
    );
    $this->assertEquals(
      array('line1', 'line2', 'line3'),
      iterator_to_array($iterator)
    );
  }

  public function getStreamFixture() {
    return fopen(
      "data:text/plain,line1\nline2\nline3",
      'rb'
    );
  }
}
