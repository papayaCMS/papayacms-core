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

namespace Papaya\CSV {

  require_once __DIR__.'/../../../bootstrap.php';

  class ReaderTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\CSV\Reader::__construct
     */
    public function testConstructor() {
      $reader = new Reader('sample.csv');
      $this->assertSame(
        'sample.csv', $reader->getFileName()
      );
    }

    /**
     * @covers \Papaya\CSV\Reader::setMaximumFileSize
     */
    public function testSetMaximumFileSize() {
      $reader = new Reader('sample.csv');
      $reader->setMaximumFileSize(3);
      $this->assertSame(
        3, $reader->getMaximumFileSize()
      );
    }

    /**
     * @covers \Papaya\CSV\Reader::setMaximumLineSize
     */
    public function testSetMaximumLineSize() {
      $reader = new Reader('sample.csv');
      $reader->setMaximumLineSize(3);
      $this->assertSame(
        3, $reader->getMaximumLineSize()
      );
    }

    /**
     * @covers \Papaya\CSV\Reader::isValid
     */
    public function testIsValidExpectingTrue() {
      $reader = new Reader(__DIR__.'/TestData/sample.csv');
      $this->assertTrue($reader->isValid(TRUE));
    }

    /**
     * @covers \Papaya\CSV\Reader::isValid
     */
    public function testIsValidDisallowLocalFilesExpectingException() {
      $reader = new Reader(__DIR__.'/TestData/sample.csv');
      $this->expectException(\LogicException::class);
      $reader->isValid(FALSE);
    }

    /**
     * @covers \Papaya\CSV\Reader::isValid
     */
    public function testIsValidNonExistingFileExpectingException() {
      $reader = new Reader(__DIR__.'/TestData/INVALID_FILENAME.csv');
      $this->expectException(\UnexpectedValueException::class);
      $reader->isValid(FALSE);
    }

    /**
     * @covers \Papaya\CSV\Reader::isValid
     */
    public function testIsValidEmptyFileExpectingException() {
      $reader = new Reader(__DIR__.'/TestData/empty.csv');
      $this->expectException(\LengthException::class);
      $reader->isValid(TRUE);
    }

    /**
     * @covers \Papaya\CSV\Reader::isValid
     */
    public function testIsValidFileToLargeExpectingException() {
      $reader = new Reader(__DIR__.'/TestData/sample.csv');
      $reader->setMaximumFileSize(3);
      $this->expectException(\LengthException::class);
      $reader->isValid(TRUE);
    }

    /**
     * @covers \Papaya\CSV\Reader::fetchAssoc
     * @covers \Papaya\CSV\Reader::_getFileResource
     * @dataProvider provideDataForFetchAssoc
     * @param int $startOffset
     * @param int $limit
     * @param int $expectedOffset
     * @param string $expectedData
     */
    public function testFetchAssoc($startOffset, $limit, $expectedOffset, $expectedData) {
      $reader = new Reader(__DIR__.'/TestData/sample.csv');
      $offset = $startOffset;
      $data = $reader->fetchAssoc($offset, $limit);
      $this->assertEquals(
        $expectedData, $data
      );
      $this->assertEquals(
        $expectedOffset, $offset
      );
    }

    /**
     * @covers \Papaya\CSV\Reader::fetchAssoc
     */
    public function testFetchAssocWithInvalidFileExpectingNull() {
      $reader = new Reader_TestProxy('sample.csv');
      $offset = 0;
      $this->assertNull(
        $reader->fetchAssoc($offset)
      );
    }

    /**
     * @covers \Papaya\CSV\Reader::_getStyle
     * @covers \Papaya\CSV\Reader::_getFirstCharacter
     * @dataProvider provideDataForGetStyle
     * @param array $expected
     * @param string $csvData
     */
    public function testGetStyle(array $expected, $csvData) {
      $reader = new Reader_TestProxy('sample.csv');
      $this->assertEquals(
        $expected, $reader->_getStyle(fopen('data://text/plain,'.$csvData, 'rb'))
      );
    }

