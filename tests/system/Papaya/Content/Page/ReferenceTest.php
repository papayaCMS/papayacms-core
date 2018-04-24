<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageReferenceTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageReference::_createKey
  */
  public function testCreateKey() {
    $reference = new PapayaContentPageReference();
    $key = $reference->key();
    $this->assertInstanceOf(PapayaDatabaseRecordKeyFields::class, $key);
    $this->assertEquals(array('source_id', 'target_id'), $key->getProperties());
  }

  /**
  * @covers PapayaContentPageReference::_createMapping
  */
  public function testCreateMapping() {
    $reference = new PapayaContentPageReference();
    $mapping = $reference->mapping();
    $this->assertInstanceOf(PapayaDatabaseRecordMapping::class, $mapping);
    $this->assertTrue(isset($mapping->callbacks()->onAfterMapping));
  }

  /**
  * @covers PapayaContentPageReference::callbackSortPageIds
  * @dataProvider provideMappingData
  */
  public function testCallbackSortPageIds($expected, $mode, $values, $record) {
    $reference = new PapayaContentPageReference();
    $this->assertEquals(
      $expected,
      $reference->callbackSortPageIds(new stdClass, $mode, $values, $record)
    );
  }

  /**
  * @covers PapayaContentPageReference::exists
  */
  public function testExistsExpectingTrue() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array(PapayaContentTables::PAGE_REFERENCES, 21, 48))
      ->will($this->returnValue($databaseResult));
    $reference = new PapayaContentPageReference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertTrue($reference->exists(48, 21));
  }

  /**
  * @covers PapayaContentPageReference::exists
  */
  public function testExistsExpectingFalse() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array(PapayaContentTables::PAGE_REFERENCES, 21, 48))
      ->will($this->returnValue($databaseResult));
    $reference = new PapayaContentPageReference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertFalse($reference->exists(21, 48));
  }

  /**
  * @covers PapayaContentPageReference::exists
  */
  public function testExistsWithDatabaseErrorExpectingFalse() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array(PapayaContentTables::PAGE_REFERENCES, 21, 48))
      ->will($this->returnValue(FALSE));
    $reference = new PapayaContentPageReference();
    $reference->setDatabaseAccess($databaseAccess);
    $this->assertFalse($reference->exists(21, 48));
  }

  /*************************
  * Data Provider
  *************************/

  public static function provideMappingData() {
    return array(
      'record keep' => array(
        array(
          'topic_source_id' => 21,
          'topic_target_id' => 42
        ),
        PapayaDatabaseRecordMapping::PROPERTY_TO_FIELD,
        array(),
        array(
          'topic_source_id' => 21,
          'topic_target_id' => 42
        )
      ),
      'record change' => array(
        array(
          'topic_source_id' => 42,
          'topic_target_id' => 84
        ),
        PapayaDatabaseRecordMapping::PROPERTY_TO_FIELD,
        array(),
        array(
          'topic_source_id' => 84,
          'topic_target_id' => 42
        )
      ),
      'values keep' => array(
        array(
          'source_id' => 21,
          'target_id' => 42
        ),
        PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY,
        array(
          'source_id' => 21,
          'target_id' => 42
        ),
        array()
      ),
      'values change' => array(
        array(
          'source_id' => 42,
          'target_id' => 84
        ),
        PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY,
        array(
          'source_id' => 84,
          'target_id' => 42
        ),
        array()
      )
    );
  }
}
