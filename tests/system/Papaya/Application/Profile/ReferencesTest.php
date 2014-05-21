<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaApplicationProfileReferenceTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileReferences::createObject
  */
  public function testCreateObject() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '[]',
        'PAPAYA_PATH_WEB' => '/'
      )
    );
    $application = $this->mockPapaya()->application(array('options' => $options));
    $profile = new PapayaApplicationProfileReferences();
    $reference = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaUiReferenceFactory',
      $reference
    );
  }
}