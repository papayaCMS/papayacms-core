<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaRequestParserImageTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserImage::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserImage();
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
        '/testbutton.image.png.preview',
        array(
          'mode' => 'image',
          'preview' => TRUE,
          'image_identifier' => 'testbutton',
          'image_format' => 'png'
        )
      )
    );
  }
}

