<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationViewTest extends PapayaTestCase {

  private $_translationData;

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationView::updateTranslations
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
          'viewId' => 33,
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
          'view_id' => 33,
          'topic_trans_modified' => 123
        ),
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(TRUE));
    $action = new PapayaAdministrationPagesDependencySynchronizationView();
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /********************************
   * Fixtures
   *******************************/

  /**
   * @param array $targetRecords
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseResult
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
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
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

  /**
   * @param PapayaDatabaseAccess|PHPUnit_Framework_MockObject_MockObject $databaseAccess
   * @param array $translations
   * @param PapayaContentPageTranslation|PHPUnit_Framework_MockObject_MockObject|NULL $translation
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaContentPageTranslations
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
            $this->getTranslationFixture(array('pageId' => 0, 'languageId' => 0))
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
