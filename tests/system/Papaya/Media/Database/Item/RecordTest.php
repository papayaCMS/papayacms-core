<?php
require_once(__DIR__.'/../../../../../bootstrap.php');
require_once(__DIR__.'/../../../../../../src/system/db/base.php');
PapayaTestCase::defineConstantDefaults('DB_FETCHMODE_ASSOC');

class PapayaMediaDatabaseItemRecordTest extends PapayaTestCase {

  /**
  * @covers PapayaMediaDatabaseItemRecord::load
  */
  public function testLoad() {
    $record = new PapayaMediaDatabaseItemRecord();
    $dbResult = $this
      ->getMockBuilder(dbresult_base::class)
      ->disableOriginalConstructor()
      ->allowMockingUnknownTypes()
      ->getMock();
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
      PapayaDatabaseAccess::class, array('queryFmt', 'getTableName'), array($record)
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
    $dbResult = $this
      ->getMockBuilder(dbresult_base::class)
      ->disableOriginalConstructor()
      ->allowMockingUnknownTypes()
      ->getMock();
    $dbResult
      ->expects($this->once())
      ->method('fetchRow')
      ->will($this->returnValue(NULL));
    $dbCon = $this->getMock(
      PapayaDatabaseAccess::class, array('queryFmt', 'getTableName'), array($record)
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
