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

class PapayaIteratorFileStreamTest extends PapayaTestCase {

  /**
  * @covers \PapayaIteratorFileStream::__construct
  * @covers \PapayaIteratorFileStream::setStream
  * @covers \PapayaIteratorFileStream::getStream
  */
  public function testConstructor() {
    $iterator = new \PapayaIteratorFileStream($this->getStreamFixture());
    $this->assertInternalType('resource', $iterator->getStream());
  }

  /**
  * @covers \PapayaIteratorFileStream::__construct
  * @covers \PapayaIteratorFileStream::setStream
  */
  public function testConstructorWithInvaloidStreamExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Provided file stream is invalid');
    new \PapayaIteratorFileStream(NULL);
  }

  /**
  * @covers \PapayaIteratorFileStream::__destruct
  */
  public function testDestructor() {
    $iterator = new \PapayaIteratorFileStream($this->getStreamFixture());
    $iterator->__destruct();
    $this->assertNull($iterator->getStream());
  }

  /**
  * @covers \PapayaIteratorFileStream::rewind
  * @covers \PapayaIteratorFileStream::next
  * @covers \PapayaIteratorFileStream::valid
  * @covers \PapayaIteratorFileStream::key
  * @covers \PapayaIteratorFileStream::current
  */
  public function testIteration() {
    $iterator = new \PapayaIteratorFileStream($this->getStreamFixture());
    $this->assertEquals(
      array("line1\n", "line2\n", 'line3'),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers \PapayaIteratorFileStream::rewind
  * @covers \PapayaIteratorFileStream::next
  * @covers \PapayaIteratorFileStream::valid
  * @covers \PapayaIteratorFileStream::key
  * @covers \PapayaIteratorFileStream::current
  */
  public function testIterationRemovingLineEnds() {
    $iterator = new \PapayaIteratorFileStream(
      $this->getStreamFixture(), \PapayaIteratorFileStream::TRIM_RIGHT
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
