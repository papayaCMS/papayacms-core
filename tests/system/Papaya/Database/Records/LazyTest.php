<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordsLazyTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordsLazy::activateLazyLoad
  * @covers PapayaDatabaseRecordsLazy::getLazyLoadParameters
  */
  public function testActivateLazyLoadDoesNotTriggerLoading() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('queryFmt');
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->activateLazyLoad();
    $this->assertEquals(
      array(),
      $records->getLazyLoadParameters()
    );
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::activateLazyLoad
  * @covers PapayaDatabaseRecordsLazy::lazyLoad
  * @covers PapayaDatabaseRecordsLazy::_loadRecords
  */
  public function testActiveLazyLoadParametersAreUsedDuringLazyLoad() {
    $databaseAccess = $this->getDatabaseAccessFixture();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 21))
      ->will($this->returnValue('>>CONDITION>>'));
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->activateLazyLoad(array('id' => 21));
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        )
      ),
      $records->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::lazyLoad
  */
  public function testLoadIsOnlyCalledOnce() {
    $databaseAccess = $this->getDatabaseAccessFixture();
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->activateLazyLoad();
    $records->toArray();
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        )
      ),
      $records->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::absCount
  */
  public function testAbsCount() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 21,
            'field_content' => 'content one'
          ),
          FALSE
        )
      );
    $databaseResult
      ->expects($this->once())
      ->method('absCount')
      ->will($this->returnValue(7));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue($databaseResult));

    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->activateLazyLoad();
    $this->assertEquals(7, $records->absCount());
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::toArray
  */
  public function testToArray() {
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        )
      ),
      $records->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::getIterator
  */
  public function testGetIterator() {
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        )
      ),
      iterator_to_array($records)
    );
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::count
  */
  public function testCount() {
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertCount(
      1, $records
    );
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::offsetExists
  */
  public function testOffsetExists() {
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertTrue(isset($records[21]));
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::offsetGet
  */
  public function testOffsetGet() {
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $this->assertEquals(
      array(
       'id' => 21,
       'content' => 'content one'
      ),
      $records[21]
    );
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::offsetSet
  */
  public function testOffsetSet() {
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    $records[42] = array(
      'id' => 42,
      'content' => 'content two'
    );
    $this->assertEquals(
      array(
        21 => array(
         'id' => 21,
         'content' => 'content one'
        ),
        42 => array(
          'id' => 42,
          'content' => 'content two'
        )
      ),
      iterator_to_array($records)
    );
  }

  /**
  * @covers PapayaDatabaseRecordsLazy::offsetUnset
  */
  public function testOffsetUnset() {
    $records = new PapayaDatabaseRecordsLazy_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseAccessFixture());
    $records->activateLazyLoad();
    unset($records[21]);
    $this->assertCount(0, $records);
  }

  /*************************
  * Fixtures
  *************************/

  private function getDatabaseAccessFixture() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 21,
            'field_content' => 'content one'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue($databaseResult));
    return $databaseAccess;
  }
}

class PapayaDatabaseRecordsLazy_TestProxy extends PapayaDatabaseRecordsLazy {

  protected $_fields = array(
    'id' => 'field_id',
    'content' => 'field_content'
  );

  protected $_identifierProperties = array('id');

  protected $_tableName = 'sampletable';
}
