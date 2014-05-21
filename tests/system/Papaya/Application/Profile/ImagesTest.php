<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaApplicationProfileImagesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileImages::createObject
  */
  public function testCreateObject() {
    $profile = new PapayaApplicationProfileImages();
    $images = $profile->createObject($this->mockPapaya()->application());
    $this->assertInstanceOf(
      'PapayaUiImages',
      $images
    );
  }
}