<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageTranslationsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageTranslations::setTranslationsTableName
  */
  public function testSetTranslationsTable() {
    $list = new PapayaContentPageTranslations();
    $list->setTranslationsTableName('success');
    $this->assertAttributeEquals(
      'success', '_translationsTableName', $list
    );
  }

  /**
  * @covers PapayaContentPageTranslations::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(PapayaDatabaseResult::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => '42',
            'lng_id' => '1',
            'topic_title' => 'Translated page title',
            'topic_trans_modified' => '123',
            'topic_trans_published' => NULL,
            'view_title' => 'Page view title'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->getMock(
      PapayaDatabaseAccess::class, array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue($databaseResult));
    $list = new PapayaContentPageTranslations();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load(42, 1));
    $this->assertAttributeEquals(
      array(
        '1' => array(
          'id' => '42',
          'language_id' => '1',
          'title' => 'Translated page title',
          'modified' => '123',
          'published' => NULL,
          'view' => 'Page view title'
        )
      ),
      '_records',
      $list
    );
  }

  /**
  * @covers PapayaContentPageTranslations::load
  */
  public function testLoadExpectingFalse() {
    $databaseAccess = $this->getMock(
      PapayaDatabaseAccess::class, array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue(FALSE));
    $list = new PapayaContentPageTranslations();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertFalse($list->load(42, 1));
  }

  /**
  * @covers PapayaContentPageTranslations::getTranslation
  */
  public function testGetTranslation() {
    $databaseAccess = $this->getMock(
      PapayaDatabaseAccess::class,
      array('getTableName', 'getSqlCondition', 'queryFmt'),
      array(new stdClass)
    );
    $list = new PapayaContentPageTranslations();
    $list->setDatabaseAccess($databaseAccess);
    $translation = $list->getTranslation(42, 21);
    $this->assertInstanceOf(
      PapayaContentPageTranslation::class, $translation
    );
  }
}
