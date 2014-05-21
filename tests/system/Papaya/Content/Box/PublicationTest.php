<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaContentBoxPublicationTest extends PapayaTestCase {

  /**
  * @covers PapayaContentBoxPublication::save
  */
  public function testSaveCreateNew() {
    $databaseResult = $this->getMock(
      'PapayaDatabaseAccess', array('fetchField'), array(new stdClass)
    );
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'queryFmt', 'insertRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        $this->equalTo(array('box_public', 42))
      )
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with(
        $this->equalTo('box_public'),
        $this->isNull(),
        $this->isType('array')
      )
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $box = new PapayaContentBoxPublication();
    $box->papaya($this->mockPapaya()->application());
    $box->setDatabaseAccess($databaseAccess);
    $box->assign(
      array(
        'id' => 42,
        'name' => 'Box Name',
        'group_id' => 21,
        'created' => 0,
        'modified' => 0,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0
      )
    );
    $this->assertTrue($box->save());
  }

  public function checkInsertData($table, $idField, $data) {
    $this->assertEquals(42, $data['box_id']);
    $this->assertEquals(21, $data['boxgroup_id']);
    $this->assertGreaterThan(0, $data['box_created']);
    $this->assertGreaterThan(0, $data['box_modified']);
    $this->assertEquals(PapayaContentOptions::CACHE_SYSTEM, $data['box_cachemode']);
    $this->assertEquals(0, $data['box_cachetime']);
    return TRUE;
  }

  /**
  * @covers PapayaContentBoxPublication::save
  */
  public function testSaveUpdateExisting() {
    $databaseResult = $this->getMock(
      'PapayaDatabaseAccess', array('fetchField'), array(new stdClass)
    );
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'queryFmt', 'updateRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        $this->equalTo(array('box_public', 42))
      )
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        $this->equalTo('box_public'),
        $this->isType('array'),
        $this->equalTo(array('box_id' => 42))
      )
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $box = new PapayaContentBoxPublication();
    $box->papaya($this->mockPapaya()->application());
    $box->setDatabaseAccess($databaseAccess);
    $box->assign(
      array(
        'id' => 42,
        'name' => 'Box Name',
        'group_id' => 21,
        'created' => 123,
        'modified' => 0,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0
      )
    );
    $this->assertTrue($box->save());
  }

  public function checkUpdateData($table, $data, $filter) {
    $this->assertEquals('Box Name', $data['box_name']);
    $this->assertEquals(21, $data['boxgroup_id']);
    $this->assertEquals(123, $data['box_created']);
    $this->assertGreaterThan(1, $data['box_modified']);
    $this->assertEquals(PapayaContentOptions::CACHE_SYSTEM, $data['box_cachemode']);
    $this->assertEquals(0, $data['box_cachetime']);
    return 42;
  }

  /**
  * @covers PapayaContentBoxPublication::save
  */
  public function testSaveWithoutIdExpectingFalse() {
    $box = new PapayaContentBoxPublication();
    $this->assertFalse($box->save());
  }

  /**
  * @covers PapayaContentBoxPublication::save
  */
  public function testSaveWithSqlErrorOnCheckExistingExpectingFalse() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getTableName', 'queryFmt', 'updateRecord'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        $this->equalTo(array('box_public', 42))
      )
      ->will($this->returnValue(FALSE));
    $page = new PapayaContentBoxPublication();
    $page->papaya($this->mockPapaya()->application());
    $page->setDatabaseAccess($databaseAccess);
    $page->assign(
      array(
        'id' => 42
      )
    );
    $this->assertFalse($page->save());
  }
}