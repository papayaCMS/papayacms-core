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

require_once __DIR__.'/../bootstrap.php';

class PapayaLibSystemPapayaStringsTest extends \Papaya\TestFramework\TestCase {

  public function testSplitLines() {
    $sample = "Line1\rLine2\n\rLine3\r\nLine4\nLine5";
    $expected = array(
      'Line1', 'Line2', 'Line3', 'Line4', 'Line5'
    );
    $this->assertSame($expected, papaya_strings::splitLines($sample));
  }

  public function testFgetcsvOnlyReadsOneRow() {
    $sample = '"row 1 value 1","value 2"'
      ."\n".'"row 2 value 1","value 2"';
    $handle = fopen('data://text/plain,'.$sample, 'rb');
    $expected = array(
      'row 1 value 1', 'value 2'
    );
    $this->assertSame(
      $expected,
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  public function testFgetcsvOnlyReadsOneRowWithCarriageReturn() {
    $sample = '"row 1 value 1","value 2"'
      ."\r\n".'"row 2 value 1","value 2"';
    $handle = fopen('data://text/plain,'.$sample, 'rb');
    $expected = array(
      'row 1 value 1', 'value 2'
    );
    $this->assertSame(
      $expected,
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  public function testFgetcsvDelimiterInValue() {
    $sample = '"value 1","value 2 before delimiter , after","value 3"';
    $handle = fopen('data://text/plain,'.$sample, 'rb');
    $expected = array(
      'value 1', 'value 2 before delimiter , after', 'value 3'
    );
    $this->assertSame(
      $expected,
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  public function testFgetcsvEnclosureInValue() {
    $sample = '"value 1","value 2 before enclosure "" after","value 3"';
    $handle = fopen('data://text/plain,'.$sample, 'rb');
    $expected = array(
      'value 1', 'value 2 before enclosure " after', 'value 3'
    );
    $this->assertSame(
      $expected,
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  public function testFgetcsvNewlineInValue() {
    $sample = '"value 1","value 2 before newline '."\n".' after","value 3"';
    $handle = fopen('data://text/plain,'.$sample, 'rb');
    $expected = array(
      'value 1', "value 2 before newline \n after", 'value 3'
    );
    $this->assertSame(
      $expected,
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  public function testFgetcsvCarriageReturnAndNewlineInValue() {
    $sample = '"value 1","value 2 before newline '."\r\n".' after","value 3"';
    $handle = fopen('data://text/plain,'.$sample, 'rb');
    $expected = array(
      'value 1', "value 2 before newline \r\n after", 'value 3'
    );
    $this->assertSame(
      $expected,
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  /**
   * @covers papaya_strings::fgetcsv
   */
  public function testFgetcsvDelimiterAndNewlineInValue() {
    $sample = '"before delimiter , after and before newline'."\n".' after"';
    $handle = fopen('data://text/plain,'.$sample, 'rb');
    $expected = array("before delimiter , after and before newline\n after");
    $this->assertSame(
      $expected,
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  public function testFgetcsvEofInValue() {
    $sample = '"value 1","end of file before the end of a value';
    $handle = fopen('data://text/plain,'.$sample, 'rb');
    $this->assertFalse(
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  /**
   * @covers papaya_strings::escapeHTMLTags
   * @dataProvider getEscapeHTMLTagsData
   * @param string $string
   * @param string $expected
   * @param bool $nl2br
   */
  public function testEscapeHTMLTags($string, $expected, $nl2br) {
    $this->assertSame(
      $expected, papaya_strings::escapeHTMLTags($string, $nl2br)
    );
  }

  public static function getEscapeHTMLTagsData() {
    // language=TEXT
    return array(
      array(
        "hallo\ntext", "hallo\ntext", FALSE
      ),
      array(
        "hallo\ntext", "hallo<br />\ntext", TRUE
      ),
      array(
        "<div class=\"select<\">hallo\ntext</div>",
        "<div class=\"select&lt;\">hallo\ntext</div>",
        FALSE
      ),
      array(
        "<div class='select>'>hallo\ntext</div>",
        "<div class='select&gt;'>hallo<br />\ntext</div>",
        TRUE
      ),
      array(
        "<!-- hallo\ntext -->", "<!-- hallo\ntext -->", FALSE
      ),
      array(
        "<!-- hallo\ntext -->", "<!-- hallo\ntext -->", TRUE
      ),
      array(
        "<div>hallo\ntext<!-- hallo\ntext --></div>",
        "<div>hallo\ntext<!-- hallo\ntext --></div>",
        FALSE
      ),
      array(
        "<div>hallo\ntext<!-- hallo\ntext --></div>",
        "<div>hallo<br />\ntext<!-- hallo\ntext --></div>",
        TRUE
      )
    );
  }
}


