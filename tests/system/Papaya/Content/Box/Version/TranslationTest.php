<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaContentBoxVersionTranslationTest extends PapayaTestCase {

  /**
  * @covers PapayaContentBoxVersionTranslation::load
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
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
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
      ->with($this->isType('string'), array('box_versions_trans', 'views', 'modules', 42, 1))
      ->will($this->returnValue($databaseResult));
    $translation = new PapayaContentBoxVersionTranslation();
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
}
