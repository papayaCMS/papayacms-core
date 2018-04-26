<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationContentTest extends PapayaTestCase {

  private $_translationData;

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::translations
  */
  public function testTranslationsGetAfterSet() {
    $translations = $this->createMock(PapayaContentPageTranslations::class);
    $action = new PapayaAdministrationPagesDependencySynchronizationContent();
    $this->assertSame(
      $translations, $action->translations($translations)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::translations
  */
  public function testTranslationsGetImplicitCreate() {
    $action = new PapayaAdministrationPagesDependencySynchronizationContent();
    $this->assertInstanceOf(
      PapayaContentPageTranslations::class, $action->translations()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getExistingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getMissingTargetTranslations
  */
  public function testSynchronizeWithoutAnyTranslations() {
    $action = new PapayaAdministrationPagesDependencySynchronizationContent();
    $action->translations($this->getTranslationsFixture($this->getDatabaseAccessFixture()));
    $this->assertTrue($action->synchronize(array(21), 42, array(1)));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getExistingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getMissingTargetTranslations
  */
  public function testSynchronizeFetchLanguagesFromTranslations() {
    $action = new PapayaAdministrationPagesDependencySynchronizationContent();
    $action->translations(
      $this->getTranslationsFixture(
        $this->getDatabaseAccessFixture(),
        array(
          1 => array('languageId' => 1)
        )
      )
    );
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getExistingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getMissingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronizeTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::updateTranslations
  */
  public function testSynchronizeUpdateOneTranslation() {
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(
        array(
          array('topic_id' => 21, 'lng_id' => 1)
        )
      ),
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'content' => array(),
          'modified' => 123
        )
      )
    );
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'table_topic_trans',
        array(
          'topic_content' =>
            /** @lang XML */
            '<data version="2"/>',
          'topic_trans_modified' => 123
        ),
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(TRUE));
    $action = new PapayaAdministrationPagesDependencySynchronizationContent();
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getExistingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getMissingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronizeTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::updateTranslations
  */
  public function testSynchronizeUpdateFailed() {
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(
        array(
          array('topic_id' => 21, 'lng_id' => 1)
        )
      ),
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'content' => array(),
          'modified' => 123
        )
      )
    );
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'table_topic_trans',
        array(
          'topic_content' =>
            /** @lang XML */
            '<data version="2"/>',
          'topic_trans_modified' => 123
        ),
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(FALSE));
    $action = new PapayaAdministrationPagesDependencySynchronizationContent();
    $action->translations($translations);
    $this->assertFalse($action->synchronize(array(21), 42));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getExistingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getMissingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronizeTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::deleteTranslations
  */
  public function testSynchronizeDeleteOneTranslation() {
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(
        array(
          array('topic_id' => 21, 'lng_id' => 1)
        )
      ),
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 0,
          'languageId' => 0,
          'content' => array(),
          'modified' => 0
        )
      )
    );
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with(
        'table_topic_trans',
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(TRUE));
    $action = new PapayaAdministrationPagesDependencySynchronizationContent();
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getExistingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getMissingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronizeTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::insertTranslations
  */
  public function testSynchronizeInsertOneTranslation() {
    $key = $this->createMock(PapayaDatabaseInterfaceKey::class);
    $key
      ->expects($this->once())
      ->method('clear');
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(),
      array(
        1 => array('languageId' => 1)
      ),
      $translation = $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'content' => array(),
          'modified' => 123
        )
      )
    );
    $translation
      ->expects($this->once())
      ->method('__set')
      ->with('id', '21');
    $translation
      ->expects($this->once())
      ->method('key')
      ->will($this->returnValue($key));
    $translation
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $action = new PapayaAdministrationPagesDependencySynchronizationContent();
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getExistingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::getMissingTargetTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::synchronizeTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::insertTranslations
  */
  public function testSynchronizeInsertOneTranslationInsertFailed() {
    $key = $this->createMock(PapayaDatabaseInterfaceKey::class);
    $key
      ->expects($this->once())
      ->method('clear');
    $translations = $this->getTranslationsFixture(
      $databaseAccess = $this->getDatabaseAccessFixture(),
      array(
        1 => array('languageId' => 1)
      ),
      $translation = $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'content' => array(),
          'modified' => 123
        )
      )
    );
    $translation
      ->expects($this->once())
      ->method('__set')
      ->with('id', '21');
    $translation
      ->expects($this->once())
      ->method('key')
      ->will($this->returnValue($key));
    $translation
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(FALSE));
    $action = new PapayaAdministrationPagesDependencySynchronizationContent();
    $action->translations($translations);
    $this->assertFalse($action->synchronize(array(21), 42));
  }

  /********************************
   * Fixtures
   *******************************/

  /**
   * @param array $targetRecords
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseAccess
   */
  private function getDatabaseAccessFixture(array $targetRecords = array()) {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        call_user_func_array(
          array($this, 'onConsecutiveCalls'), $targetRecords
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_'.PapayaContentTables::PAGE_TRANSLATIONS))
      ->will($this->returnValue($databaseResult));
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(
        array(
          'topic_id' => array(21),
          'lng_id' => array(1)
        )
      )
      ->will($this->returnValue('__FILTER__'));
    return $databaseAccess;
  }

  /**
   * @param PapayaDatabaseAccess|PHPUnit_Framework_MockObject_MockObject $databaseAccess
   * @param array $translations
   * @param PapayaContentPageTranslation|PHPUnit_Framework_MockObject_MockObject $translation
   * @return PHPUnit_Framework_MockObject_MockObject
   */
  private function getTranslationsFixture(
    PapayaDatabaseAccess $databaseAccess,
    array $translations = array(),
    PapayaContentPageTranslation $translation = NULL
  ) {
    $result = $this->createMock(PapayaContentPageTranslations::class);
    $result
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $result
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($translations)));
    $result
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    if (NULL !== $translation) {
      $result
        ->expects($this->once())
        ->method('getTranslation')
        ->with(42, 1)
        ->will($this->returnValue($translation));
      $translation
        ->expects($this->any())
        ->method('getDatabaseAccess')
        ->will($this->returnValue($databaseAccess));
    } else {
      $result
        ->expects($this->any())
        ->method('getTranslation')
        ->withAnyParameters()
        ->will(
          $this->returnValue(
            $this->getTranslationFixture(array('id' => 0, 'languageId' => 0))
          )
        );
    }
    return $result;
  }

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaContentPageTranslation
   */
  private function getTranslationFixture(array $data = array()) {
    $translation = $this->createMock(PapayaContentPageTranslation::class);
    $translation
      ->expects($this->any())
      ->method('__get')
      ->willReturnCallback(
        function($name) use ($data) {
          return $data[$name];
        }
      );
    return $translation;
  }
}
