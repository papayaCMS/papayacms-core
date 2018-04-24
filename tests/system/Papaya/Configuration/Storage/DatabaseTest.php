<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaConfigurationStorageDatabaseTest extends PapayaTestCase {

  /**
  * @covers PapayaConfigurationStorageDatabase::records
  */
  public function testRecordsGetAfterSet() {
    $records = $this->createMock(PapayaContentConfiguration::class);
    $storage = new PapayaConfigurationStorageDatabase();
    $this->assertSame($records, $storage->records($records));
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::records
  */
  public function testRecordsGetImplicitCreate() {
    $storage = new PapayaConfigurationStorageDatabase();
    $this->assertInstanceOf('PapayaContentConfiguration', $storage->records());
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::load
  */
  public function testLoad() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('errorHandler')
      ->with($this->isType('array'));

    $records = $this->createMock(PapayaContentConfiguration::class);
    $records
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $records
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage = new PapayaConfigurationStorageDatabase();
    $storage->records($records);
    $this->assertTrue($storage->load());
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::handleError
  */
  public function testHandleErrorDevmode() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_DBG_DEVMODE' => TRUE
      )
    );
    $response = $this->createMock(PapayaResponse::class);
    $response
      ->expects($this->once())
      ->method('sendHeader')
      ->with('X-Papaya-Error: PapayaDatabaseExceptionQuery: Sample Error Message');

    $storage = new PapayaConfigurationStorageDatabase();
    $storage->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $options,
          'response' => $response
        )
      )
    );

    $exception = new PapayaDatabaseExceptionQuery(
      'Sample Error Message', 0, PapayaMessage::SEVERITY_ERROR, ''
    );
    $storage->handleError($exception);
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::handleError
  */
  public function testHandleErrorNoDevmodeSilent() {
    $response = $this->createMock(PapayaResponse::class);
    $response
      ->expects($this->never())
      ->method('sendHeader');

    $storage = new PapayaConfigurationStorageDatabase();
    $storage->papaya(
      $this->mockPapaya()->application(
        array(
          'response' => $response
        )
      )
    );

    $exception = new PapayaDatabaseExceptionQuery(
      'Sample Error Message', 0, PapayaMessage::SEVERITY_ERROR, ''
    );
    $storage->handleError($exception);
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::getIterator
  */
  public function testGetIterator() {
    $records = $this->createMock(PapayaContentConfiguration::class);
    $records
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              'SAMPLE_NAME' => array(
                'name' => 'SAMPLE_NAME',
                'value' => 'sample value'
              )
            )
          )
        )
      );
    $storage = new PapayaConfigurationStorageDatabase();
    $storage->records($records);
    $this->assertEquals(
      array('SAMPLE_NAME' => 'sample value'),
      PapayaUtilArray::ensure($storage)
    );
  }

}
