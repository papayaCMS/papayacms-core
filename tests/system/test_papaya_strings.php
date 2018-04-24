<?php
require_once __DIR__.'/../bootstrap.php';

class PapayaLibSystemPapayaStringsTest extends PapayaTestCase {

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
    $handle = fopen('data://text/plain,'.$sample, 'r');
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
    $handle = fopen('data://text/plain,'.$sample, 'r');
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
    $handle = fopen('data://text/plain,'.$sample, 'r');
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
    $handle = fopen('data://text/plain,'.$sample, 'r');
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
    $handle = fopen('data://text/plain,'.$sample, 'r');
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
    $handle = fopen('data://text/plain,'.$sample, 'r');
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
    $handle = fopen('data://text/plain,'.$sample, 'r');
    $expected = array("before delimiter , after and before newline\n after");
    $this->assertSame(
      $expected,
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  public function testFgetcsvEofInValue() {
    $sample = '"value 1","end of file before the end of a value';
    $handle = fopen('data://text/plain,'.$sample, 'r');
    $this->assertFalse(
      papaya_strings::fgetcsv($handle, 8192, ',', '"')
    );
  }

  /**
  * @covers papaya_strings::escapeHTMLTags
  * @dataProvider getEscapteHTMLTagsData
  */
  public function testEscapeHTMLTags($str, $expectedStr, $nl2br) {
    $this->assertSame(
      $expectedStr, papaya_strings::escapeHTMLTags($str, $nl2br)
    );
  }

  public static function getEscapteHTMLTagsData() {
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


