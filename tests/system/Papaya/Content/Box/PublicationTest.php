<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentBoxPublicationTest extends PapayaTestCase {

  /**
  * @covers PapayaContentBoxPublication::save
  */
  public function testSaveCreateNew() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseAccess $databaseAccess */
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->setMethods(array('getTableName', 'queryFmt', 'insertRecord'))
      ->setConstructorArgs(array(new stdClass))
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
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
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
    $this->assertEquals('box_public', $table);
    $this->assertNull($idField);
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
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
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
    $this->assertEquals('box_public', $table);
    $this->assertEquals('Box Name', $data['box_name']);
    $this->assertEquals(21, $data['boxgroup_id']);
    $this->assertEquals(123, $data['box_created']);
    $this->assertGreaterThan(1, $data['box_modified']);
    $this->assertEquals(PapayaContentOptions::CACHE_SYSTEM, $data['box_cachemode']);
    $this->assertEquals(0, $data['box_cachetime']);
    $this->assertEquals(array('box_id' => 42), $filter);
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseAccess $databaseAccess */
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
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
