<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUtilStringAsciiArtworkTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringAsciiArtwork::get
  * @dataProvider getDataProvider
  */
  public function testGet($string, $fileName) {
    $this->assertStringEqualsFile(
      __DIR__.'/TestData/'.$fileName,
      PapayaUtilStringAsciiArtwork::get($string)
    );
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function getDataProvider() {
    return array(
      'ascii' => array('ascii', 'ascii.txt'),
      'numbers' => array('0123456789', 'numbers.txt'),
      'letters' => array('abcdefghijklmnopqrstuvwxyz', 'letters.txt'),
      'special chars' => array('-+:', 'special.txt')
    );
  }
}
