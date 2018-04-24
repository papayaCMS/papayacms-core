<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaApplicationProfilePageReferencesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfilePageReferences::createObject
  */
  public function testCreateObject() {
    $application = $this->mockPapaya()->application();
    $profile = new PapayaApplicationProfilePageReferences();
    $options = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaUiReferencePageFactory',
      $options
    );
  }
}
