<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaDatabaseRecordTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecord::__construct
  */
  public function testConstructor() {
    $record = new PapayaDatabaseRecord_TestProxy();
    $this->assertEquals(
      array('id' => NULL, 'data' => NULL),
      $record->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::__clone
  */
  public function testClone() {
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->key($this->createMock(PapayaDatabaseInterfaceKey::class));
    $record->mapping($this->createMock(PapayaDatabaseInterfaceMapping::class));
    $clone = clone $record;
    $this->assertNotSame($record->key(), $clone->key());
    $this->assertNotSame($record->mapping(), $clone->mapping());
  }

  /**
  * @covers PapayaDatabaseRecord::__clone
  */
  public function testCloneWithoutSubobjects() {
    $record = new PapayaDatabaseRecord_TestProxy();
    $clone = clone $record;
    $this->assertNotSame($record, $clone);
  }

  /**
  * @covers PapayaDatabaseRecord::load
  * @covers PapayaDatabaseRecord::_loadRecord
  * @covers PapayaDatabaseRecord::_compileCondition
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('field_id' => 42, 'field_data' => 'one'),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue("field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        "SELECT field_id, field_data FROM %s WHERE (field_id = '42')",
        array('tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $this->assertTrue($record->load(array('id' => 42)));
    $this->assertEquals(
      array('id' => 42, 'data' => 'one'),
      $record->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::load
  * @covers PapayaDatabaseRecord::_loadRecord
  * @covers PapayaDatabaseRecord::_compileCondition
  */
  public function testLoadWithScalar() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('field_id' => 42, 'field_data' => 'one'),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue("field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        "SELECT field_id, field_data FROM %s WHERE (field_id = '42')",
        array('tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $this->assertTrue($record->load(42));
    $this->assertEquals(
      array('id' => 42, 'data' => 'one'),
      $record->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::load
  * @covers PapayaDatabaseRecord::_loadRecord
  * @covers PapayaDatabaseRecord::_compileCondition
  */
  public function testLoadWithoutCondition() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('field_id' => 42, 'field_data' => 'one'),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        "SELECT field_id, field_data FROM %s ",
        array('tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $this->assertTrue($record->load(array()));
    $this->assertEquals(
      array('id' => 42, 'data' => 'one'),
      $record->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::load
  * @covers PapayaDatabaseRecord::_loadRecord
  * @covers PapayaDatabaseRecord::_compileCondition
  */
  public function testLoadWithoutConditionWithEmptyResult() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        "SELECT field_id, field_data FROM %s ",
        array('tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $this->assertFalse($record->load(array()));
  }

  /**
  * @covers PapayaDatabaseRecord::load
  * @covers PapayaDatabaseRecord::_loadRecord
  * @covers PapayaDatabaseRecord::_compileCondition
  */
  public function testLoadExpectingFalse() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue("field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        "SELECT field_id, field_data FROM %s WHERE (field_id = '42')",
        array('tablename')
      )
      ->will($this->returnValue(NULL));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $this->assertFalse($record->load(array('id' => 42)));
  }

  /**
  * @covers PapayaDatabaseRecord::load
  * @covers PapayaDatabaseRecord::_compileCondition
  */
  public function testLoadWithConditionObject() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('field_id' => 42, 'field_data' => 'one'),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->never())
      ->method('getSqlCondition');
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('tablename')
      )
      ->will($this->returnValue($databaseResult));
    $condition = $this
      ->getMockBuilder('PapayaDatabaseConditionElement')
      ->disableOriginalConstructor()
      ->getMock();
    $condition
      ->expects($this->once())
      ->method('getSql')
      ->will($this->returnValue(" field_id = '42'"));

    $records = new PapayaDatabaseRecord_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load($condition));
  }

  /**
  * @covers PapayaDatabaseRecord::createFilter
  */
  public function testCreateFilter() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $mapping = $this
      ->getMockBuilder('PapayaDatabaseInterfaceMapping')
      ->getMock();
    $records = new PapayaDatabaseRecord_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->mapping($mapping);
    $filter = $records->createFilter();
    $this->assertInstanceOf('PapayaDatabaseConditionRoot', $filter);
    $this->assertSame($databaseAccess, $filter->getDatabaseAccess());
    $this->assertSame($mapping, $filter->getMapping());
  }

  /**
  * @covers PapayaDatabaseRecord::isLoaded
  */
  public function testIsLoadedExpectingFalse() {
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $this->assertFalse($record->isLoaded());
  }

  /**
  * @covers PapayaDatabaseRecord::isLoaded
  */
  public function testIsLoadedAfterLoadExpectingTrue() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('field_id' => 42, 'field_data' => 'one'),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        "SELECT field_id, field_data FROM %s ",
        array('tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->load(array());
    $this->assertTrue($record->isLoaded());
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_insertRecord
  */
  public function testSaveInsertsRecordUsingDefaultAutoincrement() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('insertRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('tablename', 'field_id', array('field_data' => 'inserted'))
      ->will($this->returnValue(42));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->assign(
      array('data' => 'inserted')
    );
    $this->assertEquals(array('id' => 42), $record->save()->getFilter());
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_insertRecord
  */
  public function testSaveInsertsRecordUsingDefaultAutoincrementUseCallback() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('insertRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('tablename', 'field_id', array('field_data' => 'before insert'))
      ->will($this->returnValue(42));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->assign(
      array('data' => 'inserted')
    );
    $record->callbacks()->onBeforeInsert = array($this, 'callbackBeforeInsert');
    $this->assertEquals(array('id' => 42), $record->save()->getFilter());
    $this->assertEquals('before insert', $record->data);
  }

  public function callbackBeforeInsert($context, PapayaDatabaseRecord $record) {
    $record->data = 'before insert';
    return TRUE;
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_insertRecord
  */
  public function testSaveInsertsRecordBlockedByCallback() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('insertRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->never())
      ->method('insertRecord');
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->callbacks()->onBeforeInsert = array($this, 'callbackReturnFalse');
    $this->assertFalse($record->save());
  }

  public function callbackReturnFalse() {
    return FALSE;
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_insertRecord
  */
  public function testSaveInsertsRecordWithClientSideKey() {
    $key = $this->createMock(PapayaDatabaseInterfaceKey::class);
    $key
      ->expects($this->any())
      ->method('exists')
      ->will($this->returnValue(FALSE));
    $key
      ->expects($this->once())
      ->method('getFilter')
      ->with(PapayaDatabaseInterfaceKey::ACTION_CREATE)
      ->will($this->returnValue(array('id' => 'truth')));
    $key
      ->expects($this->any())
      ->method('getQualities')
      ->will($this->returnValue(0));

    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('insertRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('tablename', NULL, array('field_id' => 'truth', 'field_data' => 'inserted'))
      ->will($this->returnValue(TRUE));

    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->key($key);
    $record->assign(
      array('data' => 'inserted')
    );
    $this->assertEquals($key, $record->save());
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_insertRecord
  */
  public function testSaveInsertsRecordFailed() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('insertRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('tablename', 'field_id', array('field_data' => 'inserted'))
      ->will($this->returnValue(FALSE));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->assign(
      array('data' => 'inserted')
    );
    $this->assertFalse($record->save());
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_updateRecord
  */
  public function testSaveUpdatesRecordUsingDefaultAutoincrement() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('updateRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'tablename',
        array('field_data' => 'updated', 'field_id' => 42),
        array('field_id' => 42))
      ->will($this->returnValue(42));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->assign(
      $values = array(
        'data' => 'updated',
        'id' => 42
      )
    );
    $record->key()->assign($values);
    $this->assertTrue($record->save());
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_updateRecord
  */
  public function testSaveUpdatesRecordUsingDefaultAutoincrementAndCallback() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('updateRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'tablename',
        array('field_data' => 'before update', 'field_id' => 42),
        array('field_id' => 42))
      ->will($this->returnValue(42));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->assign(
      $values = array(
        'data' => 'updated',
        'id' => 42
      )
    );
    $record->key()->assign($values);
    $record->callbacks()->onBeforeUpdate = array($this, 'callbackBeforeUpdate');
    $this->assertTrue($record->save());
    $this->assertEquals('before update', $record->data);
  }

  public function callbackBeforeUpdate($context, PapayaDatabaseRecord $record) {
    $record->data = 'before update';
    return TRUE;
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_updateRecord
  */
  public function testSaveUpdatesRecordBlockedByCallback() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('updateRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->never())
      ->method('updateRecord');
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->assign(
      $values = array(
        'data' => 'updated',
        'id' => 42
      )
    );
    $record->key()->assign($values);
    $record->callbacks()->onBeforeUpdate = array($this, 'callbackReturnFalse');
    $this->assertFalse($record->save());
  }

  /**
  * @covers PapayaDatabaseRecord::delete
  */
  public function testDelete() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('deleteRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with(
        'tablename',
        array('field_id' => 42))
      ->will($this->returnValue(1));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->setDatabaseAccess($databaseAccess);
    $record->assign(
      $values = array(
        'data' => 'updated',
        'id' => 42
      )
    );
    $record->key()->assign($values);
    $this->assertTrue($record->delete());
  }
  /**
  * @covers PapayaDatabaseRecord::delete
  */
  public function testDeleteWithEmptyFilterExpectingFalse() {
    $key = $this->createMock(PapayaDatabaseInterfaceKey::class);
    $key
      ->expects($this->any())
      ->method('getFilter')
      ->will($this->returnValue(array()));
    $record = new PapayaDatabaseRecord_TestProxy(
      array('id' => 'field_id', 'data' => 'field_data')
    );
    $record->key($key);
    $this->assertFalse($record->delete());
  }

  /**
  * @covers PapayaDatabaseRecord::mapping
  */
  public function testMappingGetAfterSet() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->mapping($mapping);
    $this->assertSame(
      $mapping, $record->mapping()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::mapping
  * @covers PapayaDatabaseRecord::_createMapping
  */
  public function testMappingGetImplicitCreate() {
    $record = new PapayaDatabaseRecord_TestProxy();
    $this->assertInstanceOf(
      'PapayaDatabaseRecordMapping', $record->mapping()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::key
  */
  public function testKeyGetAfterSet() {
    $key = $this->createMock(PapayaDatabaseInterfaceKey::class);
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->key($key);
    $this->assertSame(
      $key, $record->key()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::key
  * @covers PapayaDatabaseRecord::_createKey
  */
  public function testKeyGetImplicitCreate() {
    $record = new PapayaDatabaseRecord_TestProxy();
    $this->assertInstanceOf(
      'PapayaDatabaseRecordKeyAutoincrement', $record->key()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::setDatabaseAccess
  * @covers PapayaDatabaseRecord::getDatabaseAccess
  */
  public function testGetDatabaseAccessAfterSet() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess, $record->getDatabaseAccess()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::getDatabaseAccess
  */
  public function testGetDatabaseAccessImplicitCreate() {
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      'PapayaDatabaseAccess', $record->getDatabaseAccess()
    );
    $this->assertSame(
      $record->papaya(), $record->getDatabaseAccess()->papaya()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(PapayaDatabaseRecordCallbacks::class);
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->callbacks($callbacks);
    $this->assertSame($callbacks, $record->callbacks());
  }

  /**
  * @covers PapayaDatabaseRecord::callbacks
  * @covers PapayaDatabaseRecord::_createCallbacks
  */
  public function testCallbacksImplicitCreate() {
    $record = new PapayaDatabaseRecord_TestProxy();
    $this->assertInstanceOf('PapayaDatabaseRecordCallbacks', $record->callbacks());
  }
}

class PapayaDatabaseRecord_TestProxy extends PapayaDatabaseRecord {

  protected $_fields = array(
    'id' => 'field_id',
    'data' => 'field_data'
  );

  protected $_tableName = 'tablename';
}
