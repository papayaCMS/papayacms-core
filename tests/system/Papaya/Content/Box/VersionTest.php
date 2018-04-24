<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentBoxVersionTest extends PapayaTestCase {

  /**
  * @covers PapayaContentBoxVersion::save
  */
  public function testSaveBlockingUpdateExpectingException() {
    $version = new PapayaContentBoxVersion();
    $version->id = 42;
    $this->setExpectedException(
      'LogicException',
      'LogicException: Box versions can not be changed.'
    );
    $version->save();
  }

  /**
  * @covers PapayaContentBoxVersion::save
  */
  public function testSaveInsertWhileMissingValuesExcpectingException() {
    $version = new PapayaContentBoxVersion();
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: box id, owner or message are missing.'
    );
    $version->save();
  }

  /**
  * @covers PapayaContentBoxVersion::save
  * @covers PapayaContentBoxVersion::create
  */
  public function testSave() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
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
          array('box_versions', 123, 'sample user id', 'test message', 'box', 21),
          array('box_versions_trans', 42, 'box_trans', 21)
        )
      )
      ->will(
        $this->onConsecutiveCalls(42, TRUE)
      );

    $version = new PapayaContentBoxVersion();
    $version->assign(
      array(
        'box_id' => 21,
        'owner' => 'sample user id',
        'message' => 'test message',
        'created' => 123
      )
    );
    $version->setDatabaseAccess($databaseAccess);
    $this->assertEquals(
      42, $version->save()
    );
  }

  /**
  * @covers PapayaContentBoxVersion::save
  * @covers PapayaContentBoxVersion::create
  */
  public function testSaveWithDatabaseErrorInFirstQueryExpectingFalse() {
    $databaseAccess = $this->getMock(
      PapayaDatabaseAccess::class, array('getTableName', 'queryFmtWrite'), array(new stdClass)
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
          array('box_versions', 123, 'sample user id', 'test message', 'box', 21)
        )
      )
      ->will($this->returnValue(FALSE));

    $version = new PapayaContentBoxVersion();
    $version->assign(
      array(
        'box_id' => 21,
        'owner' => 'sample user id',
        'message' => 'test message',
        'created' => 123
      )
    );
    $version->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $version->save()
    );
  }

  /**
  * @covers PapayaContentBoxVersion::translations
  */
  public function testTranslationsGetAfterSet() {
    $translations = $this->createMock(PapayaContentBoxVersionTranslations::class);
    $version = new PapayaContentBoxVersion();
    $this->assertSame($translations, $version->translations($translations));
  }

  /**
  * @covers PapayaContentBoxVersion::translations
  */
  public function testTranslationsGetWithImplicitCreate() {
    $version = new PapayaContentBoxVersion();
    $this->assertInstanceOf(
      PapayaContentBoxVersionTranslations::class,
      $version->translations()
    );
  }
}
