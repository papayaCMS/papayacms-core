<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentBoxTranslationsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentBoxTranslations::load
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
            'box_trans_published' => NULL,
            'view_title' => 'Box view title'
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
    $list = new PapayaContentBoxTranslations();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load(42, 1));
    $this->assertAttributeEquals(
      array(
        '1' => array(
          'id' => '42',
          'language_id' => '1',
          'title' => 'Translated box title',
          'modified' => '123',
          'published' => NULL,
          'view' => 'Box view title'
        )
      ),
      '_records',
      $list
    );
  }

  /**
  * @covers PapayaContentBoxTranslations::getTranslation
  */
  public function testGetTranslation() {
    $databaseAccess = $this->getMock(
      PapayaDatabaseAccess::class, array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('box_trans', 'views', 'modules', 42, 21))
      ->will($this->returnValue(FALSE));
    $list = new PapayaContentBoxTranslations();
    $list->setDatabaseAccess($databaseAccess);
    $translation = $list->getTranslation(42, 21);
    $this->assertInstanceOf(
      PapayaContentBoxTranslation::class, $translation
    );
  }
}
