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

class PapayaUtilStringJavascriptTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Utility\Text\Javascript::quote
   * @dataProvider quoteDataProvider
   * @param string $string
   * @param string $expected
   */
  public function testQuote($string, $expected) {
    $this->assertEquals(
      $expected,
      \Papaya\Utility\Text\Javascript::quote($string)
    );
  }

  public function testQuoteWithDoubleQuotes() {
    $this->assertEquals(
      '"foo\\"-" + "-bar"',
      \Papaya\Utility\Text\Javascript::quote('foo"--bar', '"')
    );
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function quoteDataProvider() {
    return array(
      array('foo"bar', "'foo\"bar'"),
      array('foo\'bar', "'foo\\'bar'"),
      array('foo--bar', "'foo-' + '-bar'"),
      array("foo\r\nbar", "'foo\\r\\nbar'")
    );
  }
}
