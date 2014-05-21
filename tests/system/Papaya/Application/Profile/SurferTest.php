<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');
PapayaTestCase::defineConstantDefaults(
  array(
    'PAPAYA_DB_TBL_SURFER',
    'PAPAYA_DB_TBL_SURFERGROUPS',
    'PAPAYA_DB_TBL_SURFERPERM',
    'PAPAYA_DB_TBL_SURFERACTIVITY',
    'PAPAYA_DB_TBL_SURFERPERMLINK',
    'PAPAYA_DB_TBL_SURFERCHANGEREQUESTS',
    'PAPAYA_DB_TBL_TOPICS'
  )
);

class PapayaApplicationProfileSurferTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileSurfer::createObject
  */
  public function testCreateObject() {
    $profile = new PapayaApplicationProfileSurfer();
    $surferOne = base_surfer::getInstance(FALSE);
    $surferTwo = $profile->createObject($application = NULL);
    $this->assertSame(
      $surferOne,
      $surferTwo
    );
  }
}
