<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaContentPageVersionTranslationTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageVersionTranslation::load
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
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'getSqlCondition', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('topic_versions_trans', 'views'))
      ->will($this->returnValue($databaseResult));
    $translation = new PapayaContentPageVersionTranslation();
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
}
