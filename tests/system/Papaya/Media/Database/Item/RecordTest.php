<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');
PapayaTestCase::defineConstantDefaults('DB_FETCHMODE_ASSOC');

class PapayaMediaDatabaseItemRecordTest extends PapayaTestCase {

  /**
  * @covers PapayaMediaDatabaseItemRecord::load
  */
  public function testLoad() {
    $record = new PapayaMediaDatabaseItemRecord();
    $dbResult = $this->getMock('dbresult_common', array('fetchRow'));
    $dbResult
      ->expects($this->once())
      ->method('fetchRow')
      ->will(
        $this->returnValue(
          array(
            'file_id' => '',
            'folder_id' => '',
            'surfer_id' => '',
            'file_name' => '',
            'file_date' => '',
            'file_size' => '',
            'width' => '',
            'height' => ''
          )
        )
      );
    $dbCon = $this->getMock(
      'PapayaDatabaseAccess', array('queryFmt', 'getTableName'), array($record)
    );
    $dbCon
      ->expects($this->exactly(2))
      ->method('getTableName')
      ->will($this->returnValue('TEST'));
    $dbCon
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue($dbResult));
    $record->setDatabaseAccess($dbCon);
    $this->assertTrue($record->load('sample'));
  }

  /**
  * @covers PapayaMediaDatabaseItemRecord::load
  */
  public function testLoadExpectingFalse() {
    $record = new PapayaMediaDatabaseItemRecord();
    $dbResult = $this->getMock('dbresult_common', array('fetchRow'));
    $dbResult
      ->expects($this->once())
      ->method('fetchRow')
      ->will($this->returnValue(NULL));
    $dbCon = $this->getMock(
      'PapayaDatabaseAccess', array('queryFmt', 'getTableName'), array($record)
    );
    $dbCon
      ->expects($this->exactly(2))
      ->method('getTableName')
      ->will($this->returnValue('TEST'));
    $dbCon
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue($dbResult));
    $record->setDatabaseAccess($dbCon);
    $this->assertFalse($record->load('sample'));
  }

  /**
  * @covers PapayaMediaDatabaseItemRecord::save
  */
  public function testSaveExpectingFalse() {
    $record = new PapayaMediaDatabaseItemRecord();
    $this->assertFalse($record->save());
  }
}
