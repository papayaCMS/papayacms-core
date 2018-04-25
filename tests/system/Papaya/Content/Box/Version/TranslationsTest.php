<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaContentBoxVersionTranslationsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentBoxVersionTranslations::load
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
            'box_id' => '42',
            'lng_id' => '1',
            'box_title' => 'Translated box title',
            'box_trans_modified' => '123',
            'view_title' => 'Box view title'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue($databaseResult));
    $list = new PapayaContentBoxVersionTranslations();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load(42));
    $this->assertAttributeEquals(
      array(
        '1' => array(
          'id' => '42',
          'language_id' => '1',
          'title' => 'Translated box title',
          'modified' => '123',
          'view' => 'Box view title'
        )
      ),
      '_records',
      $list
    );
  }

  /**
  * @covers PapayaContentBoxVersionTranslations::getTranslation
  */
  public function testGetTranslation() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('box_versions_trans', 'views', 'modules', 42, 21))
      ->will($this->returnValue(FALSE));
    $list = new PapayaContentBoxVersionTranslations();
    $list->setDatabaseAccess($databaseAccess);
    $translation = $list->getTranslation(42, 21);
    $this->assertInstanceOf(
      PapayaContentBoxVersionTranslation::class, $translation
    );
  }
}
