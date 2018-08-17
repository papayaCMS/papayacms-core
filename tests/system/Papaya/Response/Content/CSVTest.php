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

namespace Papaya\Response\Content;
require_once __DIR__.'/../../../../bootstrap.php';

class CSVTest extends \Papaya\TestCase {


  /**
   * @covers \Papaya\Response\Content\CSV::length
   */
  public function testLength() {
    $content = new CSV(new \EmptyIterator(), []);
    $this->assertEquals(-1, $content->length());
  }

  /**
   * @covers \Papaya\Response\Content\File::output
   */
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

  /**
   * @covers \Papaya\Response\Content\File::output
   */
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

  /**
   * @covers \Papaya\Response\Content\File::output
   */
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

  /**
   * @covers \Papaya\Response\Content\File::output
   */
  public function testOutputMappingRowAndField() {
    $content = new CSV(
      new \ArrayIterator([1, 2])
    );
    $content->callbacks()->onMapRow = function ($original) {
      $data = [
        1 => ['one', $original],
        2 => ['two', $original]
      ];
      return $data[$original];
    };
    $content->callbacks()->onMapField = function ($original) {
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
