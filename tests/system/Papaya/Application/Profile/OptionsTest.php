<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileOptionsTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileOptions::createObject
  */
  public function testCreateObject() {
    $profile = new PapayaApplicationProfileOptions();
    $options = $profile->createObject($this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaConfiguration::class,
      $options
    );
  }
}
