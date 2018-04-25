<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaCsvWriterTest extends PapayaTestCase {

  /**
  * @covers PapayaCsvWriter::__construct
  */
  public function testConstructor() {
    $writer = new PapayaCsvWriter();
    $this->assertNull($writer->stream);
  }

  /**
  * @covers PapayaCsvWriter::__construct
  */
  public function testConstructorWithStream() {
    $writer = new PapayaCsvWriter($ms = fopen('php://memory', 'rw'));
    $this->assertSame($ms, $writer->stream);
  }

  /**
  * @covers PapayaCsvWriter::__get
  * @covers PapayaCsvWriter::__set
  */
  public function testStreamGetAfterSet() {
    $writer = new PapayaCsvWriter();
    $writer->stream = $ms = fopen('php://memory', 'rw');
    $this->assertSame($ms, $writer->stream);
  }

  /**
  * @covers PapayaCsvWriter::__get
  * @covers PapayaCsvWriter::__set
  */
  public function testSeparatorGetAfterSet() {
    $writer = new PapayaCsvWriter();
    $writer->separator = '; ';
    $this->assertEquals('; ', $writer->separator);
    $this->assertEquals(2, $writer->separatorLength);
  }

  /**
  * @covers PapayaCsvWriter::__get
  * @covers PapayaCsvWriter::__set
  */
  public function testSeparatorLengthExpectingException() {
    $writer = new PapayaCsvWriter();
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Can not write read only property "separatorLength".');
    $writer->separatorLength = 23;
  }

  /**
  * @covers PapayaCsvWriter::__get
  * @covers PapayaCsvWriter::__set
  */
  public function testQuoteGetAfterSet() {
    $writer = new PapayaCsvWriter();
    $writer->quote = "'";
    $this->assertEquals("'", $writer->quote);
  }

  /**
  * @covers PapayaCsvWriter::__get
  * @covers PapayaCsvWriter::__set
  */
  public function testLinebreakGetAfterSet() {
    $writer = new PapayaCsvWriter();
    $writer->linebreak = "\r\n";
    $this->assertEquals("\r\n", $writer->linebreak);
  }

  /**
  * @covers PapayaCsvWriter::__get
  * @covers PapayaCsvWriter::__set
  */
  public function testEncodedLinebreakGetAfterSet() {
    $writer = new PapayaCsvWriter();
    $writer->encodedLinebreak = '\\r\\n';
    $this->assertEquals('\\r\\n', $writer->encodedLinebreak);
  }

  /**
  * @covers PapayaCsvWriter::__set
  */
  public function testSetPropertyWithInvalidNameExpectingException() {
    $writer = new PapayaCsvWriter();
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Can not write undefined property "invalidPropertyName".');
    $writer->invalidPropertyName = ' ';
  }

  /**
  * @covers PapayaCsvWriter::__get
  */
  public function testGetPropertyWithInvalidNameExpectingException() {
    $writer = new PapayaCsvWriter();
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Can not read undefined property "invalidPropertyName".');
    $dummy = $writer->invalidPropertyName;
  }

  /**
  * @covers PapayaCsvWriter::writeHeader
  */
  public function testWriteHeader() {
    $writer = new PapayaCsvWriter();
    ob_start();
    $writer->writeRow(array('columnOne', 'columnTwo'));
    $this->assertEquals(
      'columnOne,columnTwo'."\n",
      ob_get_clean()
    );
  }

  /**
  * @covers PapayaCsvWriter::writeHeader
  */
  public function testWriteHeaderCallsCallback() {
    $callbacks = $this
      ->getMockBuilder(PapayaCsvWriterCallbacks::class)
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

    $writer = new PapayaCsvWriter();
    $writer->callbacks($callbacks);
    ob_start();
    $writer->writeHeader(array('columnOne', 'columnTwo'));
    $this->assertEquals(
      "columnTwo\n",
      ob_get_clean()
    );
  }

  /**
  * @covers PapayaCsvWriter::writeRow
  * @covers PapayaCsvWriter::quoteValue
  * @covers PapayaCsvWriter::write
  * @covers PapayaCsvWriter::writeString
  * @dataProvider provideSampleRowsAndExpectedOutput
  */
  public function testWriteRow($expected, $row) {
    $writer = new PapayaCsvWriter();
    ob_start();
    $writer->writeRow($row);
    $this->assertEquals(
      $expected,
      ob_get_clean()
    );
  }

  /**
  * @covers PapayaCsvWriter::writeRow
  */
  public function testWriteRowCallsCallback() {
    $callbacks = $this
      ->getMockBuilder(PapayaCsvWriterCallbacks::class)
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

    $writer = new PapayaCsvWriter();
    $writer->callbacks($callbacks);
    ob_start();
    $writer->writeRow(array('one', 'two'));
    $this->assertEquals(
      "three\n",
      ob_get_clean()
    );
  }

  /**
  * @covers PapayaCsvWriter::writeList
  * @covers PapayaCsvWriter::writeString
  */
  public function testWriteList() {
    $list = array(
      array('one', 'two', 'three'),
      array('four', 'five', 'six')
    );
    $writer = new PapayaCsvWriter();
    ob_start();
    $writer->writeList($list);
    $this->assertEquals(
      "one,two,three\nfour,five,six\n",
      ob_get_clean()
    );
  }

  /**
  * @covers PapayaCsvWriter::writeList
  */
  public function testWriteListWithTraversable() {
    $list = new ArrayIterator(
      array(
        array('one', 'two', 'three'),
        array('four', 'five', 'six')
      )
    );
    $writer = new PapayaCsvWriter();
    ob_start();
    $writer->writeList($list);
    $this->assertEquals(
      "one,two,three\nfour,five,six\n",
      ob_get_clean()
    );
  }

  /**
  * @covers PapayaCsvWriter::writeList
  * @covers PapayaCsvWriter::writeString
  */
  public function testWriteListToStream() {
    $list = array(
      array('one', 'two', 'three'),
      array('four', 'five', 'six')
    );
    $ms = fopen('php://memory', 'rw');
    $writer = new PapayaCsvWriter($ms);
    $writer->writeList($list);
    fseek($ms, 0);
    $this->assertEquals(
      "one,two,three\nfour,five,six\n",
      fread($ms, 2048)
    );
  }

  /**
  * @covers PapayaCsvWriter::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $writer = new PapayaCsvWriter();
    $writer->callbacks($callbacks = $this->createMock(PapayaCsvWriterCallbacks::class));
    $this->assertSame($callbacks, $writer->callbacks());
  }

  /**
  * @covers PapayaCsvWriter::callbacks
  */
  public function testCallbacksGetWithImplizitCreate() {
    $writer = new PapayaCsvWriter();
    $this->assertInstanceOf(PapayaCsvWriterCallbacks::class, $writer->callbacks());
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
