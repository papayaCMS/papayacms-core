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

  use Papaya\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\CSV\Writer
   */
  class WriterTest extends TestCase {

    public function testConstructor() {
      $writer = new Writer();
      $this->assertNull($writer->stream);
    }

    public function testConstructorWithStream() {
      $writer = new Writer($ms = fopen('php://memory', 'rwb'));
      $this->assertSame($ms, $writer->stream);
    }

    public function testConstructorWithStreamWritesBOM() {
      $writer = new Writer($ms = fopen('php://memory', 'rwb'), TRUE);
      $this->assertSame($ms, $writer->stream);
      $this->assertSame(3, fstat($writer->stream)['size']);
    }

    public function testConstructorWithoutStreamOutputsBOM() {
      $this->expectOutputString("\xEF\xBB\xBF");
      new Writer(NULL, TRUE);
    }

    public function testStreamGetAfterSet() {
      $writer = new Writer();
      $writer->stream = $ms = fopen('php://memory', 'rwb');
      $this->assertTrue(isset($writer->stream));
      $this->assertSame($ms, $writer->stream);
    }

    public function testSeparatorGetAfterSet() {
      $writer = new Writer();
      $writer->separator = '; ';
      $this->assertTrue(isset($writer->separator));
      $this->assertTrue(isset($writer->separatorLength));
      $this->assertEquals('; ', $writer->separator);
      $this->assertEquals(2, $writer->separatorLength);
    }

    public function testSeparatorLengthExpectingException() {
      $writer = new Writer();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Can not write read only property "separatorLength".');
      /** @noinspection Annotator */
      $writer->separatorLength = 23;
    }

    public function testQuoteGetAfterSet() {
      $writer = new Writer();
      $writer->quote = "'";
      $this->assertTrue(isset($writer->quote));
      $this->assertEquals("'", $writer->quote);
    }

    public function testLinebreakGetAfterSet() {
      $writer = new Writer();
      $writer->linebreak = "\r\n";
      $this->assertTrue(isset($writer->linebreak));
      $this->assertEquals("\r\n", $writer->linebreak);
    }

    public function testEncodedLinebreakGetAfterSet() {
      $writer = new Writer();
      $writer->encodedLinebreak = '\\r\\n';
      $this->assertTrue(isset($writer->encodedLinebreak));
      $this->assertEquals('\\r\\n', $writer->encodedLinebreak);
    }

    public function testSetPropertyWithInvalidNameExpectingException() {
      $writer = new Writer();
      $this->assertFalse(isset($writer->invalidPropertyName));
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Can not write undefined property "invalidPropertyName".');
      /** @noinspection PhpUndefinedFieldInspection */
      $writer->invalidPropertyName = ' ';
    }

    public function testGetPropertyWithInvalidNameExpectingException() {
      $writer = new Writer();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Can not read undefined property "invalidPropertyName".');
      /** @noinspection PhpUndefinedFieldInspection */
      $writer->invalidPropertyName;
    }

    public function testUnsetPropertyExpectingException() {
      $writer = new Writer();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Can not unset property "linebreak".');
      unset($writer->linebreak);
    }

    public function testWriteHeader() {
      $writer = new Writer();
      ob_start();
      $writer->writeRow(['columnOne', 'columnTwo']);
      $this->assertEquals(
        'columnOne,columnTwo'."\n",
        ob_get_clean()
      );
    }

    public function testWriteHeaderCallsCallback() {
      $callbacks = $this
        ->getMockBuilder(Writer\Callbacks::class)
        ->disableOriginalConstructor()
        ->setMethods(['__isset', 'onMapHeader'])
        ->getMock();
      $callbacks
        ->expects($this->once())
        ->method('__isset')
        ->withAnyParameters()
        ->willReturn(TRUE);
      $callbacks
        ->expects($this->once())
        ->method('onMapHeader')
        ->with(['columnOne', 'columnTwo'])
        ->willReturn(['columnTwo']);

      $writer = new Writer();
      $writer->callbacks($callbacks);
      ob_start();
      $writer->writeHeader(['columnOne', 'columnTwo']);
      $this->assertEquals(
        "columnTwo\n",
        ob_get_clean()
      );
    }

    /**
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

    public function testWriteRowCallsCallback() {
      $callbacks = $this
        ->getMockBuilder(Writer\Callbacks::class)
        ->disableOriginalConstructor()
        ->setMethods(['__isset', 'onMapRow'])
        ->getMock();
      $callbacks
        ->expects($this->once())
        ->method('__isset')
        ->withAnyParameters()
        ->willReturn(TRUE);
      $callbacks
        ->expects($this->once())
        ->method('onMapRow')
        ->with(['one', 'two'])
        ->willReturn(['three']);

      $writer = new Writer();
      $writer->callbacks($callbacks);
      ob_start();
      $writer->writeRow(['one', 'two']);
      $this->assertEquals(
        "three\n",
        ob_get_clean()
      );
    }

    public function testWriteList() {
      $list = [
        ['one', 'two', 'three'],
        ['four', 'five', 'six']
      ];
      $writer = new Writer();
      ob_start();
      $writer->writeList($list);
      $this->assertEquals(
        "one,two,three\nfour,five,six\n",
        ob_get_clean()
      );
    }

    public function testWriteListWithTraversable() {
      $list = new \ArrayIterator(
        [
          ['one', 'two', 'three'],
          ['four', 'five', 'six']
        ]
      );
      $writer = new Writer();
      ob_start();
      $writer->writeList($list);
      $this->assertEquals(
        "one,two,three\nfour,five,six\n",
        ob_get_clean()
      );
    }

    public function testWriteListToStream() {
      $list = [
        ['one', 'two', 'three'],
        ['four', 'five', 'six']
      ];
      $ms = fopen('php://memory', 'rwb');
      $writer = new Writer($ms);
      $writer->writeList($list);
      fseek($ms, 0);
      $this->assertEquals(
        "one,two,three\nfour,five,six\n",
        fread($ms, 2048)
      );
    }

    public function testCallbacksGetAfterSet() {
      $writer = new Writer();
      $writer->callbacks($callbacks = $this->createMock(Writer\Callbacks::class));
      $this->assertSame($callbacks, $writer->callbacks());
    }

    public function testCallbacksGetWithImplizitCreate() {
      $writer = new Writer();
      $this->assertInstanceOf(Writer\Callbacks::class, $writer->callbacks());
    }

    public static function provideSampleRowsAndExpectedOutput() {
      return [
        'one element' => ['one'."\n", ['one']],
        'two elements' => ['one,two'."\n", ['one', 'two']],
        'separator in value' => ['"one,two"'."\n", ['one,two']],
        'quote in value' => ['"one""two"'."\n", ['one"two']],
      ];
    }
  }
}
