<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordKeyFieldsTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordKeyFields::__construct
  */
  public function testConstructor() {
    $key = $this->getKeyFixture();
    $this->assertEquals(
      array('fk_one_id', 'fk_two_id'), $key->getProperties()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::assign
  * @covers PapayaDatabaseRecordKeyFields::getFilter
  */
  public function testAssignAndGetFilter() {
    $key = $this->getKeyFixture();
    $this->assertTrue($key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42)));
    $this->assertEquals(
      array('fk_one_id' => 21, 'fk_two_id' => 42), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::assign
  * @covers PapayaDatabaseRecordKeyFields::getFilter
  */
  public function testAssignWithInvalidData() {
    $key = $this->getKeyFixture();
    $this->assertFalse($key->assign(array('other' => 42)));
    $this->assertEquals(
      array('fk_one_id' => NULL, 'fk_two_id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::getFilter
  */
  public function testGetFilterWithoutAssign() {
    $key = $this->getKeyFixture();
    $this->assertEquals(
      array('fk_one_id' => NULL, 'fk_two_id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::getFilter
  */
  public function testGetFilterWithRecord() {
    $record = $this->createMock(PapayaDatabaseRecord::class);
    $record
      ->expects($this->atLeastOnce())
      ->method('offsetExists')
      ->will(
        $this->returnValueMap(
          array(
            array('fk_one_id', TRUE),
            array('fk_two_id', FALSE)
          )
        )
      );
    $record
      ->expects($this->atLeastOnce())
      ->method('offsetGet')
      ->will(
        $this->returnValueMap(
          array(
            array('fk_one_id', 21),
            array('fk_two_id', 48)
          )
        )
      );
    $key = $this->getKeyFixture($record);
    $this->assertEquals(
      array('fk_one_id' => 21, 'fk_two_id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::getProperties
  */
  public function testGetProperties() {
    $key = $this->getKeyFixture();
    $this->assertEquals(
      array('fk_one_id', 'fk_two_id'), $key->getProperties()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::exists
  */
  public function testExistsExpectingTrue() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));

    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_one_id' => 21, 'field_two_id' => 42))
      ->will($this->returnValue('{CONDITION}'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with("SELECT COUNT(*) FROM %s WHERE {CONDITION}", array('table_sometable'))
      ->will($this->returnValue($databaseResult));

    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->once())
      ->method('mapPropertiesToFields')
      ->with(array('fk_one_id' => 21, 'fk_two_id' => 42))
      ->will($this->returnValue(array('field_one_id' => 21, 'field_two_id' => 42)));

    $record = $this->createMock(PapayaDatabaseRecord::class);
    $record
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $record
      ->expects($this->any())
      ->method('mapping')
      ->will($this->returnValue($mapping));

    $key = $this->getKeyFixture($record);
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $this->assertTrue($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::exists
  */
  public function testExistsWithDatabaseErrorExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_one_id' => 21, 'field_two_id' => 42))
      ->will($this->returnValue('{CONDITION}'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with("SELECT COUNT(*) FROM %s WHERE {CONDITION}", array('table_sometable'))
      ->will($this->returnValue(FALSE));

    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->once())
      ->method('mapPropertiesToFields')
      ->with(array('fk_one_id' => 21, 'fk_two_id' => 42))
      ->will($this->returnValue(array('field_one_id' => 21, 'field_two_id' => 42)));

    $record = $this->createMock(PapayaDatabaseRecord::class);
    $record
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $record
      ->expects($this->any())
      ->method('mapping')
      ->will($this->returnValue($mapping));

    $key = $this->getKeyFixture($record);
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $this->assertFalse($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::exists
  */
  public function testExistsWithEmptyMappingResult() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->once())
      ->method('mapPropertiesToFields')
      ->with(array('fk_one_id' => NULL, 'fk_two_id' => NULL))
      ->will($this->returnValue(array()));
    $record = $this->createMock(PapayaDatabaseRecord::class);
    $record
      ->expects($this->any())
      ->method('mapping')
      ->will($this->returnValue($mapping));
    $key = $this->getKeyFixture($record);
    $this->assertFalse($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::getQualities
  */
  public function testGetQualities() {
    $key = $this->getKeyFixture();
    $this->assertEquals(0, $key->getQualities());
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::__toString
  */
  public function testMagicToString() {
    $key = $this->getKeyFixture();
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $this->assertSame('21|42', (string)$key);
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::clear
  */
  public function testClear() {
    $key = $this->getKeyFixture();
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $key->clear();
    $this->assertSame('', (string)$key);
  }

  public function getKeyFixture(PapayaDatabaseRecord $record = NULL) {
    if (is_null($record)) {
      $record = $this->createMock(PapayaDatabaseRecord::class);
    }
    return new PapayaDatabaseRecordKeyFields(
      $record, 'sometable', array('fk_one_id', 'fk_two_id')
    );
  }
}
