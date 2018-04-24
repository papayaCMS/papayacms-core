<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseObjectListTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseObjectList::getIterator
  */
  public function testGetIterator() {
    $list = new PapayaDatabaseObjectList_TestProxy();
    $iterator = $list->getIterator();
    $this->assertEquals(
      array(
        array(
          'property1' => '1_1',
          'property2' => '1_2'
        ),
        array(
          'property1' => '2_1',
          'property2' => '2_2'
        )
      ),
      $iterator->getArrayCopy()
    );
  }

  /**
  * @covers PapayaDatabaseObjectList::count
  */
  public function testCount() {
    $list = new PapayaDatabaseObjectList_TestProxy();
    $this->assertEquals(
      2, count($list)
    );
  }

  /**
  * @covers PapayaDatabaseObjectList::countAll
  */
  public function testCountAll() {
    $list = new PapayaDatabaseObjectList_TestProxy();
    $this->assertEquals(
      2, $list->countAll()
    );
  }

  /**
  * @covers PapayaDatabaseObjectList::countAll
  */
  public function testCountAllReturnsAbsoluteCount() {
    $list = new PapayaDatabaseObjectList_TestProxy();
    $list->_recordCount = 42;
    $this->assertEquals(
      42, $list->countAll()
    );
  }

  /**
  * @covers PapayaDatabaseObjectList::item
  */
  public function testItemExpectingArray() {
    $list = new PapayaDatabaseObjectList_TestProxy();
    $this->assertEquals(
      array(
        'property1' => '1_1',
        'property2' => '1_2'
      ),
      $list->item(0)
    );
  }

  /**
  * @covers PapayaDatabaseObjectList::item
  */
  public function testItemExpectingNull() {
    $list = new PapayaDatabaseObjectList_TestProxy();
    $this->assertNull($list->item(-99));
  }

  /**
  * @covers PapayaDatabaseObjectList::itemAt
  */
  public function testItemAtExpectingArray() {
    $list = new PapayaDatabaseObjectList_TestProxy();
    $this->assertEquals(
      array(
        'property1' => '1_1',
        'property2' => '1_2'
      ),
      $list->itemAt(0)
    );
  }

  /**
  * @covers PapayaDatabaseObjectList::itemAt
  */
  public function testItemAtNegativePositionExpectingArray() {
    $list = new PapayaDatabaseObjectList_TestProxy();
    $this->assertEquals(
      array(
        'property1' => '2_1',
        'property2' => '2_2'
      ),
      $list->itemAt(-1)
    );
  }

  /**
  * @covers PapayaDatabaseObjectList::assign
  */
  public function testAssign() {
    $list = new PapayaDatabaseObjectList_TestProxy();
    $list->assign(
      array(
        '21' => array(
          'property1' => '1.1',
          'property2' => '1.2',
          'property_unknown' => 'failed'
        ),
        '42' => array(
          'property1' => '2.1',
          'property2' => '2.2',
          'property_unknown' => 'failed'
        )
      )
    );
    $this->assertAttributeEquals(
      array(
        '21' => array(
          'property1' => '1.1',
          'property2' => '1.2'
        ),
        '42' => array(
          'property1' => '2.1',
          'property2' => '2.2'
        )
      ),
      '_records',
      $list
    );
    $this->assertEquals(2, $list->count());
  }

  /**
  * @covers PapayaDatabaseObjectList::_loadRecords
  */
  public function testLoadRecords() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(PapayaDatabaseResult::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array('field1' => 'Hello', 'field2' => 'World'),
          FALSE
        )
      );
    $databaseResult
      ->expects($this->any())
      ->method('absCount')
      ->will($this->returnValue(42));
    $databaseAccess = $this->getMock(
      PapayaDatabaseAccess::class, array('queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->equalTo('SQL'),
        $this->equalTo(array('1', '2')),
        $this->equalTo(10),
        $this->equalTo(5)
      )
      ->will($this->returnValue($databaseResult));
    $list = new PapayaDatabaseObjectList_TestProxy();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->_loadRecords('SQL', array('1', '2'), 'field1', 10, 5));
    $this->assertAttributeEquals(
      array('Hello' => array('property1' => 'Hello', 'property2' => 'World')),
      '_records',
      $list
    );
    $this->assertAttributeEquals(
      42, '_recordCount', $list
    );
  }

  /**
  * @covers PapayaDatabaseObjectList::_loadRecords
  */
  public function testLoadRecordsExpectingFalse() {
    $databaseAccess = $this->getMock(
      PapayaDatabaseAccess::class, array('queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->equalTo('SQL'),
        $this->equalTo(array('1', '2')),
        $this->equalTo(NULL),
        $this->equalTo(NULL)
      )
      ->will($this->returnValue(FALSE));
    $list = new PapayaDatabaseObjectList_TestProxy();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertFalse($list->_loadRecords('SQL', array('1', '2')));
    $this->assertAttributeEquals(array(), '_records', $list);
    $this->assertAttributeEquals(0, '_recordCount', $list);
  }

  /**
  * @covers PapayaDatabaseObjectList::_fetchRecords
  */
  public function testFetchRecords() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(PapayaDatabaseResult::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array('field1' => 'Hello', 'field2' => 'World'),
          FALSE
        )
      );
    $list = new PapayaDatabaseObjectList_TestProxy();
    $list->_fetchRecords($databaseResult);
    $this->assertAttributeEquals(
      array(array('property1' => 'Hello', 'property2' => 'World')),
      '_records',
      $list
    );
  }

  /**
  * @covers PapayaDatabaseObjectList::_fetchRecords
  */
  public function testFetchRecordsWithIndex() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(PapayaDatabaseResult::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array('field1' => 'Hello', 'field2' => 'World'),
          FALSE
        )
      );
    $list = new PapayaDatabaseObjectList_TestProxy();
    $list->_fetchRecords($databaseResult, 'field1');
    $this->assertAttributeEquals(
      array('Hello' => array('property1' => 'Hello', 'property2' => 'World')),
      '_records',
      $list
    );
  }
}

/**
* Proxy class with some predefined values
*/
class PapayaDatabaseObjectList_TestProxy extends PapayaDatabaseObjectList {

  protected $_records = array(
    array(
      'property1' => '1_1',
      'property2' => '1_2'
    ),
    array(
      'property1' => '2_1',
      'property2' => '2_2'
    )
  );

  protected $_fieldMapping = array(
    'field1' => 'property1',
    'field2' => 'property2'
  );

  public $_recordCount = 0;

  public function _loadRecords($sql, $parameters, $idProperty = NULL,
                               $limit = NULL, $offset = NULL) {
    return parent::_loadRecords($sql, $parameters, $idProperty, $limit, $offset);
  }

  public function _fetchRecords($databaseResult, $idField = '') {
    parent::_fetchRecords($databaseResult, $idField);
  }
}

