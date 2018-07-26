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

class PapayaUtilStringHtmlTest extends PapayaTestCase {

  /**
   * @covers \PapayaUtilStringHtml::escapeStripped
   * @dataProvider escapeStrippedDataProvider
   * @param string $expected
   * @param string $string
   */
  public function testEscapeStripped($expected, $string) {
    $this->assertEquals(
      $expected,
      \PapayaUtilStringHtml::escapeStripped($string)
    );
  }

  /**
   * @covers \PapayaUtilStringHtml::stripTags
   * @dataProvider stripTagsDataProvider
   * @param string $expected
   * @param string $string
   */
  public function testStripTags($expected, $string) {
    $this->assertEquals(
      $expected,
      \PapayaUtilStringHtml::stripTags($string)
    );
  }

  /**
   * @covers \PapayaUtilStringHtml::decodeNamedEntities
   * @dataProvider decodeNamedEntitiesDataProvider
   * @param string $expected
   * @param string $string
   */
  public function testDecodeNamedEntities($expected, $string) {
    $this->assertEquals(
      $expected,
      \PapayaUtilStringHtml::decodeNamedEntities($string)
    );
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function escapeStrippedDataProvider() {
    return array(
      array('', /** @lang Text */'<sample>'),
      array('&lt;sample', /** @lang Text */'<sample'),
      array('&lt;5&gt;', /** @lang Text */'<5>'),
    );
  }

  public static function stripTagsDataProvider() {
    return array(
      array('', /** @lang Text */'<sample>'),
      array('<sample', '<sample'),
      array('<5>', '<5>'),
      array('FOO', /** @lang Text */'<p>FOO</p>')
    );
  }

  public static function decodeNamedEntitiesDataProvider() {
    return array(
      array(/** @lang Text */'<sample>', /** @lang Text */'<sample>'),
      array('ä', '&auml;'),
      array('&gt;', '&gt;'),
      array('&lt;', '&lt;'),
      array('&quot;', '&quot;'),
      array(
        /** @lang XML */'<sample attr="">&#39;ä&#160;ö&#160;ü&#39;</sample>',
        /** @lang Text */'<sample attr="">&#39;&auml;&#160;&ouml;&#160;&uuml;&#39;</sample>'
      )
    );
  }
}
