<?php
require_once(__DIR__.'/../../../../../bootstrap.php');

class PapayaMediaFileInfoImageTest extends PapayaTestCase {

  public function testFetchInfoFromPng() {
    $info = new PapayaMediaFileInfoImage(__DIR__.'/TestData/20x20.png');
    $this->assertEquals(
      [
        'is_valid' => TRUE,
        'mimetype' => 'image/png',
        'imagetype' => IMAGETYPE_PNG,
        'width' => 20,
        'height' => 20,
        'bits' => 8,
        'channels' => 0,
        'file_created' => NULL
      ],
      iterator_to_array($info, TRUE)
    );
  }
}
