<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaApplicationProfileAdministrationLanguageTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileAdministrationLanguage::createObject
  */
  public function testCreateObject() {
    $application = $this->mockPapaya()->application();
    $profile = new PapayaApplicationProfileAdministrationLanguage();
    $switch = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaAdministrationLanguagesSwitch',
      $switch
    );
  }
}
