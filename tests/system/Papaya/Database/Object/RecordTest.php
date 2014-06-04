<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaDatabaseObjectRecordTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseObjectRecord::load
  */
  public function testLoad() {
    $record = array(
      'sample_id' => 42,
      'sample_title' => 'title text'
    );
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('sample_table')
      ->will($this->returnValue('papaya_sample_table'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('papaya_sample_table', 42))
      ->will($this->returnValue($databaseResult));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue($item->load(42));
    $this->assertAttributeEquals(
      array(
        'id' => 42,
        'title' => 'title text'
      ),
      '_values',
      $item
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::save
  * @covers PapayaDatabaseObjectRecord::_saveRecordWithAutoId
  * @covers PapayaDatabaseObjectRecord::_insertRecord
  */
  public function testSaveInsertsRecordWithAutoId() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'insertRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('sample_table')
      ->will($this->returnValue('papaya_sample_table'));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with(
        'papaya_sample_table',
        'sample_id',
        array(
          'sample_title' => 'title text'
        )
      )
      ->will($this->returnValue(TRUE));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue($item->save());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::save
  * @covers PapayaDatabaseObjectRecord::_saveRecordWithAutoId
  * @covers PapayaDatabaseObjectRecord::_insertRecord
  */
  public function testSaveInsertsRecordFailed() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'insertRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('sample_table')
      ->will($this->returnValue('papaya_sample_table'));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with(
        'papaya_sample_table',
        'sample_id',
        array(
          'sample_title' => 'title text'
        )
      )
      ->will($this->returnValue(FALSE));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertFalse($item->save());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_saveRecordWithDefinedId
  * @covers PapayaDatabaseObjectRecord::_insertRecord
  */
  public function testSaveInsertsRecordWithDefinedId() {
    $databaseResult = $this->getMock(
      'PapayaDatabaseResult'
    );
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'queryFmt', 'insertRecord', 'updateRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('sample_table')
      ->will($this->returnValue('papaya_sample_table'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->equalTo("SELECT COUNT(*) FROM %s WHERE sample_id = '%s'"),
        $this->equalTo(array('papaya_sample_table', 42))
      )
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with(
        'papaya_sample_table',
        NULL,
        array(
          'sample_id' => 42,
          'sample_title' => 'title text'
        )
      )
      ->will($this->returnValue(TRUE));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'id' => 42,
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertEquals(42, $item->_saveRecordWithDefinedId());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_saveRecordWithDefinedId
  */
  public function testSaveInsertsRecordWithDefinedIdExistanceQueryFailed() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'queryFmt', 'insertRecord', 'updateRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('sample_table')
      ->will($this->returnValue('papaya_sample_table'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->equalTo("SELECT COUNT(*) FROM %s WHERE sample_id = '%s'"),
        $this->equalTo(array('papaya_sample_table', 42))
      )
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with(
        'papaya_sample_table',
        NULL,
        array(
          'sample_id' => 42,
          'sample_title' => 'title text'
        )
      )
      ->will($this->returnValue(FALSE));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'id' => 42,
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertFalse($item->_saveRecordWithDefinedId());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_saveRecordWithDefinedId
  */
  public function testSaveInsertsRecordWithDefinedIdInsertQueryFailed() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('queryFmt', 'insertRecord', 'updateRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->equalTo("SELECT COUNT(*) FROM %s WHERE sample_id = '%s'"),
        $this->equalTo(array('sample_table', 42))
      )
      ->will($this->returnValue(FALSE));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'id' => 42,
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertFalse($item->_saveRecordWithDefinedId());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_saveRecordWithDefinedId
  */
  public function testSaveInsertsRecordWithDefinedIdWithoutValueExpectingFalse() {
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'id' => NULL,
      'title' => 'title text'
    );
    $this->assertFalse($item->_saveRecordWithDefinedId());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::save
  * @covers PapayaDatabaseObjectRecord::_saveRecordWithAutoId
  */
  public function testSaveUpdatesRecordWithAutoId() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'updateRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('sample_table')
      ->will($this->returnValue('papaya_sample_table'));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'papaya_sample_table',
        array('sample_id' => 23, 'sample_title' => 'title text'),
        array('sample_id' => 23)
      )
      ->will($this->returnValue(1));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'id' => 23,
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue($item->save());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_saveRecordWithDefinedId
  */
  public function testSaveUpdatesRecordWithDefinedId() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'queryFmt', 'insertRecord', 'updateRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('sample_table')
      ->will($this->returnValue('papaya_sample_table'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->equalTo("SELECT COUNT(*) FROM %s WHERE sample_id = '%s'"),
        $this->equalTo(array('papaya_sample_table', 42))
      )
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'papaya_sample_table',
        array(
          'sample_id' => 42,
          'sample_title' => 'title text'
        ),
        array(
          'sample_id' => 42
        )
      )
      ->will($this->returnValue(1));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'id' => 42,
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue($item->_saveRecordWithDefinedId());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::assign
  */
  public function testAssign() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $record->assign(
      array(
        'field1' => '1',
        'field2' => '2',
        'field3' => '3'
      )
    );
    $this->assertAttributeEquals(
      array(
        'field1' => '1',
        'field2' => '2'
      ),
      '_values',
      $record
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::toArray
  */
  public function testToArray() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertEquals(
      array(
        'field1' => 'value1',
        'field2' => NULL
      ),
      $record->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::getIterator
  */
  public function testGetIterator() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $iterator = $record->getIterator();
    $this->assertInstanceOf('ArrayIterator', $iterator);
    $this->assertEquals(
      array(
        'field1' => 'value1',
        'field2' => NULL
      ),
      $iterator->getArrayCopy()
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertFalse(
      $record->offsetExists('invalid_field')
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertTrue(
      $record->offsetExists('field1')
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::offsetGet
  */
  public function testOffsetGet() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertEquals(
      'value1', $record['field1']
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::offsetGet
  */
  public function testOffetGetWithInvalidField() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->setExpectedException('OutOfBoundsException');
    $dummy = $record['invalid_field'];
  }

  /**
  * @covers PapayaDatabaseObjectRecord::offsetGet
  */
  public function testOffsetGetExpectingNull() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertNull(
      $record['field2']
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::offsetSet
  */
  public function testOffetSet() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $record['field1'] = 'success';
    $this->assertAttributeSame(
      array('field1' => 'success'), '_values', $record
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::offsetSet
  */
  public function testOffetSetWithInvalidField() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->setExpectedException('OutOfBoundsException');
    $record['invalid_field'] = 'fail';
  }

  /**
  * @covers PapayaDatabaseObjectRecord::offsetUnset
  */
  public function testOffetUnset() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    unset($record['field1']);
    $this->assertAttributeSame(
      array(), '_values', $record
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::__isset
  */
  public function testPropertyIsset() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertTrue(
      isset($record->field1)
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::__get
  */
  public function testPropertyGet() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertEquals(
      'value1', $record->field1
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::__set
  */
  public function testPropertySet() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $record->field1 = 'success';
    $this->assertAttributeEquals(
      array(
        'field1' => 'success'
      ),
      '_values',
      $record
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::__set
  * @dataProvider provideValidOffsetVariations
  */
  public function testPropertySetTestingNormalization($offset) {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $record->_fields = array(
      'field_name_test' => ''
    );
    $record->_values = array();
    $record->$offset = 'success';
    $this->assertAttributeEquals(
      array(
        'field_name_test' => 'success'
      ),
      '_values',
      $record
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::__set
  */
  public function testPropertySetTestingNormalizationExpectingException() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->setExpectedException('OutOfBoundsException');
    $record->{'1_invalid__argument'} = 'success';
  }

  /**
  * @covers PapayaDatabaseObjectRecord::__unset
  */
  public function testPropertyUnset() {
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    unset($record->field1);
    $this->assertFalse(
      isset($record->field1)
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_loadRecordFromTable
  */
  public function testLoadRecordFromTable() {
    $record = array(
      'sample_id' => 42,
      'sample_title' => 'title text'
    );
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        "SELECT sample_id, sample_title FROM %s WHERE sample_id= '%s'", array('sample_table', 42)
      )
      ->will($this->returnValue($databaseResult));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $item->_loadRecordFromTable('sample_table', 'id', 42)
    );
    $this->assertAttributeEquals(
      array(
        'id' => 42,
        'title' => 'title text'
      ),
      '_values',
      $item
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_loadRecord
  */
  public function testLoadRecord() {
    $record = array(
      'sample_id' => 42,
      'sample_title' => 'title text'
    );
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with('SQL', array('sample_table', 42))
      ->will($this->returnValue($databaseResult));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $item->_loadRecord('SQL', array('sample_table', 42))
    );
    $this->assertAttributeEquals(
      array(
        'id' => 42,
        'title' => 'title text'
      ),
      '_values',
      $item
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_loadRecord
  */
  public function testLoadRecordWithEmptyResult() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue(NULL));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with('SQL', array('sample_table', 42))
      ->will($this->returnValue($databaseResult));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $item->_loadRecord('SQL', array('sample_table', 42))
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_loadRecord
  */
  public function testLoadRecordWithSqlError() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with('SQL', array('sample_table', 42))
      ->will($this->returnValue(FALSE));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $item->_loadRecord('SQL', array('sample_table', 42))
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_insertRecord
  */
  public function testInsertRecord() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('insertRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('sample_table', 'sample_id', array('sample_title' => 'title text'))
      ->will($this->returnValue(21));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      21, $item->_insertRecord('sample_table', 'id')
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_insertRecord
  */
  public function testInsertRecordRemovingOldId() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('insertRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('sample_table', 'sample_id', array('sample_title' => 'title text'))
      ->will($this->returnValue(21));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'id' => 23,
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      21, $item->_insertRecord('sample_table', 'id')
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_insertRecord
  */
  public function testInsertRecordWithoutId() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('insertRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with(
        'sample_table',
        NULL,
        array(
          'sample_id' => 23,
          'sample_title' => 'title text'
        )
      )
      ->will($this->returnValue(TRUE));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'id' => 23,
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $item->_insertRecord('sample_table', NULL)
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_updateRecord
  */
  public function testUpdateRecord() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('updateRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with('sample_table', array('sample_title' => 'title text'), array('sample_id' => 23))
      ->will($this->returnValue(1));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue($item->_updateRecord('sample_table', array('sample_id' => 23)));
  }

  /**
  * @covers PapayaDatabaseObjectRecord::delete
  * @covers PapayaDatabaseObjectRecord::_deleteRecord
  */
  public function testDelete() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'deleteRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('sample_table')
      ->will($this->returnValue('papaya_sample_table'));
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with('papaya_sample_table', array('sample_id' => 42))
      ->will($this->returnValue(1));
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'id' => 42,
      'title' => 'title text'
    );
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue($item->delete());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::delete
  * @covers PapayaDatabaseObjectRecord::_deleteRecord
  */
  public function testDeleteWithoutIdExpectingFalse() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'deleteRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('sample_table')
      ->will($this->returnValue('papaya_sample_table'));
    $databaseAccess
      ->expects($this->never())
      ->method('deleteRecord');
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $item->setDatabaseAccess($databaseAccess);
    $item->_fields = array(
      'id' => 'sample_id',
      'title' => 'sample_title'
    );
    $item->_values = array(
      'title' => 'title text'
    );
    $this->assertFalse($item->delete());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_applyCallback
  */
  public function testApplyCallback() {
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertEquals(
      array('test' => 'success'),
      $item->_applyCallback(array($this, 'callbackForApplyCallback'), NULL, array('test' => ''))
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_applyCallback
  */
  public function testApplyCallbackWithDefaultCallback() {
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertEquals(
      array('test' => 'success'),
      $item->_applyCallback(NULL, array($this, 'callbackForApplyCallback'), array('test' => ''))
    );
  }

  public function callbackForApplyCallback(array $data) {
    $data['test'] = 'success';
    return $data;
  }

  /**
  * @covers PapayaDatabaseObjectRecord::_applyCallback
  */
  public function testApplyCallbackWithoutCallbackExpectingException() {
    $item = new PapayaDatabaseObjectRecord_TestProxy();
    $this->setExpectedException('UnexpectedValueException');
    $item->_applyCallback(NULL, NULL, array());
  }

  /**
  * @covers PapayaDatabaseObjectRecord::convertRecordToValues
  */
  public function testConvertRecordToValues() {
    $row = array(
      'fieldname1' => 'success',
      'fieldname2' => 'failed',
    );
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertEquals(
      array('field1' => 'success'),
      $record->convertRecordToValues($row)
    );
  }

  /**
  * @covers PapayaDatabaseObjectRecord::convertValuesToRecord
  */
  public function testConvertValuesToRecord() {
    $data = array(
      'field1' => 'success',
      'field2' => 'failed',
    );
    $record = new PapayaDatabaseObjectRecord_TestProxy();
    $this->assertEquals(
      array('fieldname1' => 'success'),
      $record->convertValuesToRecord($data)
    );
  }

  /****************************
  * Data Provider
  ****************************/

  public static function provideValidOffsetVariations() {
    return array(
      array('field_name_test'),
      array('fieldNameTest'),
      array('FIELD_NAME_TEST')
    );
  }
}

/**
* Proxy class with some predefined values
*/
class PapayaDatabaseObjectRecord_TestProxy extends PapayaDatabaseObjectRecord {

  public $_fields = array(
    'field1' => 'fieldname1',
    'field2' => ''
  );

  public $_values = array(
    'field1' => 'value1'
  );

  public $_tableName = 'sample_table';

  public function _loadRecordFromTable($table, $identifier, $value, $convertRecordCallback = NULL) {
    return parent::_loadRecordFromTable($table, $identifier, $value, $convertRecordCallback);
  }

  public function _saveRecordWithDefinedId($convertRecordCallback = NULL) {
    return parent::_saveRecordWithDefinedId($convertRecordCallback);
  }

  public function _loadRecord($sql, array $parameters, $convertRecordCallback = NULL) {
    return parent::_loadRecord($sql, $parameters, $convertRecordCallback);
  }

  public function _insertRecord($table, $identifier = NULL, $convertValuesCallback = NULL) {
    return parent::_insertRecord($table, $identifier, $convertValuesCallback);
  }

  public function _updateRecord($table, array $filter, $convertValuesCallback = NULL) {
    return parent::_updateRecord($table, $filter, $convertValuesCallback);
  }

  public function _applyCallback($actual, $default, array $data) {
    return parent::_applyCallback($actual, $default, $data);
  }
}

