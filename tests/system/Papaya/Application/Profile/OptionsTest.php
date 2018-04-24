<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileOptionsTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileOptions::createObject
  */
  public function testCreateObject() {
    $application = $this->getMock('PapayaApplication');
    $profile = new PapayaApplicationProfileOptions();
    $options = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaConfiguration',
      $options
    );
  }
}
