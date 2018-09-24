<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Database\BaseObject {

  require_once __DIR__.'/../../../../bootstrap.php';

  class RecordTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Database\BaseObject\Record::load
     */
    public function testLoad() {
      $record = array(
        'sample_id' => 42,
        'sample_title' => 'title text'
      );
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchRow')
        ->with(\Papaya\Database\Result::FETCH_ASSOC)
        ->will($this->returnValue($record));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('table_sample_table', 42))
        ->will($this->returnValue($databaseResult));
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::save
     * @covers \Papaya\Database\BaseObject\Record::_saveRecordWithAutoId
     * @covers \Papaya\Database\BaseObject\Record::_insertRecord
     */
    public function testSaveInsertsRecordWithAutoId() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with(
          'table_sample_table',
          23,
          array(
            'sample_title' => 'title text'
          )
        )
        ->will($this->returnValue(TRUE));
      $item = new Record_TestProxy();
      $item->_fields = array(
        'id' => '23',
        'title' => 'sample_title'
      );
      $item->_values = array(
        'title' => 'title text'
      );
      $item->setDatabaseAccess($databaseAccess);
      $this->assertTrue($item->save());
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::save
     * @covers \Papaya\Database\BaseObject\Record::_saveRecordWithAutoId
     * @covers \Papaya\Database\BaseObject\Record::_insertRecord
     */
    public function testSaveInsertsRecordFailed() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with(
          'table_sample_table',
          'sample_id',
          array(
            'sample_title' => 'title text'
          )
        )
        ->will($this->returnValue(FALSE));
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::_saveRecordWithDefinedId
     * @covers \Papaya\Database\BaseObject\Record::_insertRecord
     */
    public function testSaveInsertsRecordWithDefinedId() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchField')
        ->will($this->returnValue(0));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with(
          $this->equalTo(/** @lang Text */
            "SELECT COUNT(*) FROM %s WHERE sample_id = '%s'"),
          $this->equalTo(array('table_sample_table', 42))
        )
        ->will($this->returnValue($databaseResult));
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with(
          'table_sample_table',
          NULL,
          array(
            'sample_id' => 42,
            'sample_title' => 'title text'
          )
        )
        ->will($this->returnValue(TRUE));
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::_saveRecordWithDefinedId
     */
    public function testSaveInsertsRecordWithDefinedIdExistenceQueryFailed() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchField')
        ->will($this->returnValue(0));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with(
          $this->equalTo(/** @lang Text */
            "SELECT COUNT(*) FROM %s WHERE sample_id = '%s'"),
          $this->equalTo(array('table_sample_table', 42))
        )
        ->will($this->returnValue($databaseResult));
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with(
          'table_sample_table',
          NULL,
          array(
            'sample_id' => 42,
            'sample_title' => 'title text'
          )
        )
        ->will($this->returnValue(FALSE));
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::_saveRecordWithDefinedId
     */
    public function testSaveInsertsRecordWithDefinedIdInsertQueryFailed() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with(
          $this->equalTo(/** @lang Text */
            "SELECT COUNT(*) FROM %s WHERE sample_id = '%s'"),
          $this->equalTo(array('table_sample_table', 42))
        )
        ->will($this->returnValue(FALSE));
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::_saveRecordWithDefinedId
     */
    public function testSaveInsertsRecordWithDefinedIdWithoutValueExpectingFalse() {
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::save
     * @covers \Papaya\Database\BaseObject\Record::_saveRecordWithAutoId
     */
    public function testSaveUpdatesRecordWithAutoId() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('updateRecord')
        ->with(
          'table_sample_table',
          array('sample_id' => 23, 'sample_title' => 'title text'),
          array('sample_id' => 23)
        )
        ->will($this->returnValue(1));
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::_saveRecordWithDefinedId
     */
    public function testSaveUpdatesRecordWithDefinedId() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchField')
        ->will($this->returnValue(1));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with(
          $this->equalTo(/** @lang Text */
            "SELECT COUNT(*) FROM %s WHERE sample_id = '%s'"),
          $this->equalTo(array('table_sample_table', 42))
        )
        ->will($this->returnValue($databaseResult));
      $databaseAccess
        ->expects($this->once())
        ->method('updateRecord')
        ->with(
          'table_sample_table',
          array(
            'sample_id' => 42,
            'sample_title' => 'title text'
          ),
          array(
            'sample_id' => 42
          )
        )
        ->will($this->returnValue(1));
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::assign
     */
    public function testAssign() {
      $record = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::toArray
     */
    public function testToArray() {
      $record = new Record_TestProxy();
      $this->assertEquals(
        array(
          'field1' => 'value1',
          'field2' => NULL
        ),
        $record->toArray()
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::getIterator
     */
    public function testGetIterator() {
      $record = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::offsetExists
     */
    public function testOffsetExistsExpectingFalse() {
      $record = new Record_TestProxy();
      $this->assertFalse(
        $record->offsetExists('invalid_field')
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::offsetExists
     */
    public function testOffsetExistsExpectingTrue() {
      $record = new Record_TestProxy();
      $this->assertTrue(
        $record->offsetExists('field1')
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::offsetGet
     */
    public function testOffsetGet() {
      $record = new Record_TestProxy();
      $this->assertEquals(
        'value1', $record['field1']
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::offsetGet
     */
    public function testOffetGetWithInvalidField() {
      $record = new Record_TestProxy();
      $this->expectException(\OutOfBoundsException::class);
      $record['invalid_field'];
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::offsetGet
     */
    public function testOffsetGetExpectingNull() {
      $record = new Record_TestProxy();
      $this->assertNull(
        $record['field2']
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::offsetSet
     */
    public function testOffetSet() {
      $record = new Record_TestProxy();
      $record['field1'] = 'success';
      $this->assertAttributeSame(
        array('field1' => 'success'), '_values', $record
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::offsetSet
     */
    public function testOffetSetWithInvalidField() {
      $record = new Record_TestProxy();
      $this->expectException(\OutOfBoundsException::class);
      $record['invalid_field'] = 'fail';
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::offsetUnset
     */
    public function testOffetUnset() {
      $record = new Record_TestProxy();
      unset($record['field1']);
      $this->assertAttributeSame(
        array(), '_values', $record
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::__isset
     */
    public function testPropertyIsset() {
      $record = new Record_TestProxy();
      $this->assertTrue(
        isset($record->field1)
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::__get
     */
    public function testPropertyGet() {
      $record = new Record_TestProxy();
      /** @noinspection PhpUndefinedFieldInspection */
      $this->assertEquals(
        'value1', $record->field1
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::__set
     */
    public function testPropertySet() {
      $record = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::__set
     * @dataProvider provideValidOffsetVariations
     * @param string $offset
     */
    public function testPropertySetTestingNormalization($offset) {
      $record = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::__set
     */
    public function testPropertySetTestingNormalizationExpectingException() {
      $record = new Record_TestProxy();
      $this->expectException(\OutOfBoundsException::class);
      $record->{'1_invalid__argument'} = 'success';
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::__unset
     */
    public function testPropertyUnset() {
      $record = new Record_TestProxy();
      unset($record->field1);
      $this->assertFalse(
        isset($record->field1)
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::_loadRecordFromTable
     */
    public function testLoadRecordFromTable() {
      $record = array(
        'sample_id' => 42,
        'sample_title' => 'title text'
      );
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchRow')
        ->with(\Papaya\Database\Result::FETCH_ASSOC)
        ->will($this->returnValue($record));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with(
          "SELECT sample_id, sample_title FROM %s WHERE sample_id= '%s'", array('table_sample_table', 42)
        )
        ->will($this->returnValue($databaseResult));
      $item = new Record_TestProxy();
      $item->_fields = array(
        'id' => 'sample_id',
        'title' => 'sample_title'
      );
      $item->setDatabaseAccess($databaseAccess);
      $this->assertTrue(
        $item->_loadRecordFromTable('table_sample_table', 'id', 42)
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
     * @covers \Papaya\Database\BaseObject\Record::_loadRecord
     */
    public function testLoadRecord() {
      $record = array(
        'sample_id' => 42,
        'sample_title' => 'title text'
      );
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchRow')
        ->with(\Papaya\Database\Result::FETCH_ASSOC)
        ->will($this->returnValue($record));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with('SQL', array('table_sample_table', 42))
        ->will($this->returnValue($databaseResult));
      $item = new Record_TestProxy();
      $item->_fields = array(
        'id' => 'sample_id',
        'title' => 'sample_title'
      );
      $item->setDatabaseAccess($databaseAccess);
      $this->assertTrue(
        $item->_loadRecord('SQL', array('table_sample_table', 42))
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
     * @covers \Papaya\Database\BaseObject\Record::_loadRecord
     */
    public function testLoadRecordWithEmptyResult() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchRow')
        ->with(\Papaya\Database\Result::FETCH_ASSOC)
        ->will($this->returnValue(NULL));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with('SQL', array('table_sample_table', 42))
        ->will($this->returnValue($databaseResult));
      $item = new Record_TestProxy();
      $item->setDatabaseAccess($databaseAccess);
      $this->assertFalse(
        $item->_loadRecord('SQL', array('table_sample_table', 42))
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::_loadRecord
     */
    public function testLoadRecordWithSqlError() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with('SQL', array('table_sample_table', 42))
        ->will($this->returnValue(FALSE));
      $item = new Record_TestProxy();
      $item->setDatabaseAccess($databaseAccess);
      $this->assertFalse(
        $item->_loadRecord('SQL', array('table_sample_table', 42))
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::_insertRecord
     */
    public function testInsertRecord() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with('table_sample_table', 'sample_id', array('sample_title' => 'title text'))
        ->will($this->returnValue(21));
      $item = new Record_TestProxy();
      $item->_fields = array(
        'id' => 'sample_id',
        'title' => 'sample_title'
      );
      $item->_values = array(
        'title' => 'title text'
      );
      $item->setDatabaseAccess($databaseAccess);
      $this->assertEquals(
        21, $item->_insertRecord('table_sample_table', 'id')
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::_insertRecord
     */
    public function testInsertRecordRemovingOldId() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with('table_sample_table', 'sample_id', array('sample_title' => 'title text'))
        ->will($this->returnValue(21));
      $item = new Record_TestProxy();
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
        21, $item->_insertRecord('table_sample_table', 'id')
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::_insertRecord
     */
    public function testInsertRecordWithoutId() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with(
          'table_sample_table',
          NULL,
          array(
            'sample_id' => 23,
            'sample_title' => 'title text'
          )
        )
        ->will($this->returnValue(TRUE));
      $item = new Record_TestProxy();
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
        $item->_insertRecord('table_sample_table', NULL)
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::_updateRecord
     */
    public function testUpdateRecord() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('updateRecord')
        ->with('table_sample_table', array('sample_title' => 'title text'), array('sample_id' => 23))
        ->will($this->returnValue(1));
      $item = new Record_TestProxy();
      $item->_fields = array(
        'id' => 'sample_id',
        'title' => 'sample_title'
      );
      $item->_values = array(
        'title' => 'title text'
      );
      $item->setDatabaseAccess($databaseAccess);
      $this->assertTrue($item->_updateRecord('table_sample_table', array('sample_id' => 23)));
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::delete
     * @covers \Papaya\Database\BaseObject\Record::_deleteRecord
     */
    public function testDelete() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('deleteRecord')
        ->with('table_sample_table', array('sample_id' => 42))
        ->will($this->returnValue(1));
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::delete
     * @covers \Papaya\Database\BaseObject\Record::_deleteRecord
     */
    public function testDeleteWithoutIdExpectingFalse() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->never())
        ->method('deleteRecord');
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::_applyCallback
     */
    public function testApplyCallback() {
      $item = new Record_TestProxy();
      $this->assertEquals(
        array('test' => 'success'),
        $item->_applyCallback(array($this, 'callbackForApplyCallback'), NULL, array('test' => ''))
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::_applyCallback
     */
    public function testApplyCallbackWithDefaultCallback() {
      $item = new Record_TestProxy();
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
     * @covers \Papaya\Database\BaseObject\Record::_applyCallback
     */
    public function testApplyCallbackWithoutCallbackExpectingException() {
      $item = new Record_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      $item->_applyCallback(NULL, NULL, array());
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::convertRecordToValues
     */
    public function testConvertRecordToValues() {
      $row = array(
        'fieldname1' => 'success',
        'fieldname2' => 'failed',
      );
      $record = new Record_TestProxy();
      $this->assertEquals(
        array('field1' => 'success'),
        $record->convertRecordToValues($row)
      );
    }

    /**
     * @covers \Papaya\Database\BaseObject\Record::convertValuesToRecord
     */
    public function testConvertValuesToRecord() {
      $data = array(
        'field1' => 'success',
        'field2' => 'failed',
      );
      $record = new Record_TestProxy();
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
   *
   * @property string field1
   * @property string field2
   */
  class Record_TestProxy extends Record {

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
}

