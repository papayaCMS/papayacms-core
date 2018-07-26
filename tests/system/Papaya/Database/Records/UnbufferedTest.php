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
use Papaya\Database\Interfaces\Order;
use Papaya\Database\Records\Unbuffered;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordsUnbufferedTest extends PapayaTestCase {

  /**
  * @covers Unbuffered::load
  * @covers Unbuffered::_loadSql
  * @covers Unbuffered::_compileCondition
  * @covers Unbuffered::_compileOrderBy
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
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
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load(42));
  }

  /**
  * @covers Unbuffered::load
  * @covers Unbuffered::_loadSql
  * @covers Unbuffered::_compileCondition
  * @covers Unbuffered::_compileOrderBy
  * @see https://bugs.papaya-cms.com/view.php?id=2982 Reason for checking if SQL contains WHERE
  */
  public function testLoadWithConditionObject() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->never())
      ->method('getSqlCondition');
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->logicalAnd(
          $this->isType('string'),
          $this->matchesRegularExpression('/.+ WHERE .+/i')
        ),
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

    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load($condition));
  }

  /**
  * @covers Unbuffered::load
  * @covers Unbuffered::_loadSql
  * @covers Unbuffered::_compileCondition
  * @covers Unbuffered::_compileOrderBy
  */
  public function testLoadWithoutConditions() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load());
  }

  /**
  * @covers Unbuffered::load
  * @covers Unbuffered::_loadSql
  * @covers Unbuffered::_compileCondition
  * @covers Unbuffered::_compileOrderBy
  */
  public function testLoadWithoutConditionsWithOrderBy() {
    $orderBy = $this->createMock(Order::class);
    $orderBy
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('>>ORDERBY<<'));
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->stringContains('>>ORDERBY<<'),
        array('table_tablename')
      )
      ->will($this->returnValue($databaseResult));
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->orderBy($orderBy);
    $records->setDatabaseAccess($databaseAccess);
    $this->assertTrue($records->load());
  }

  /**
  * @covers Unbuffered::load
  * @covers Unbuffered::_loadSql
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
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertFalse($records->load(42));
  }

  /**
  * @covers Unbuffered::createFilter
  */
  public function testCreateFilter() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $mapping = $this
      ->getMockBuilder(Papaya\Database\Interfaces\Mapping::class)
      ->getMock();
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $records->mapping($mapping);
    $filter = $records->createFilter();
    $this->assertInstanceOf(Root::class, $filter);
    $this->assertSame($databaseAccess, $filter->getDatabaseAccess());
    $this->assertSame($mapping, $filter->getMapping());
  }

  /**
  * @covers Unbuffered::count
  */
  public function testCount() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(3));
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->databaseResult($databaseResult);
    $this->assertEquals(3, $records->count());
  }

  /**
  * @covers Unbuffered::count
  */
  public function testCountWihtoutDatabaseResultExpectingZero() {
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $this->assertEquals(0, $records->count());
  }

  /**
  * @covers Unbuffered::absCount
  */
  public function testAbsCount() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('absCount')
      ->will($this->returnValue(7));
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->databaseResult($databaseResult);
    $this->assertEquals(7, $records->absCount());
  }

  /**
  * @covers Unbuffered::absCount
  */
  public function testAbsCountWihtoutDatabaseResultExpectingZero() {
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $this->assertEquals(0, $records->absCount());
  }

  /**
  * @covers Unbuffered::toArray
  * @covers Unbuffered::getIterator
  * @covers Unbuffered::getResultIterator
  */
  public function testToArrayUsingGetIterator() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 21,
            'field_data' => 'row 1'
          ),
          array(
            'field_id' => 42,
            'field_data' => 'row 2'
          ),
          NULL
        )
      );
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->databaseResult($databaseResult);
    $this->assertEquals(
      array(
        array(
          'id' => 21,
          'data' => 'row 1'
        ),
        array(
          'id' => 42,
          'data' => 'row 2'
        ),
      ),
      $records->toArray()
    );
  }

  /**
  * @covers Unbuffered::toArray
  * @covers Unbuffered::getIterator
  * @covers Unbuffered::getResultIterator
  */
  public function testToArrayWithEmptyResultUsingGetIterator() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          NULL
        )
      );
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->databaseResult($databaseResult);
    $this->assertEquals(
      array(),
      $records->toArray()
    );
  }

  /**
  * @covers Unbuffered::getIterator
  * @covers Unbuffered::getResultIterator
  */
  public function testGetIteratorWithoutResultEmptytingEmpty() {
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $this->assertInstanceOf('EmptyIterator', $records->getIterator());
  }

  /**
  * @covers Unbuffered::mapping
  */
  public function testMappingGetAfterSet() {
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->mapping($mapping);
    $this->assertSame(
      $mapping, $records->mapping()
    );
  }

  /**
  * @covers Unbuffered::mapping
  * @covers Unbuffered::_createMapping
  */
  public function testMappingGetImplicitCreate() {
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $this->assertInstanceOf(
      Papaya\Database\Record\Mapping::class, $records->mapping()
    );
  }

  /**
  * @covers Unbuffered::orderBy
  */
  public function testOrderByGetAfterSet() {
    $orderBy = $this->createMock(Order::class);
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->orderBy($orderBy);
    $this->assertSame(
      $orderBy, $records->orderBy()
    );
  }

  /**
  * @covers Unbuffered::orderBy
  * @covers Unbuffered::_createOrderBy
  */
  public function testOrderByGetImplicitCreateExpectingEmpty() {
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $this->assertFalse($records->orderBy());
  }

  /**
  * @covers Unbuffered::orderBy
  * @covers Unbuffered::_createOrderBy
  */
  public function testOrderByGetImplicitCreateWithField() {
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->_orderByFields = array('fieldname' => Order::ASCENDING);
    $this->assertEquals(
      'fieldname ASC',
      (string)$records->orderBy()
    );
  }

  /**
  * @covers Unbuffered::orderBy
  * @covers Unbuffered::_createOrderBy
  */
  public function testOrderByGetImplicitCreateWithTwoFields() {
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->_orderByFields = array(
      'field_one' => Order::DESCENDING,
      'field_two' => Order::ASCENDING
    );
    $this->assertEquals(
      'field_one DESC, field_two ASC',
      (string)$records->orderBy()
    );
  }

  /**
  * @covers Unbuffered::orderBy
  * @covers Unbuffered::_createOrderBy
  */
  public function testOrderByGetImplicitCreateWithProperty() {
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('name')
      ->will($this->returnValue('fieldname'));
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->mapping($mapping);
    $records->_orderByProperties = array('name' => Order::ASCENDING);
    $this->assertEquals(
      'fieldname ASC',
      (string)$records->orderBy()
    );
  }

  /**
  * @covers Unbuffered::orderBy
  * @covers Unbuffered::_createOrderBy
  */
  public function testOrderByGetImplicitCreateWithTwoProperties() {
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->any())
      ->method('getField')
      ->will(
        $this->returnValueMap(
          array(
            array('one', TRUE, 'field_one'),
            array('two', TRUE, 'field_two')
          )
        )
      );
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->mapping($mapping);
    $records->_orderByProperties = array(
      'one' => Order::ASCENDING,
      'two' => Order::DESCENDING
    );
    $this->assertEquals(
      'field_one ASC, field_two DESC',
      (string)$records->orderBy()
    );
  }

  /**
  * @covers Unbuffered::orderBy
  * @covers Unbuffered::_createOrderBy
  */
  public function testOrderByGetImplicitCreateWithPropertiesAndFields() {
    $mapping = $this->createMock(Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->any())
      ->method('getField')
      ->will(
        $this->returnValueMap(
          array(
            array('one', TRUE, 'field_one'),
            array('two', TRUE, NULL)
          )
        )
      );
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->mapping($mapping);
    $records->_orderByProperties = array(
      'one' => Order::ASCENDING,
      'two' => Order::DESCENDING
    );
    $records->_orderByFields = array(
      'field_three' => Order::ASCENDING,
      'field_four' => Order::DESCENDING
    );
    $this->assertEquals(
      'field_one ASC, field_three ASC, field_four DESC',
      (string)$records->orderBy()
    );
  }

  /**
  * @covers Unbuffered::databaseResult
  */
  public function testDatabaseResultGetAfterSet() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->databaseResult($databaseResult);
    $this->assertSame(
      $databaseResult, $records->databaseResult()
    );
  }

  /**
  * @covers Unbuffered::setDatabaseAccess
  * @covers Unbuffered::getDatabaseAccess
  */
  public function testGetDatabaseAccessAfterSet() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess, $records->getDatabaseAccess()
    );
  }

  /**
  * @covers Unbuffered::getDatabaseAccess
  */
  public function testGetDatabaseAccessImplicitCreate() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseManager = $this->createMock(PapayaDatabaseManager::class);
    $databaseManager
      ->expects($this->any())
      ->method('createDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->papaya(
      $this->mockPapaya()->application(
        array('database' => $databaseManager)
      )
    );
    $this->assertEquals(
      $databaseAccess, $records->getDatabaseAccess()
    );
  }

  /**
   * @covers Unbuffered::_createItem
   * @covers Unbuffered::getItem
   */
  public function testGetItemExpectingException() {
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $this->expectException(LogicException::class);
    $records->getItem();
  }

  /**
   * @covers Unbuffered::_createItem
   * @covers Unbuffered::getItem
   */
  public function testGetItem() {
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy();
    $records->_itemClass = \PapayaDatabaseRecordsUnbuffered_TestItemProxy::class;
    $this->assertInstanceOf(PapayaDatabaseRecordsUnbuffered_TestItemProxy::class, $records->getItem());
  }

  /**
   * @covers Unbuffered::getItem
   */
  public function testGetItemWithFilterCallingLoad() {
    $record = $this->createMock(PapayaDatabaseRecord::class);
    $record
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => '42'));
    $records = new \PapayaDatabaseRecordsUnbuffered_TestProxy;
    $records->item = $record;
    $records->getItem(array('id' => '42'));
  }
}

class PapayaDatabaseRecordsUnbuffered_TestProxy extends Unbuffered {

  public $_fields = array(
    'id' => 'field_id',
    'data' => 'field_data'
  );

  public $_orderByFields = array();
  public $_orderByProperties = array();

  protected $_tableName = 'tablename';

  public $_itemClass;
  public $item;

  public function _createItem() {
    if (NULL !== $this->item) {
      return $this->item;
    }
    return parent::_createItem();
  }
}

class PapayaDatabaseRecordsUnbuffered_TestItemProxy extends PapayaDatabaseRecord {

}
