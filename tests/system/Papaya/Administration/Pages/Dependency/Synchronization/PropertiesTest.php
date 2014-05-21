<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaAdministrationPagesDependencySynchronizationPropertiesTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationProperties::page
  */
  public function testPageGetAfterSet() {
    $page = $this->getMock('PapayaContentPageWork');
    $action = new PapayaAdministrationPagesDependencySynchronizationProperties();
    $this->assertSame(
      $page, $action->page($page)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationProperties::page
  */
  public function testPageGetImplicitCreate() {
    $action = new PapayaAdministrationPagesDependencySynchronizationProperties();
    $this->assertInstanceOf(
      'PapayaContentPageWork', $action->page()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationProperties::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationProperties::updateTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationProperties::updatePages
  */
  public function testSynchronizeUpdatePageAndOneTranslation() {
    $databaseAccess = $this->getDatabaseAccessFixture(
      array(
        array('topic_id' => 21, 'lng_id' => 1)
      )
    );
    $page = $this->getPageFixture(
      $databaseAccess,
      array(
        'pageId' => 42,
        'defaultLanguage' => 1,
        'linkType' => 23,
        'changeFrequency' => 50,
        'priority' => 99,
        'scheme' => 1,
        'cacheMode' => 2,
        'cacheTime' => 3600,
        'expiresMode' => 2,
        'expiresTime' => 3600
      )
    );
    $translations = $this->getTranslationsFixture(
      $databaseAccess,
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'title' => 'Test Title',
          'metaTitle' => 'Test Meta Title',
          'metaDescription' => 'Test Meta Description',
          'metaKeywords' => 'Keyowrd1, Keyword2'
        )
      )
    );
    $databaseAccess
      ->expects($this->at(2))
      ->method('updateRecord')
      ->with(
        'topic_trans',
        array(
          'meta_descr' => 'Test Meta Description',
          'meta_keywords' => 'Keyowrd1, Keyword2',
          'meta_title' => 'Test Meta Title',
          'topic_title' => 'Test Title'
        ),
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(TRUE));
    $databaseAccess
      ->expects($this->at(4))
      ->method('updateRecord')
      ->with(
        'topic',
        array(
          'topic_mainlanguage' => 1,
          'topic_modified' => 84,
          'linktype_id' => 23,
          'topic_changefreq' => 50,
          'topic_priority' => 99,
          'topic_protocol' => 1,
          'topic_cachemode' => 2,
          'topic_cachetime' => 3600,
          'topic_expiresmode' => 2,
          'topic_expirestime' => 3600
        ),
        array(
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(TRUE));
    $action = new PapayaAdministrationPagesDependencySynchronizationProperties();
    $action->page($page);
    $action->translations($translations);
    $this->assertTrue($action->synchronize(array(21), 42));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationProperties::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationProperties::updateTranslations
  * @covers PapayaAdministrationPagesDependencySynchronizationProperties::updatePages
  */
  public function testSynchronizeUpdatePageFailed() {
    $databaseAccess = $this->getDatabaseAccessFixture(
      array(
        array('topic_id' => 21, 'lng_id' => 1)
      )
    );
    $page = $this->getPageFixture(
      $databaseAccess,
      array(
        'id' => 42,
        'defaultLanguage' => 1,
        'linkType' => 23,
        'changeFrequency' => 50,
        'priority' => 99,
        'scheme' => 1,
        'cacheMode' => 2,
        'cacheTime' => 3600,
        'expiresMode' => 2,
        'expiresTime' => 3600
      )
    );
    $translations = $this->getTranslationsFixture(
      $databaseAccess,
      array(
        1 => array('languageId' => 1)
      ),
      $this->getTranslationFixture(
        array(
          'id' => 42,
          'languageId' => 1,
          'title' => 'Test Title',
          'metaTitle' => 'Test Meta Title',
          'metaDescription' => 'Test Meta Description',
          'metaKeywords' => 'Keyowrd1, Keyword2'
        )
      )
    );
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'topic_trans',
        array(
          'meta_descr' => 'Test Meta Description',
          'meta_keywords' => 'Keyowrd1, Keyword2',
          'meta_title' => 'Test Meta Title',
          'topic_title' => 'Test Title'
        ),
        array(
          'lng_id' => 1,
          'topic_id' => array(21)
        )
      )
      ->will($this->returnValue(FALSE));
    $action = new PapayaAdministrationPagesDependencySynchronizationProperties();
    $action->page($page);
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
      ->setMethods(
        array('getTimestamp', 'queryFmt', 'getSqlCondition', 'updateRecord', 'deleteRecord')
      )
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
    $databaseAccess
      ->expects($this->any())
      ->method('getTimestamp')
      ->will($this->returnValue(84));
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

  private function getPageFixture($databaseAccess, $data = array()) {
    $this->_pageData = $data;
    $page = $this->getMock('PapayaContentPageWork');
    $page
      ->expects($this->any())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $page
      ->expects($this->any())
      ->method('__get')
      ->will($this->returnCallback(array($this, 'callbackPageData')));
    $page
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    return $page;
  }

  public function callbackPageData($name) {
    return $this->_pageData[$name];
  }
}