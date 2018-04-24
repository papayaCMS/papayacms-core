<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordsGroupedTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordsGrouped::_loadRecords
  * @covers PapayaDatabaseRecordsGrouped::getIterator
  */
  public function testLoadAndIterateRootWithoutIdentifier() {
    $records = new PapayaDatabaseRecordsGrouped_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->assertTrue($records->load());
    $this->assertEquals(
      array(
        0 => new ArrayObject(
          array(
            array(
              'id' => 1,
              'group_id' => 0,
              'title' => 'One'
            ),
            array(
              'id' => 2,
              'group_id' => 0,
              'title' => 'Two'
            )
          )
        ),
        1 => new ArrayObject(
          array(
            array(
              'id' => 3,
              'group_id' => 1,
              'title' => 'Tree'
            )
          )
        )
      ),
      iterator_to_array($records)
    );
  }

  /**
  * @covers PapayaDatabaseRecordsGrouped::_loadRecords
  * @covers PapayaDatabaseRecordsGrouped::getIterator
  */
  public function testLoadAndIterateRootWithIdentifier() {
    $records = new PapayaDatabaseRecordsGrouped_TestProxy();
    $records->_identifierProperties = array('id');
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->assertTrue($records->load());
    $this->assertEquals(
      array(
        0 => new ArrayObject(
          array(
            1 => array(
              'id' => 1,
              'group_id' => 0,
              'title' => 'One'
            ),
            2 => array(
              'id' => 2,
              'group_id' => 0,
              'title' => 'Two'
            )
          )
        ),
        1 => new ArrayObject(
          array(
            3 => array(
              'id' => 3,
              'group_id' => 1,
              'title' => 'Tree'
            )
          )
        )
      ),
      iterator_to_array($records)
    );
  }

  /**
  * @covers PapayaDatabaseRecordsGrouped::_loadRecords
  * @covers PapayaDatabaseRecordsGrouped::getIterator
  */
  public function testLoadWithInvalidIdentifierExpectingException() {
    $records = new PapayaDatabaseRecordsGrouped_TestProxy();
    $records->_groupIdentifierProperties = array();
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->setExpectedException(
      'LogicException',
      'Properties needed to group records.'
    );
    $records->load();
  }

  /**
  * @covers PapayaDatabaseRecordsGrouped::load
  * @covers PapayaDatabaseRecordsGrouped::_loadRecords
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
      ->will($this->returnValue(" field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('tablename')
      )
      ->will($this->returnValue(FALSE));
    $records = new PapayaDatabaseRecordsGrouped_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertFalse($records->load(42));
  }

  /************************
  * Fixtures
  ************************/

  public function getDatabaseFixture() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 1,
            'field_group_id' => 0,
            'field_title' => 'One'
          ),
          array(
            'field_id' => 2,
            'field_group_id' => 0,
            'field_title' => 'Two'
          ),
          array(
            'field_id' => 3,
            'field_group_id' => 1,
            'field_title' => 'Tree'
          )
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('tablename')
      )
      ->will($this->returnValue($databaseResult));
    return $databaseAccess;
  }
}

class PapayaDatabaseRecordsGrouped_TestProxy extends PapayaDatabaseRecordsGrouped {

  public $_identifierProperties = array();

  public $_groupIdentifierProperties = array('group_id');

  protected $_fields = array(
    'id' => 'field_id',
    'group_id' => 'field_group_id',
    'title' => 'field_title'
  );

  protected $_tableName = 'tablename';
}
