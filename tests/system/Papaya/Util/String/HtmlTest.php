<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilStringHtmlTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringHtml::escapeStripped
  * @dataProvider escapeStrippedDataProvider
  */
  public function testEscapeStripped($expected, $string) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringHtml::escapeStripped($string)
    );
  }

  /**
  * @covers PapayaUtilStringHtml::stripTags
  * @dataProvider stripTagsDataProvider
  */
  public function testStripTags($expected, $string) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringHtml::stripTags($string)
    );
  }

  /**
  * @covers PapayaUtilStringHtml::decodeNamedEntities
  * @dataProvider decodeNamedEntitiesDataProvider
  */
  public function testDecodeNamedEntities($expected, $string) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringHtml::decodeNamedEntities($string)
    );
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function escapeStrippedDataProvider() {
    return array(
      array('', '<sample>'),
      array('&lt;sample', '<sample'),
      array('&lt;5&gt;', "<5>"),
    );
  }

  public static function stripTagsDataProvider() {
    return array(
      array('', '<sample>'),
      array('<sample', '<sample'),
      array('<5>', "<5>"),
      array('FOO', '<p>FOO</p>')
    );
  }

  public static function decodeNamedEntitiesDataProvider() {
    return array(
      array('<sample>', '<sample>'),
      array('ä', '&auml;'),
      array('&gt;', '&gt;'),
      array('&lt;', '&lt;'),
      array('&quot;', '&quot;'),
      array(
        '<sample attr="">&#39;ä&#160;ö&#160;ü&#39;</sample>',
        '<sample attr="">&#39;&auml;&#160;&ouml;&#160;&uuml;&#39;</sample>'
      )
    );
  }
}
