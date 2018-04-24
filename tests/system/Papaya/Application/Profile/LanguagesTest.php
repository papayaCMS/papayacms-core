<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileLanguagesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileLanguages::createObject
  */
  public function testCreateObject() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue(FALSE));
    $databaseManager = $this->getMock('PapayaDatabaseManager');
    $databaseManager
      ->expects($this->once())
      ->method('createDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $application = $this->getMock('PapayaApplication');
    $application
      ->expects($this->any())
      ->method('__get')
      ->with('database')
      ->will($this->returnValue($databaseManager));
    $profile = new PapayaApplicationProfileLanguages();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaContentLanguages',
      $request
    );
  }
}
