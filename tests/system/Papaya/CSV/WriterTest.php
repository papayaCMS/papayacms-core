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

namespace Papaya\CSV;

require_once __DIR__.'/../../../bootstrap.php';

class WriterTest extends \Papaya\TestCase {

  /**
   * @covers Writer::__construct
   */
  public function testConstructor() {
    $writer = new Writer();
    $this->assertNull($writer->stream);
  }

  /**
   * @covers Writer::__construct
   */
  public function testConstructorWithStream() {
    $writer = new Writer($ms = fopen('php://memory', 'rwb'));
    $this->assertSame($ms, $writer->stream);
  }

  /**
   * @covers Writer::__get
   * @covers Writer::__set
   */
  public function testStreamGetAfterSet() {
    $writer = new Writer();
    $writer->stream = $ms = fopen('php://memory', 'rwb');
    $this->assertSame($ms, $writer->stream);
  }

  /**
   * @covers Writer::__get
   * @covers Writer::__set
   */
  public function testSeparatorGetAfterSet() {
    $writer = new Writer();
    $writer->separator = '; ';
    $this->assertEquals('; ', $writer->separator);
    $this->assertEquals(2, $writer->separatorLength);
  }

  /**
   * @covers Writer::__get
   * @covers Writer::__set
   */
  public function testSeparatorLengthExpectingException() {
    $writer = new Writer();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Can not write read only property "separatorLength".');
    /** @noinspection Annotator */
    $writer->separatorLength = 23;
  }

  /**
   * @covers Writer::__get
   * @covers Writer::__set
   */
  public function testQuoteGetAfterSet() {
    $writer = new Writer();
    $writer->quote = "'";
    $this->assertEquals("'", $writer->quote);
  }

  /**
   * @covers Writer::__get
   * @covers Writer::__set
   */
  public function testLinebreakGetAfterSet() {
    $writer = new Writer();
    $writer->linebreak = "\r\n";
    $this->assertEquals("\r\n", $writer->linebreak);
  }

  /**
   * @covers Writer::__get
   * @covers Writer::__set
   */
  public function testEncodedLinebreakGetAfterSet() {
    $writer = new Writer();
    $writer->encodedLinebreak = '\\r\\n';
    $this->assertEquals('\\r\\n', $writer->encodedLinebreak);
  }

  /**
   * @covers Writer::__set
   */
  public function testSetPropertyWithInvalidNameExpectingException() {
    $writer = new Writer();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Can not write undefined property "invalidPropertyName".');
    /** @noinspection PhpUndefinedFieldInspection */
    $writer->invalidPropertyName = ' ';
  }

  /**
   * @covers Writer::__get
   */
  public function testGetPropertyWithInvalidNameExpectingException() {
    $writer = new Writer();
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Can not read undefined property "invalidPropertyName".');
    /** @noinspection PhpUndefinedFieldInspection */
    $writer->invalidPropertyName;
  }

  /**
   * @covers Writer::writeHeader
   */
  public function testWriteHeader() {
    $writer = new Writer();
    ob_start();
    $writer->writeRow(array('columnOne', 'columnTwo'));
    $this->assertEquals(
      'columnOne,columnTwo'."\n",
      ob_get_clean()
    );
  }

  /**
   * @covers Writer::writeHeader
   */
  public function testWriteHeaderCallsCallback() {
    $callbacks = $this
      ->getMockBuilder(Writer\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('__isset', 'onMapHeader'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('__isset')
      ->withAnyParameters()
      ->will($this->returnValue(TRUE));
    $callbacks
      ->expects($this->once())
      ->method('onMapHeader')
      ->with(array('columnOne', 'columnTwo'))
      ->will($this->returnValue(array('columnTwo')));

    $writer = new Writer();
    $writer->callbacks($callbacks);
    ob_start();
    $writer->writeHeader(array('columnOne', 'columnTwo'));
    $this->assertEquals(
      "columnTwo\n",
      ob_get_clean()
    );
  }

  /**
   * @covers       Writer::writeRow
   * @covers       Writer::quoteValue
   * @covers       Writer::write
   * @covers       Writer::writeString
   * @dataProvider provideSampleRowsAndExpectedOutput
   * @param $expected
   * @param $row
   */
  public function testWriteRow($expected, $row) {
    $writer = new Writer();
    ob_start();
    $writer->writeRow($row);
    $this->assertEquals(
      $expected,
      ob_get_clean()
    );
  }

  /**
   * @covers Writer::writeRow
   */
  public function testWriteRowCallsCallback() {
    $callbacks = $this
      ->getMockBuilder(Writer\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('__isset', 'onMapRow'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('__isset')
      ->withAnyParameters()
      ->will($this->returnValue(TRUE));
    $callbacks
      ->expects($this->once())
      ->method('onMapRow')
      ->with(array('one', 'two'))
      ->will($this->returnValue(array('three')));

    $writer = new Writer();
    $writer->callbacks($callbacks);
    ob_start();
    $writer->writeRow(array('one', 'two'));
    $this->assertEquals(
      "three\n",
      ob_get_clean()
    );
  }

  /**
   * @covers Writer::writeList
   * @covers Writer::writeString
   */
  public function testWriteList() {
    $list = array(
      array('one', 'two', 'three'),
      array('four', 'five', 'six')
    );
    $writer = new Writer();
    ob_start();
    $writer->writeList($list);
    $this->assertEquals(
      "one,two,three\nfour,five,six\n",
      ob_get_clean()
    );
  }

  /**
   * @covers Writer::writeList
   */
  public function testWriteListWithTraversable() {
    $list = new \ArrayIterator(
      array(
        array('one', 'two', 'three'),
        array('four', 'five', 'six')
      )
    );
    $writer = new Writer();
    ob_start();
    $writer->writeList($list);
    $this->assertEquals(
      "one,two,three\nfour,five,six\n",
      ob_get_clean()
    );
  }

  /**
   * @covers Writer::writeList
   * @covers Writer::writeString
   */
  public function testWriteListToStream() {
    $list = array(
      array('one', 'two', 'three'),
      array('four', 'five', 'six')
    );
    $ms = fopen('php://memory', 'rwb');
    $writer = new Writer($ms);
    $writer->writeList($list);
    fseek($ms, 0);
    $this->assertEquals(
      "one,two,three\nfour,five,six\n",
      fread($ms, 2048)
    );
  }

  /**
   * @covers Writer::callbacks
   */
  public function testCallbacksGetAfterSet() {
    $writer = new Writer();
    $writer->callbacks($callbacks = $this->createMock(Writer\Callbacks::class));
    $this->assertSame($callbacks, $writer->callbacks());
  }

  /**
   * @covers Writer::callbacks
   */
  public function testCallbacksGetWithImplizitCreate() {
    $writer = new Writer();
    $this->assertInstanceOf(Writer\Callbacks::class, $writer->callbacks());
  }

  public static function provideSampleRowsAndExpectedOutput() {
    return array(
      'one element' => array('one'."\n", array('one')),
      'two elements' => array('one,two'."\n", array('one', 'two')),
      'separator in value' => array('"one,two"'."\n", array('one,two')),
      'quote in value' => array('"one""two"'."\n", array('one"two')),
    );
  }
}
