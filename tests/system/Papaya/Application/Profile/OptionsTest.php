<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileOptionsTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileOptions::createObject
  */
  public function testCreateObject() {
    $application = $this->createMock(PapayaApplication::class);
    $profile = new PapayaApplicationProfileOptions();
    $options = $profile->createObject($application);
    $this->assertInstanceOf(
      PapayaConfiguration::class,
      $options
    );
  }
}
