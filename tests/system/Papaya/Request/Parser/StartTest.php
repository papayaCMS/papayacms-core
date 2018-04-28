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

class PapayaRequestParserStartTest extends PapayaTestCase {

  /**
   * @covers PapayaRequestParserStart::parse
   * @dataProvider parseDataProvider
   * @param string $path
   * @param array|FALSE $expected
   */
  public function testParse($path, $expected) {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUrl $url */
    $url = $this
      ->getMockBuilder(PapayaUrl::class)
      ->setMethods(array('getPath'))
      ->getMock();
    $url
      ->expects($this->any())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserStart();
    $parser->papaya($this->mockPapaya()->application());
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function parseDataProvider() {
    return array(
      array(
        '/index.html',
        array(
          'mode' => 'page',
          'is_startpage' => TRUE,
          'output_mode' => 'html',
          'page_title' => 'index'
        )
      ),
      array(
        '/index.html.preview',
        array(
          'mode' => 'page',
          'is_startpage' => TRUE,
          'output_mode' => 'html',
          'preview' => TRUE,
          'page_title' => 'index'
        )
      ),
      array(
        '/index.html.preview.1240848952',
        array(
          'mode' => 'page',
          'is_startpage' => TRUE,
          'output_mode' => 'html',
          'preview' => TRUE,
          'preview_time' => 1240848952,
          'page_title' => 'index'
        )
      ),
      array(
        '/forum.5.html',
        FALSE
      ),
      array(
        '/index.de.html',
        array(
          'mode' => 'page',
          'is_startpage' => TRUE,
          'output_mode' => 'html',
          'page_title' => 'index',
          'language' => 'de'
        )
      ),
      array(
        '/foobar.rss',
        false
      ),
      array(
        '/index.rss',
        array(
          'mode' => 'page',
          'is_startpage' => TRUE,
          'output_mode' => 'rss',
          'page_title' => 'index',
        )
      ),
      array(
        '/index.de.rss',
        array(
          'mode' => 'page',
          'is_startpage' => TRUE,
          'output_mode' => 'rss',
          'page_title' => 'index',
          'language' => 'de'
        )
      )
    );
  }
}

