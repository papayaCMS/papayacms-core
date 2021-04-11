<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace system\Papaya\Response\Content {

  use Papaya\Response\Content\JSON;
  use Papaya\TestCase;

  /**
   * @covers \Papaya\Response\Content\JSON
   */
  class JSONTest extends TestCase {

    /**
     * @param int $expectedLength
     * @param mixed $data
     * @testWith
     *   [4, null]
     *   [2, ""]
     *   [10, {"foo": 42}]
     *   [10, ["foo", 42]]
     */
    public function testLength($expectedLength, $data) {
      $content = new JSON($data);
      $this->assertSame($expectedLength, $content->length());
    }

    /**
     * @param string $expected
     * @param mixed $data
     * @testWith
     *   ["null", null]
     *   ["\"\"", ""]
     *   ["{\"foo\":42}", {"foo": 42}]
     *   ["[\"foo\",42]", ["foo", 42]]
     */
    public function testOutput($expected, $data) {
      $content = new JSON($data);
      $this->expectOutputString($expected);
      $content->output();
    }

    /**
     * @param string $expected
     * @param mixed $data
     * @testWith
     *   ["null", null]
     *   ["\"\"", ""]
     *   ["{\"foo\":42}", {"foo": 42}]
     *   ["[\"foo\",42]", ["foo", 42]]
     */
    public function testToString($expected, $data) {
      $content = new JSON($data);
      $this->assertSame($expected, (string)$content);
    }
  }

}
