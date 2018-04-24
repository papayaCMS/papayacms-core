<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageVersionTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageVersion::save
  */
  public function testSaveBlockingUpdateExpectingException() {
    $version = new PapayaContentPageVersion();
    $version->id = 42;
    $this->setExpectedException(
      'LogicException',
      'LogicException: Page versions can not be changed.'
    );
    $version->save();
  }

  /**
  * @covers PapayaContentPageVersion::save
  */
  public function testSaveInsertWhileMissingValuesExcpectingException() {
    $version = new PapayaContentPageVersion();
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: page id, owner or message are missing.'
    );
    $version->save();
  }

  /**
  * @covers PapayaContentPageVersion::save
  * @covers PapayaContentPageVersion::create
  */
  public function testSave() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmtWrite', 'lastInsertId'))
      ->getMock();
    $databaseAccess
      ->expects($this->any())
      ->method('lastInsertId')
      ->will($this->returnValue(42));
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('queryFmtWrite')
      ->with(
        $this->isType('string'),
        $this->logicalOr(
          array('topic_versions', 123, 'sample user id', 'test message', 1, 'topic', 21),
          array('topic_versions_trans', 42, 'topic_trans', 21)
        )
      )
      ->will(
        $this->onConsecutiveCalls(1, 2)
      );

    $version = new PapayaContentPageVersion();
    $version->assign(
      array(
        'page_id' => 21,
        'owner' => 'sample user id',
        'message' => 'test message',
        'created' => 123,
        'level' => 1,
      )
    );
    $version->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      42, $version->save()
    );
  }

  /**
  * @covers PapayaContentPageVersion::save
  * @covers PapayaContentPageVersion::create
  */
  public function testSaveWithDatabaseErrorInFirstQueryExpectingFalse() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmtWrite'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with(
        $this->isType('string'),
        $this->logicalOr(
          array('topic_versions', 123, 'sample user id', 'test message', 1, 'topic', 21)
        )
      )
      ->will($this->returnValue(FALSE));

    $version = new PapayaContentPageVersion();
    $version->assign(
      array(
        'page_id' => 21,
        'owner' => 'sample user id',
        'message' => 'test message',
        'created' => 123,
        'level' => 1,
      )
    );
    $version->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $version->save()
    );
  }

  /**
  * @covers PapayaContentPageVersion::translations
  */
  public function testTranslationsGetAfterSet() {
    $translations = $this->createMock(PapayaContentPageVersionTranslations::class);
    $version = new PapayaContentPageVersion();
    $this->assertSame($translations, $version->translations($translations));
  }

  /**
  * @covers PapayaContentPageVersion::translations
  */
  public function testTranslationsGetWithImplicitCreate() {
    $version = new PapayaContentPageVersion();
    $this->assertInstanceOf(
      'PapayaContentPageVersionTranslations',
      $version->translations()
    );
  }
}
