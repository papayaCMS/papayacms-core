<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaRequestParserMediaTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserMedia::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock(PapayaUrl::class, array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserMedia();
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
        '/index.media.01234567890123456789012345678901',
        array(
          'mode' => 'media',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901'
        )
      ),
      array(
        '/index.thumb.01234567890123456789012345678901',
        array(
          'mode' => 'media',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901'
        )
      ),
      array(
        '/index.download.01234567890123456789012345678901',
        array(
          'mode' => 'download',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901'
        )
      ),
      array(
        '/index.media.01234567890123456789012345678901.jpg',
        array(
          'mode' => 'media',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901.jpg'
        )
      ),
      array(
        '/index.media.01234567890123456789012345678901v23.jpg',
        array(
          'mode' => 'media',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901v23.jpg',
          'media_version' => 23
        )
      ),
      array(
        '/hn999sramon-7esrp-tours5.download.preview.d7e21e7a82c200090aa0e29327ad4581v23',
        array(
          'mode' => 'download',
          'preview' => TRUE,
          'media_id' => 'd7e21e7a82c200090aa0e29327ad4581',
          'media_uri' => 'd7e21e7a82c200090aa0e29327ad4581v23',
          'media_version' => 23
        )
      ),
      array(
        '/test-mp3.download.preview.dd68030bbe132f36922f855c48e71172v23.mp3',
        array(
          'mode' => 'download',
          'preview' => TRUE,
          'media_id' => 'dd68030bbe132f36922f855c48e71172',
          'media_uri' => 'dd68030bbe132f36922f855c48e71172v23.mp3',
          'media_version' => 23
        )
      )
    );
  }
}

