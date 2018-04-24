<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfilesCmsTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfilesCms::getProfiles
  */
  public function testGetProfiles() {
    $application = $this->getMock('PapayaApplication');
    $profiles = new PapayaApplicationProfilesCms();
    $list = $profiles->getProfiles($application);
    $this->assertEquals(
      array(
        'Database' => new PapayaApplicationProfileDatabase(),
        'Images' => new PapayaApplicationProfileImages(),
        'Languages' => new PapayaApplicationProfileLanguages(),
        'Messages' => new PapayaApplicationProfileMessages(),
        'Options' => new PapayaApplicationProfileOptions(),
        'Plugins' => new PapayaApplicationProfilePlugins(),
        'Profiler' => new PapayaApplicationProfileProfiler(),
        'Request' => new PapayaApplicationProfileRequest(),
        'Response' => new PapayaApplicationProfileResponse(),
        'Session' => new PapayaApplicationProfileSession(),
        'Surfer' => new PapayaApplicationProfileSurfer(),

        'AdministrationUser' => new PapayaApplicationProfileAdministrationUser(),
        'AdministrationLanguage' => new PapayaApplicationProfileAdministrationLanguage(),

        'References' => new PapayaApplicationProfileReferences(),
        'PageReferences' => new PapayaApplicationProfilePageReferences()
      ),
      $list
    );
  }
}
