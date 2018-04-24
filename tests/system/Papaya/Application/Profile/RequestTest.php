<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileRequestTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileRequest::createObject
  */
  public function testCreateObject() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '[]',
        'PAPAYA_PATH_WEB' => '/'
      )
    );
    $application = $this->mockPapaya()->application(array('options' => $options));
    $profile = new PapayaApplicationProfileRequest();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaRequest',
      $request
    );
  }
}
