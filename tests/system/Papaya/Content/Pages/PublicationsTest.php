<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaContentPagesPublicationsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPagesPublications::__construct
  * @covers PapayaContentPagesPublications::load
  * @covers PapayaContentPagesPublications::_compileCondition
  */
  public function testLoadWithTranslationNeeded() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->logicalAnd(
          $this->isType('string'),
          $this->stringContains(
            "((t.published_from <= '123456789' AND t.published_to >= '123456789')"
          ),
          $this->stringContains("OR t.published_to <= t.published_from)")
        ),
        array(
          PapayaContentTables::PAGE_PUBLICATIONS,
          PapayaContentTables::PAGE_PUBLICATION_TRANSLATIONS,
          1,
          PapayaContentTables::PAGE_PUBLICATIONS,
          PapayaContentTables::VIEWS,
          PapayaContentTables::VIEW_CONFIGURATIONS,
          0,
          PapayaContentTables::AUTHENTICATION_USERS
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