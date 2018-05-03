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

/** @noinspection PhpDeprecationInspection */
require_once __DIR__.'/../bootstrap.php';

class PapayaLibSystemCheckitTest extends PapayaTestCase {



  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
   * @dataProvider isHTTPXDataProvider
   * @covers checkit::isHTTPX
   * @param string $string
   * @param bool $mandatory
   */
  public function testIsHTTPX($string, $mandatory) {
    $this->assertTrue(checkit::isHTTPX($string, $mandatory));
  }

  /**
   * @dataProvider isXhtmlDataProvider
   * @covers checkit::isXhtml
   * @param string $string
   * @param bool $mustContainValue
   * @param bool $expectedResult
   */
  public function testIsXhtml($string, $mustContainValue, $expectedResult) {
    $this->assertSame($expectedResult, checkit::isXhtml($string, $mustContainValue));
  }


  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/



  /***************************************************************************/
  /** DataProvider                                                           */
  /***************************************************************************/

  public static function isXhtmlDataProvider() {
    $attributeUrl = '<a href="http://www.papaya-cms.com/?d=xyz&amp;l=1&amp;b=2">link</a>';
    $validXhtml = '<br />';
    $invalidXhtml = '<br>';
    return array(
      'attribute url' => array($attributeUrl, FALSE, TRUE),
      'valid xhtml on non-mandatory value' => array($validXhtml, FALSE, TRUE),
      'invalid xhtml on non-mandatory value' => array($invalidXhtml, FALSE, FALSE),
      'empty xhtml with must contain value flag' => array('', TRUE, FALSE),
      'empty xhtml without must contain value flag' => array('', FALSE, TRUE)
    );
  }

  public static function isHTTPXDataProvider() {
    return array(
      array('', FALSE),
      array('https://www.blah.co.uk', TRUE),
      array('https://www.blah.de', TRUE),
      array('https://calvin:hobbes@www.blah.de', TRUE),
      array('http://127.0.0.1', TRUE),
      array('http://www.blah.de', TRUE),
      array('http://www.blah.de/', TRUE),
      array('http://www.blah.de/index.html', TRUE),

      array('http://www.blah.de/index.html#anchor', TRUE),
      array('http://www.blah.de/index.html#+', TRUE),
      array('http://www.blah.de/index.html#&', TRUE),
      array('http://www.blah.de/index.html#?', TRUE),
      array('http://www.blah.de/index.html#=', TRUE),
      array('http://www.blah.de/index.html#:', TRUE),
      array('http://www.blah.de/index.html#42', TRUE),
      array('http://www.blah.de/index.html#[]', TRUE),
      array('http://www.blah.de/index.html#()', TRUE),
      array('http://www.blah.de/index.html#%', TRUE),
      array('http://www.blah.de/index.html#!', TRUE),
      array('http://www.blah.de/index.html#_', TRUE),
      array('http://www.blah.de/index.html#,', TRUE),
      array('http://www.blah.de/index.html#;', TRUE),
      array('http://www.blah.de/index.html#/', TRUE),
      array('http://www.blah.de/index.html#-', TRUE),
      array('http://www.blah.de/index.html#anchor[23]=do();', TRUE),

      array('http://www.blah.de/?foo', TRUE),
      array('http://www.blah.de/?42', TRUE),
      array('http://www.blah.de/?[]', TRUE),
      array('http://www.blah.de/?:', TRUE),
      array('http://www.blah.de/?()', TRUE),
      array('http://www.blah.de/?.', TRUE),
      array('http://www.blah.de/?%', TRUE),
      array('http://www.blah.de/?!', TRUE),
      array('http://www.blah.de/?_', TRUE),
      array('http://www.blah.de/?,', TRUE),
      array('http://www.blah.de/?;', TRUE),
      array('http://www.blah.de/?-', TRUE),
      array('http://www.blah.de/?foo=bar', TRUE),
      array('http://www.blah.de/?foo-bar[42]=t(%20_bar-23);test!', TRUE),
      array('http://www.blah.de?foo=bar', TRUE),
      array('http://www.blah.de/index.html?foo=bar', TRUE),
      array('http://www.blah.de/index.html?foo=bar&Calvin=Hobbes', TRUE),
      array('http://www.blah.de/index.html?foo+bar=bar+foo', TRUE),
      array('http://www.blah.de/index.html?foobar=bar+foo', TRUE),

      array('http://www.blah.de#anchor', TRUE),
      array('http://www.blah.de/#anchor', TRUE),
      array('http://www.blah.de/index.html?foo=bar#anchor', TRUE),

      array('http://www.blah.de/sub+dir', TRUE),
      array('http://www.blah.de/sub:dir', TRUE),
      array('http://www.blah.de/sub%20dir', TRUE),
      array('http://www.blah.de/sub%dir', TRUE),
      array('http://www.blah.de/sub(dir)', TRUE),
      array('http://www.blah.de/sub.dir', TRUE),
      array('http://www.blah.de/sub_dir', TRUE),
      array('http://www.blah.de/sub!dir', TRUE),
      array('http://www.blah.de/sub,dir', TRUE),
      array('http://www.blah.de/sub;dir', TRUE),
      array('http://www.blah.de/sub-dir', TRUE),
      array('http://www.blah.de/sub-dir(foo):21,23', TRUE),
      array('http://www.blah.de/subdir/index.html', TRUE),
      array('http://www.blah.de:8080/subdir/index.html', TRUE),
      array('http://www.blah.de:8080', TRUE),
      array('http://www.blah.de:8080/index.php?test#blupp', TRUE),
      array('http://www.blah.de:80f80/', FALSE),
      array('http://www.blah.de:x/', FALSE),
    );
  }
}

