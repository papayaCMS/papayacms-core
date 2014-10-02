<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaRequestParserStartTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserStart::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
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

