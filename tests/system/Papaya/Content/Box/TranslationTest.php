<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentBoxTranslationTest extends PapayaTestCase {

  /**
  * @covers PapayaContentBoxTranslation::load
  * @covers PapayaContentBoxTranslation::convertBoxRecordToValues
  */
  public function testLoad() {
    $record = array(
      'box_id' => '42',
      'lng_id' => '1',
      'box_title' => 'translated box title',
      'box_data' => '',
      'box_trans_created' => '123',
      'box_trans_modified' => '456',
      'view_id' => '21',
      'view_title' => 'view title',
      'module_guid' => '123456789012345678901234567890ab',
      'module_title' => 'module title'
    );
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('box_trans', 'views', 'modules', 42, 1))
      ->will($this->returnValue($databaseResult));
    $translation = new PapayaContentBoxTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $translation->load(array(42, 1))
    );
    $this->assertAttributeEquals(
      array(
        'box_id' => 42,
        'language_id' => 1,
        'title' => 'translated box title',
        'created' => 123,
        'modified' => 456,
        'view_id' => 21,
        'view_title' => 'view title',
        'module_guid' => '123456789012345678901234567890ab',
        'module_title' => 'module title',
        'content' => array()
      ),
      '_values',
      $translation
    );
  }

  /**
  * @covers PapayaContentBoxTranslation::save
  * @covers PapayaContentBoxTranslation::_insert
  */
  public function testSaveCreateNew() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('box_trans', 42, 21)))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $translation = new PapayaContentBoxTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'box_id' => 42,
        'language_id' => 21,
        'title' => 'box title',
        'content' => array('foo' => 'bar'),
        'view_id' => 23
      )
    );
    $this->assertTrue($translation->save());
  }

  public function checkInsertData($table, $idField, array $data) {
    $this->assertEquals('box_trans', $table);
    $this->assertNull($idField);
    $this->assertEquals(42, $data['box_id']);
    $this->assertEquals(21, $data['lng_id']);
    $this->assertEquals('box title', $data['box_title']);
    $this->assertEquals(
      /** @lang XML */
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $data['box_data']
    );
    $this->assertEquals(23, $data['view_id']);
    return TRUE;
  }

  /**
  * @covers PapayaContentBoxTranslation::save
  * @covers PapayaContentBoxTranslation::_update
  */
  public function testSaveUpdateExisting() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('box_trans', 42, 21)))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $translation = new PapayaContentBoxTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'box_id' => 42,
        'language_id' => 21,
        'title' => 'box title',
        'content' => array('foo' => 'bar'),
        'view_id' => 23
      )
    );
    $this->assertTrue($translation->save());
  }

  public function checkUpdateData($table, $data, $filter) {
    $this->assertEquals('box_trans', $table);
    $this->assertEquals('box title', $data['box_title']);
    $this->assertEquals(
      /** @lang XML */
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $data['box_data']
    );
    $this->assertEquals(23, $data['view_id']);
    $this->assertEquals(array('box_id' => 42, 'lng_id' => 21), $filter);
    return TRUE;
  }

  /**
  * @covers PapayaContentBoxTranslation::save
  */
  public function testSaveWithoutIndexDataExpectingFalse() {
    $translation = new PapayaContentBoxTranslation();
    $this->assertFalse($translation->save());
  }

  /**
  * @covers PapayaContentBoxTranslation::save
  */
  public function testSaveCheckFailesExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('box_trans', 42, 21)))
      ->will($this->returnValue(FALSE));
    $translation = new PapayaContentBoxTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'box_id' => 42,
        'language_id' => 21
      )
    );
    $this->assertFalse($translation->save());
  }
}
