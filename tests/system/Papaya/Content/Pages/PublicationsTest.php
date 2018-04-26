<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPagesPublicationsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPagesPublications::__construct
  * @covers PapayaContentPagesPublications::load
  * @covers PapayaContentPagesPublications::_compileCondition
  */
  public function testLoadWithTranslationNeeded() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->logicalAnd(
          $this->isType('string'),
          $this->stringContains(
            "((t.published_from <= '123456789' AND t.published_to >= '123456789')"
          ),
          $this->stringContains('OR t.published_to <= t.published_from)')
        ),
        array(
          'table_'.PapayaContentTables::PAGE_PUBLICATIONS,
          'table_'.PapayaContentTables::PAGE_PUBLICATION_TRANSLATIONS,
          1,
          'table_'.PapayaContentTables::PAGE_PUBLICATIONS,
          'table_'.PapayaContentTables::VIEWS,
          'table_'.PapayaContentTables::VIEW_CONFIGURATIONS,
          0,
          'table_'.PapayaContentTables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new PapayaContentPagesPublications(TRUE);
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('time' => 123456789, 'language_id' => 1)));
  }

  /**
  * @covers PapayaContentPagesPublications
  */
  public function testIsPublicExpectingTrue() {
    $pages = new PapayaContentPagesPublications();
    $this->assertTrue($pages->isPublic());
  }
}
