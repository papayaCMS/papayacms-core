<?php
require_once __DIR__.'/../../../../../bootstrap.php';
PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_AUTHOPTIONS',
  'PAPAYA_DB_TBL_AUTHUSER',
  'PAPAYA_DB_TBL_AUTHGROUPS',
  'PAPAYA_DB_TBL_AUTHLINK',
  'PAPAYA_DB_TBL_AUTHPERM',
  'PAPAYA_DB_TBL_AUTHMODPERMS',
  'PAPAYA_DB_TBL_AUTHMODPERMLINKS',
  'PAPAYA_DB_TBL_SURFER'
);

class PapayaApplicationProfileAdministrationUserTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileAdministrationUser::createObject
  */
  public function testCreateObject() {
    $options = $this->createMock(PapayaConfigurationCms::class);
    $options
      ->expects($this->once())
      ->method('defineDatabaseTables');
    $application = $this
      ->mockPapaya()
      ->application(
        array('options' => $options)
      );
    $profile = new PapayaApplicationProfileAdministrationUser();
    $options = $profile->createObject($application);
    $this->assertInstanceOf(
      'base_auth',
      $options
    );
  }
}