    /**
     * @covers \Papaya\CSV\Reader::_readLine
     * @dataProvider provideDataForReadLine
     * @param array $expected
     * @param string $csvData
     * @param string $separator
     * @param string $enclosure
     */
    public function testReadLine($expected, $csvData, $separator, $enclosure) {
      $reader = new Reader_TestProxy('sample.csv');
      $this->assertEquals(
        $expected,
        $reader->_readLine(
          fopen('data://text/plain,'.$csvData, 'rb'),
          $separator,
          $enclosure
        )
      );
    }

    /**********************************
     * Data Provider
     ***********************************/

    public static function provideDataForFetchAssoc() {
      return array(
        'all' => array(
          0,
          0,
          43,
          array(
            array('foo' => '1_1', 'bar' => '1_2'),
            array('foo' => '2_1', 'bar' => '2_2'),
            array('foo' => '3_1', 'bar' => '3_2')
          )
        ),
        'first record' => array(
          0,
          1,
          20,
          array(
            array('foo' => '1_1', 'bar' => '1_2')
          )
        ),
        'second record' => array(
          20,
          1,
          32,
          array(
            array('foo' => '2_1', 'bar' => '2_2')
          )
        ),
        'first two records' => array(
          0,
          2,
          32,
          array(
            array('foo' => '1_1', 'bar' => '1_2'),
            array('foo' => '2_1', 'bar' => '2_2')
          )
        )
      );
    }

    public static function provideDataForGetStyle() {
      return array(
        array(
          array('separator' => ',', 'enclosure' => '"'),
          "foo,bar\n1,2"
        ),
        array(
          array('separator' => ',', 'enclosure' => '"'),
          'foo,bar'."\n".'"1","2"'
        ),
        array(
          array('separator' => ';', 'enclosure' => "'"),
          "foo;bar\n'1';'2'"
        )
      );
    }

    public static function provideDataForReadLine() {
      return array(
        'one row of two' => array(
          array(
            array('row 1 value 1', 'row 1 value 2'),
            33
          ),
          '"row 1 value 1","row 1 value 2"'."\r\n".'"row 2 value 1","row 2 value 2"',
          ',',
          '"'
        ),
        'separator in value' => array(
          array(
            array('value 1', 'value 2 before delimiter , after', 'value 3'),
            54
          ),
          '"value 1","value 2 before delimiter , after","value 3"',
          ',',
          '"'
        ),
        'enclosure in value' => array(
          array(
            array('value 1', 'value 2 before enclosure " after', 'value 3'),
            55
          ),
          '"value 1","value 2 before enclosure "" after","value 3"',
          ',',
          '"'
        ),
        'lf in value' => array(
          array(
            array('value 1', "value 2 before newline \n after", 'value 3'),
            52
          ),
          '"value 1","value 2 before newline '."\n".' after","value 3"',
          ',',
          '"'
        ),
        'crlf in value' => array(
          array(
            array('value 1', "value 2 before newline \r\n after", 'value 3'),
            53
          ),
          '"value 1","value 2 before newline '."\r\n".' after","value 3"',
          ',',
          '"'
        ),
        'separator and lf in value' => array(
          array(
            array("before separator , after and before newline\n after"),
            52
          ),
          '"before separator , after and before newline'."\n".' after"',
          ',',
          '"'
        ),
        'no enclosure' => array(
          array(
            array('value_1', 'value_2'),
            15
          ),
          'value_1,value_2',
          ',',
          '"'
        ),
        'eof in value' => array(
          FALSE,
          '',
          ',',
          '"'
        ),
        'trailing lf' => array(
          array(array(), 1),
          "\n",
          ',',
          '"'
        ),
        'trailing crlf' => array(
          array(array(), 2),
          "\r\n",
          ',',
          '"'
        )
      );
    }
  }

  class Reader_TestProxy extends Reader {

    public function _getFileResource() {
      return NULL;
    }

    public function _getStyle($fh) {
      return parent::_getStyle($fh);
    }

    public function _readLine($fh, $delimiter, $enclosure) {
      return parent::_readLine($fh, $delimiter, $enclosure);
    }
  }
}
