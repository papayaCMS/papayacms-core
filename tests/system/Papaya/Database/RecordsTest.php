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

/** @noinspection PhpIllegalArrayKeyTypeInspection */
require_once __DIR__.'/../../../bootstrap.php';

class PapayaDatabaseRecordsTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecords::load
  * @covers PapayaDatabaseRecords::_loadRecords
  * @covers PapayaDatabaseRecords::getIdentifier
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 42,
            'field_data' => 'Sample Content'
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue(" field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $records = new PapayaDatabaseRecords_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load(42));
    $this->assertEquals(
      array(array('id' => 42, 'data' => 'Sample Content')),
      $records->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecords::load
  * @covers PapayaDatabaseRecords::_loadRecords
  */
  public function testLoadWithEmptyResult() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $records = new PapayaDatabaseRecords_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load());
    $this->assertEquals(
      array(),
      $records->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecords::load
  * @covers PapayaDatabaseRecords::_loadRecords
  */
  public function testLoadWithoutConditions() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 42,
            'field_data' => 'Sample Content'
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $records = new PapayaDatabaseRecords_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load());
    $this->assertEquals(
      array(array('id' => 42, 'data' => 'Sample Content')),
      $records->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecords::load
  * @covers PapayaDatabaseRecords::_loadRecords
  */
  public function testLoadWithoutConditionsWithOrderBy() {
    $orderBy = $this->createMock(PapayaDatabaseInterfaceOrder::class);
    $orderBy
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('>>ORDERBY<<'));
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 42,
            'field_data' => 'Sample Content'
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->stringContains('>>ORDERBY<<'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $records = new PapayaDatabaseRecords_TestProxy();
    $records->orderBy($orderBy);
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load());
  }

  /**
  * @covers PapayaDatabaseRecords::load
  * @covers PapayaDatabaseRecords::_loadRecords
  */
  public function testLoadExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue(" field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue(FALSE));
    $records = new PapayaDatabaseRecords_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertFalse($records->load(42));
  }

  /**
  * @covers PapayaDatabaseRecords::load
  * @covers PapayaDatabaseRecords::_loadRecords
  * @covers PapayaDatabaseRecords::getIdentifier
  */
  public function testLoadWithIdentifierField() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 42,
            'field_data' => 'Sample Content'
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue(" field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $records = new PapayaDatabaseRecords_TestProxy();
    $records->_identifierProperties = 'id';
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load(42));
    $this->assertEquals(
      array(42 => array('id' => 42, 'data' => 'Sample Content')),
      $records->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecords::load
  * @covers PapayaDatabaseRecords::_loadRecords
  * @covers PapayaDatabaseRecords::getIdentifier
  */
  public function testLoadWithInvalidIdentifierFields() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 42,
            'field_data' => 'Sample Content'
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue(" field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $records = new PapayaDatabaseRecords_TestProxy();
    $records->_identifierProperties = array('id', 'invalid');
    $records->setDatabaseAccess($databaseAccess);

    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('The property "invalid" was not found, but is needed to create the identifier.');
    $records->load(42);
  }

  /**
  * @covers PapayaDatabaseRecords::reset
  */
  public function testResetAfterLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 42,
            'field_data' => 'Sample Content'
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue(" field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $records = new PapayaDatabaseRecords_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->load(42);
    $records->reset();
    $this->assertEquals(
      array(),
      $records->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecords::offsetExists
  * @covers PapayaDatabaseRecords::getIdentifier
  */
  public function testOffsetExistsExpectingTrue() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $records[array(21, 42)] = array('id' => 42, 'data' => 'Hello World');
    $this->assertTrue(
      isset($records[array(21, 42)])
    );
  }

  /**
  * @covers PapayaDatabaseRecords::offsetExists
  * @covers PapayaDatabaseRecords::getIdentifier
  */
  public function testOffsetExistsExpectingFalse() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $this->assertFalse(
      isset($records[array(21, 42)])
    );
  }

  /**
  * @covers PapayaDatabaseRecords::offsetGet
  * @covers PapayaDatabaseRecords::offsetSet
  * @covers PapayaDatabaseRecords::getIdentifier
  */
  public function testOffsetGetAfterSet() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $records[42] = array('id' => 42, 'data' => 'Hello World');
    $this->assertEquals(
      array('id' => 42, 'data' => 'Hello World'),
      $records[42]
    );
  }

  /**
  * @covers PapayaDatabaseRecords::offsetGet
  * @covers PapayaDatabaseRecords::offsetSet
  * @covers PapayaDatabaseRecords::getIdentifier
  */
  public function testOffsetGetAfterSetWithArray() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $records[array(21, 42)] = array('id' => 42, 'data' => 'Hello World');
    $this->assertEquals(
      array('id' => 42, 'data' => 'Hello World'),
      $records[array(21, 42)]
    );
  }

  /**
  * @covers PapayaDatabaseRecords::offsetGet
  * @covers PapayaDatabaseRecords::offsetSet
  * @covers PapayaDatabaseRecords::getIdentifier
  */
  public function testOffsetGetAfterSetWithNull() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $records[] = array('id' => 42, 'data' => '');
    $records[] = array('id' => 21, 'data' => 'Hello World');
    $this->assertEquals(
      array('id' => 21, 'data' => 'Hello World'),
      $records[1]
    );
  }

  /**
  * @covers PapayaDatabaseRecords::offsetSet
  */
  public function testOffsetSetWithUnknownKeysInArray() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $records[23] = array('id' => 23, 'unknown_key' => 'failed');
    $this->assertEquals(
      array('id' => 23, 'data' => NULL),
      $records[23]
    );
  }

  /**
  * @covers PapayaDatabaseRecords::offsetUnset
  */
  public function testOffsetUnset() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $records[array(21, 42)] = array('id' => 42, 'data' => 'Hello World');
    unset($records[array(21, 42)]);
    $this->assertFalse(
      isset($records[array(21, 42)])
    );
  }

  /**
  * @covers PapayaDatabaseRecords::getIterator
  */
  public function testGetIterator() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $records[42] = array('id' => 42, 'data' => 'Hello World');
    $iterator = $records->getIterator();
    $this->assertEquals(
      array(42 => array('id' => 42, 'data' => 'Hello World')),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaDatabaseRecords::getIterator
  */
  public function testGetIteratorWithoutRecords() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $iterator = $records->getIterator();
    $this->assertEquals(
      array(),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaDatabaseRecords::toArray
  */
  public function testToArray() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $records[42] = array('id' => 42, 'data' => 'Hello World');
    $this->assertEquals(
      array(42 => array('id' => 42, 'data' => 'Hello World')),
      $records->toArray()
    );
  }

  /**
  * @covers PapayaDatabaseRecords::count
  */
  public function testCount() {
    $records = new PapayaDatabaseRecords_TestProxy();
    $records[] = array('id' => 21, 'data' => 'Hello World');
    $records[] = array('id' => 42, 'data' => 'Hello World');
    $this->assertCount(2, $records);
  }
}

class PapayaDatabaseRecords_TestProxy extends PapayaDatabaseRecords {

  public $_fields = array(
    'id' => 'field_id',
    'data' => 'field_data'
  );

  public $_orderByFields = array();

  protected $_tableName = 'tablename';

  public /** @noinspection PropertyInitializationFlawsInspection */
    $_identifierProperties = array();
}
