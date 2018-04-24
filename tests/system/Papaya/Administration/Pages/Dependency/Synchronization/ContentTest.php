<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationContentTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationContent::translations
  */
  public function testTranslationsGetAfterSet() {
    $translations = $this->getMock('PapayaContentPageTranslations');
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
      'PapayaContentPageTranslations', $action->translations()
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
        'topic_trans',
        array(
          'topic_content' => '<data version="2"/>',
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
        'topic_trans',
        array(
          'topic_content' => '<data version="2"/>',
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
        'topic_trans',
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
    $key = $this->getMock('PapayaDatabaseInterfaceKey');
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
    $key = $this->getMock('PapayaDatabaseInterfaceKey');
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
  ********************************/

  private function getDatabaseAccessFixture($targetRecords = array()) {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        call_user_func_array(
          array($this, 'onConsecutiveCalls'), $targetRecords
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt', 'getSqlCondition', 'updateRecord', 'deleteRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array(PapayaContentTables::PAGE_TRANSLATIONS))
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

  private function getTranslationsFixture(
                     $databaseAccess, $translations = array(), $translation = NULL
                   ) {
    $result = $this->getMock('PapayaContentPageTranslations');
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
    if (isset($translation)) {
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

  private function getTranslationFixture($data = array()) {
    $this->_translationData = $data;
    $translation = $this->getMock('PapayaContentPageTranslation');
    $translation
      ->expects($this->any())
      ->method('__get')
      ->will($this->returnCallback(array($this, 'callbackTranslationData')));
    return $translation;
  }

  public function callbackTranslationData($name) {
    return $this->_translationData[$name];
  }
}
