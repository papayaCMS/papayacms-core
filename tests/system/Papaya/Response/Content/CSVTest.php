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

namespace Papaya\Response\Content {

  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Response\Content\CSV
   * @covers \Papaya\Response\Content\CSV\Callbacks
   */
  class CSVTest extends TestCase {

    public function testCallbacksGetAfterSet() {
      $callbacks = $this->createMock(CSV\Callbacks::class);
      $content = new CSV(new \EmptyIterator(), []);
      $this->assertSame($callbacks, $content->callbacks($callbacks));
    }

    public function testLength() {
      $content = new CSV(new \EmptyIterator(), []);
      $this->assertEquals(-1, $content->length());
    }

    public function testOutputUsingNumericColumnIndex() {
      $content = new CSV(
        new \ArrayIterator(
          [
            ['1', '2'],
            ['3', '4']
          ]
        ),
        ['one', 'two']
      );
      ob_start();
      $content->output();
      $this->assertEquals(
        "one,two\r\n1,2\r\n3,4\r\n",
        ob_get_clean()
      );
    }

    public function testOutputUsingNamedColumnIndex() {
      $content = new CSV(
        new \ArrayIterator(
          [
            ['one' => 'first value', 'two' => 'second value'],
            ['two' => 4, 'one' => 3],
            ['two' => 5],
            ['one' => 6]
          ]
        ),
        ['one' => 'First Column', 'two' => 'Second']
      );
      ob_start();
      $content->output();
      $this->assertEquals(
        "First Column,Second\r\nfirst value,second value\r\n3,4\r\n,5\r\n6,\r\n",
        ob_get_clean()
      );
    }

    public function testOutputWithoutColumns() {
      $content = new CSV(
        new \ArrayIterator(
          [
            ['1', '2'],
            ['3', '4']
          ]
        )
      );
      ob_start();
      $content->output();
      $this->assertEquals(
        "1,2\r\n3,4\r\n",
        ob_get_clean()
      );
    }

    public function testOutputWithoutColumnsEscapingValues() {
      $content = new CSV(
        new \ArrayIterator(
          [
            ["foo\nbar", '"foo"'],
            ['3', '4']
          ]
        )
      );
      ob_start();
      $content->output();
      $this->assertEquals(
        "\"foo\\nbar\",\"\"\"foo\"\"\"\r\n3,4\r\n",
        ob_get_clean()
      );
    }

    public function testOutputMappingRowAndField() {
      $content = new CSV(
        new \ArrayIterator([1, 2])
      );
      $content->callbacks()->onMapRow = static function($original) {
        $data = [
          1 => ['one', $original],
          2 => ['two', $original]
        ];
        return $data[$original];
      };
      $content->callbacks()->onMapField = static function($original) {
        return strtoupper($original);
      };
      ob_start();
      $content->output();
      $this->assertEquals(
        "ONE,1\r\nTWO,2\r\n",
        ob_get_clean()
      );
    }
  }
}
