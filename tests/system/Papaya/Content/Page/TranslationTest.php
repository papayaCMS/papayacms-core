<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageTranslationTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageTranslation
  */
  public function testLoad() {
    $record = array(
      'topic_id' => '42',
      'lng_id' => '1',
      'topic_title' => 'translated page title',
      'topic_content' => '',
      'topic_trans_created' => '123',
      'topic_trans_modified' => '456',
      'meta_title' => 'meta title',
      'meta_keywords' => 'meta, keywords',
      'meta_descr' => 'meta description',
      'view_id' => '21',
      'module_guid' => '123456789012345678901234567890ab'
    );
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          PapayaContentTables::PAGE_TRANSLATIONS,
          PapayaContentTables::VIEWS
        )
      )
      ->will($this->returnValue($databaseResult));
    $translation = new PapayaContentPageTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $translation->load(array(42, 1))
    );
    $this->assertAttributeEquals(
      array(
        'id' => 42,
        'language_id' => 1,
        'title' => 'translated page title',
        'created' => 123,
        'modified' => 456,
        'meta_title' => 'meta title',
        'meta_keywords' => 'meta, keywords',
        'meta_description' => 'meta description',
        'view_id' => 21,
        'module_guid' => '123456789012345678901234567890ab',
        'content' => array()
      ),
      '_values',
      $translation
    );
  }

  /**
  * @covers PapayaContentPageTranslation
  */
  public function testLoadWithId() {
    $record = array(
      'topic_id' => '42',
      'lng_id' => '1',
      'topic_title' => 'translated page title',
      'topic_content' => '',
      'topic_trans_created' => '123',
      'topic_trans_modified' => '456',
      'meta_title' => 'meta title',
      'meta_keywords' => 'meta, keywords',
      'meta_descr' => 'meta description',
      'view_id' => '21',
      'module_guid' => '123456789012345678901234567890ab'
    );
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          PapayaContentTables::PAGE_TRANSLATIONS,
          PapayaContentTables::VIEWS
        )
      )
      ->will($this->returnValue($databaseResult));
    $translation = new PapayaContentPageTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $translation->load(array(42, 1))
    );
    $this->assertAttributeEquals(
      array(
        'id' => 42,
        'language_id' => 1,
        'title' => 'translated page title',
        'created' => 123,
        'modified' => 456,
        'meta_title' => 'meta title',
        'meta_keywords' => 'meta, keywords',
        'meta_description' => 'meta description',
        'view_id' => 21,
        'module_guid' => '123456789012345678901234567890ab',
        'content' => array()
      ),
      '_values',
      $translation
    );
  }

  /**
  * @covers PapayaContentPageTranslation
  */
  public function testLoadFailedExpectingFalse() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          PapayaContentTables::PAGE_TRANSLATIONS,
          PapayaContentTables::VIEWS
        )
      )
      ->will($this->returnValue(FALSE));
    $translation = new PapayaContentPageTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $translation->load(array(42, 1))
    );
  }

  /**
  * @covers PapayaContentPageTranslation
  */
  public function testLoadNoRecordExpectingFalse() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          PapayaContentTables::PAGE_TRANSLATIONS,
          PapayaContentTables::VIEWS
        )
      )
      ->will($this->returnValue($databaseResult));
    $translation = new PapayaContentPageTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $translation->load(array(42, 1))
    );
  }

  /**
  * @covers PapayaContentPageTranslation
  */
  public function testSaveCreateNew() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'queryFmt', 'insertRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('topic_id' => 42, 'lng_id' => 21))
      ->will($this->returnValue('>>CONDITION<<'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('topic_trans')))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with($this->equalTo('topic_trans'), $this->equalTo(NULL), $this->isType('array'))
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $translation = new PapayaContentPageTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'id' => 42,
        'language_id' => 21,
        'title' => 'page title',
        'content' => array('foo' => 'bar'),
        'meta_title' => 'meta title',
        'meta_keywords' => 'meta keywords',
        'meta_description' => 'meta description',
        'view_id' => 23
      )
    );
    $this->assertTrue((boolean)$translation->save());
  }

  public function checkInsertData($table, $idField, $data) {
    $this->assertEquals(42, $data['topic_id']);
    $this->assertEquals(21, $data['lng_id']);
    $this->assertEquals('page title', $data['topic_title']);
    $this->assertEquals(
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $data['topic_content']
    );
    $this->assertEquals('meta title', $data['meta_title']);
    $this->assertEquals('meta keywords', $data['meta_keywords']);
    $this->assertEquals('meta description', $data['meta_descr']);
    $this->assertEquals(23, $data['view_id']);
    return TRUE;
  }

  /**
  * @covers PapayaContentPageTranslation
  */
  public function testSaveUpdateExisting() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'queryFmt', 'updateRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('topic_id' => 42, 'lng_id' => 21))
      ->will($this->returnValue('>>CONDITION<<'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('topic_trans')))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        $this->equalTo('topic_trans'),
        $this->isType('array'),
        $this->equalTo(
          array('topic_id' => 42, 'lng_id' => 21)
        )
      )
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $translation = new PapayaContentPageTranslation();
    $translation->setDatabaseAccess($databaseAccess);
    $translation->assign(
      array(
        'id' => 42,
        'language_id' => 21,
        'title' => 'page title',
        'content' => array('foo' => 'bar'),
        'meta_title' => 'meta title',
        'meta_keywords' => 'meta keywords',
        'meta_description' => 'meta description',
        'view_id' => 23
      )
    );
    $this->assertTrue($translation->save());
  }

  public function checkUpdateData($table, $data, $filter) {
    $this->assertEquals('page title', $data['topic_title']);
    $this->assertEquals(
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $data['topic_content']
    );
    $this->assertEquals('meta title', $data['meta_title']);
    $this->assertEquals('meta keywords', $data['meta_keywords']);
    $this->assertEquals('meta description', $data['meta_descr']);
    $this->assertEquals(23, $data['view_id']);
    return TRUE;
  }

  /**
  * @covers PapayaContentPageTranslation
  */
  public function testSaveWithoutIndexDataExpectingFalse() {
    $translation = new PapayaContentPageTranslation();
    $this->assertFalse($translation->save());
  }
}
