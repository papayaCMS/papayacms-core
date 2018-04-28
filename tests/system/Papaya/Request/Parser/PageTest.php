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

class PapayaRequestParserPageTest extends PapayaTestCase {

  /**
   * @covers PapayaRequestParserPage::parse
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
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserPage();
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
        FALSE
      ),
      array(
        '/forum.5.html',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'page_id' => 5,
          'page_title' => 'forum'
        )
      ),
      array(
        '/forum.5.en.html',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'page_id' => 5,
          'page_title' => 'forum',
          'language' => 'en'
        )
      ),
      array(
        '/forum.5.html.preview',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'preview' => TRUE,
          'page_id' => 5,
          'page_title' => 'forum',
        )
      ),
      array(
        '/catalog.25.5.en.pdf',
        array(
          'mode' => 'page',
          'output_mode' => 'pdf',
          'page_id' => 5,
          'page_title' => 'catalog',
          'language' => 'en',
          'category_id' => 25
        )
      ),
      array(
        '/index.35.en.html.preview',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'preview' => TRUE,
          'page_id' => 35,
          'page_title' => 'index',
          'language' => 'en'
        )
      ),
      array(
        '/index.6.en.html.preview.1240848952',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'preview' => TRUE,
          'preview_time' => 1240848952,
          'page_id' => 6,
          'page_title' => 'index',
          'language' => 'en'
        )
      ),
      array(
        '/category_subcategory_page-title.6.en.html',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'page_id' => 6,
          'page_title' => 'category_subcategory_page-title',
          'language' => 'en'
        )
      )
    );
  }
}

