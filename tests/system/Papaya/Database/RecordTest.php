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

use Papaya\Database\Condition\Element;
use Papaya\Database\Condition\Root;
use Papaya\Database\Interfaces\Key;
use Papaya\Database\Record\Key\Autoincrement;
use Papaya\Database\Record\Callbacks;

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
    $record->key($this->createMock(Key::class));
    $record->mapping($this->createMock(Papaya\Database\Interfaces\Mapping::class));
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
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
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
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
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        /** @lang Text */'SELECT field_id, field_data FROM %s ',
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        /** @lang Text */'SELECT field_id, field_data FROM %s ',
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->setDatabaseAccess($databaseAccess);
    $this->assertFalse($record->load(array()));
  }

  /**
  * @covers PapayaDatabaseRecord::load
  * @covers PapayaDatabaseRecord::_loadRecord
  * @covers PapayaDatabaseRecord::_compileCondition
  */
  public function testLoadExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
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
        array('table_tablename')
      )
      ->will($this->returnValue(NULL));
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('getSqlCondition');
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $condition = $this
      ->getMockBuilder(Element::class)
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $mapping = $this
      ->getMockBuilder(Papaya\Database\Interfaces\Mapping::class)
      ->getMock();
    $records = new PapayaDatabaseRecord_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->mapping($mapping);
    $filter = $records->createFilter();
    $this->assertInstanceOf(Root::class, $filter);
    $this->assertSame($databaseAccess, $filter->getDatabaseAccess());
    $this->assertSame($mapping, $filter->getMapping());
  }

  /**
  * @covers PapayaDatabaseRecord::isLoaded
  */
  public function testIsLoadedExpectingFalse() {
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        /** @lang Text */'SELECT field_id, field_data FROM %s ',
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->setDatabaseAccess($databaseAccess);
    $record->load(array());
    $this->assertTrue($record->isLoaded());
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_insertRecord
  */
  public function testSaveInsertsRecordUsingDefaultAutoincrement() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('table_tablename', 'field_id', array('field_data' => 'inserted'))
      ->will($this->returnValue(42));
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('table_tablename', 'field_id', array('field_data' => 'before insert'))
      ->will($this->returnValue(42));
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->setDatabaseAccess($databaseAccess);
    $record->assign(
      array('data' => 'inserted')
    );
    $record->callbacks()->onBeforeInsert = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, PapayaDatabaseRecord_TestProxy $record
    ) {
      $record->data = 'before insert';
      return TRUE;
    };
    $this->assertEquals(array('id' => 42), $record->save()->getFilter());
    $this->assertEquals('before insert', $record->data);
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_insertRecord
  */
  public function testSaveInsertsRecordBlockedByCallback() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('insertRecord');
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $key = $this->createMock(Key::class);
    $key
      ->expects($this->any())
      ->method('exists')
      ->will($this->returnValue(FALSE));
    $key
      ->expects($this->once())
      ->method('getFilter')
      ->with(Key::ACTION_CREATE)
      ->will($this->returnValue(array('id' => 'truth')));
    $key
      ->expects($this->any())
      ->method('getQualities')
      ->will($this->returnValue(0));

    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('table_tablename', NULL, array('field_id' => 'truth', 'field_data' => 'inserted'))
      ->will($this->returnValue(TRUE));

    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with('table_tablename', 'field_id', array('field_data' => 'inserted'))
      ->will($this->returnValue(FALSE));
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'table_tablename',
        array('field_data' => 'updated', 'field_id' => 42),
        array('field_id' => 42))
      ->will($this->returnValue(42));
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'table_tablename',
        array('field_data' => 'before update', 'field_id' => 42),
        array('field_id' => 42))
      ->will($this->returnValue(42));
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->setDatabaseAccess($databaseAccess);
    $record->assign(
      $values = array(
        'data' => 'updated',
        'id' => 42
      )
    );
    $record->key()->assign($values);
    $record->callbacks()->onBeforeUpdate = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, PapayaDatabaseRecord_TestProxy $record
    ) {
      $record->data = 'before update';
      return TRUE;
    };
    $this->assertTrue($record->save());
    $this->assertEquals('before update', $record->data);
  }

  /**
  * @covers PapayaDatabaseRecord::save
  * @covers PapayaDatabaseRecord::_updateRecord
  */
  public function testSaveUpdatesRecordBlockedByCallback() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('updateRecord');
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with(
        'table_tablename',
        array('field_id' => 42))
      ->will($this->returnValue(1));
    $record = new PapayaDatabaseRecord_TestProxy();
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
    $key = $this->createMock(Key::class);
    $key
      ->expects($this->any())
      ->method('getFilter')
      ->will($this->returnValue(array()));
    $record = new PapayaDatabaseRecord_TestProxy();
    $record->key($key);
    $this->assertFalse($record->delete());
  }

  /**
  * @covers PapayaDatabaseRecord::mapping
  */
  public function testMappingGetAfterSet() {
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
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
      Papaya\Database\Record\Mapping::class, $record->mapping()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::key
  */
  public function testKeyGetAfterSet() {
    $key = $this->createMock(Key::class);
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
      Autoincrement::class, $record->key()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::setDatabaseAccess
  * @covers PapayaDatabaseRecord::getDatabaseAccess
  */
  public function testGetDatabaseAccessAfterSet() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
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
      PapayaDatabaseAccess::class, $record->getDatabaseAccess()
    );
    $this->assertSame(
      $record->papaya(), $record->getDatabaseAccess()->papaya()
    );
  }

  /**
  * @covers PapayaDatabaseRecord::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(Callbacks::class);
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
    $this->assertInstanceOf(Callbacks::class, $record->callbacks());
  }
}

/**
 * @property $id
 * @property $data
 */
class PapayaDatabaseRecord_TestProxy extends PapayaDatabaseRecord {

  protected $_fields = array(
    'id' => 'field_id',
    'data' => 'field_data'
  );

  protected $_tableName = 'tablename';
}
