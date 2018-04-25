<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaContentLanguagesTest extends PapayaTestCase {

  /**
  * @covers PapayaContentLanguages::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->withAnyParameters()
      ->will(
        $this->onConsecutiveCalls(
          array(
            'lng_id' => 1,
            'lng_ident' => 'en',
            'lng_short' => 'en-US',
            'lng_title' => 'English',
            'lng_glyph' => 'en-US.gif',
            'is_content_lng' => 1,
            'is_interface_lng' => 1,
          ),
          array(
            'lng_id' => 2,
            'lng_ident' => 'de',
            'lng_short' => 'de-DE',
            'lng_title' => 'Deutsch',
            'lng_glyph' => 'de-DE.gif',
            'is_content_lng' => 1,
            'is_interface_lng' => 1,
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array(PapayaContentTables::LANGUAGES))
      ->will($this->returnValue($databaseResult));
    $languages = new PapayaContentLanguages();
    $languages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($languages->load());
    $this->assertAttributeEquals(
      array(
        1 => array(
          'id' => 1,
          'identifier' => 'en',
          'code' => 'en-US',
          'title' => 'English',
          'image' => 'en-US.gif',
          'is_content' => 1,
          'is_interface' => 1
        ),
        2 => array(
          'id' => 2,
          'identifier' => 'de',
          'code' => 'de-DE',
          'title' => 'Deutsch',
          'image' => 'de-DE.gif',
          'is_content' => 1,
          'is_interface' => 1
        )
      ),
      '_records',
      $languages
    );
    $this->assertAttributeEquals(
      array(
        'en-US' => 1,
        'de-DE' => 2
      ),
      '_mapCodes',
      $languages
    );
    $this->assertAttributeEquals(
      array(
        'en' => 1,
        'de' => 2
      ),
      '_mapIdentifiers',
      $languages
    );
  }

  /**
  * @covers PapayaContentLanguages::getLanguage
  * @dataProvider provideLanguageFilterVariants
  */
  public function testGetLanguage($languageFilter) {
    $languages = new PapayaContentLanguages_TestProxy();
    $languages->papaya($this->mockPapaya()->application());
    $languages->setDatabaseAccess(
      $this->getMock(PapayaDatabaseAccess::class, array(), array(new stdClass))
    );
    $language = $languages->getLanguage($languageFilter);
    $this->assertInstanceOf(PapayaContentLanguage::class, $language);
    $this->assertAttributeEquals(
      array(
        'id' => 2,
        'identifier' => 'de',
        'code' => 'de-DE',
        'title' => 'Deutsch',
        'image' => 'de-DE.gif',
        'is_content' => 1,
        'is_interface' => 1
      ),
      '_values',
      $language
    );
  }

  public static function provideLanguageFilterVariants() {
    return array(
      'id' => array(2),
      'code' => array('de-DE'),
      'identifier' => array('de')
    );
  }

  /**
  * @covers PapayaContentLanguages::getLanguage
  */
  public function testGetLanguageImplicitLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->withAnyParameters()
      ->will(
        $this->returnValue(
          array(
            'lng_id' => 2,
            'lng_ident' => 'de',
            'lng_short' => 'de-DE',
            'lng_title' => 'Deutsch',
            'lng_glyph' => 'de-DE.gif',
            'is_content_lng' => 1,
            'is_interface_lng' => 1,
          )
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('lng_id' => 2))
      ->will($this->returnValue(" lng_id = '2'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array(PapayaContentTables::LANGUAGES))
      ->will($this->returnValue($databaseResult));
    $languages = new PapayaContentLanguages();
    $languages->setDatabaseAccess($databaseAccess);
    $language = $languages->getLanguage(2);
    $this->assertInstanceOf(PapayaContentLanguage::class, $language);
    $this->assertAttributeEquals(
      array(
        'id' => 2,
        'identifier' => 'de',
        'code' => 'de-DE',
        'title' => 'Deutsch',
        'image' => 'de-DE.gif',
        'is_content' => 1,
        'is_interface' => 1
      ),
      '_values',
      $language
    );
  }

  /**
  * @covers PapayaContentLanguages::getLanguage
  */
  public function testGetLanguageImplicitLoadExpectingNull() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->withAnyParameters()
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->with(array('lng_id' => 99))
      ->will($this->returnValue(" lng_id = '99'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array(PapayaContentTables::LANGUAGES))
      ->will($this->returnValue($databaseResult));
    $languages = new PapayaContentLanguages();
    $languages->setDatabaseAccess($databaseAccess);
    $language = $languages->getLanguage(99);
    $this->assertNull($language);
  }

  /**
  * @covers PapayaContentLanguages::getLanguageByCode
  */
  public function testGetLanguageByCode() {
    $languages = new PapayaContentLanguages_TestProxy();
    $language = $languages->getLanguageByCode('de-DE');
    $this->assertInstanceOf(PapayaContentLanguage::class, $language);
    $this->assertAttributeEquals(
      array(
        'id' => 2,
        'identifier' => 'de',
        'code' => 'de-DE',
        'title' => 'Deutsch',
        'image' => 'de-DE.gif',
        'is_content' => 1,
        'is_interface' => 1
      ),
      '_values',
      $language
    );
  }

  /**
  * @covers PapayaContentLanguages::getLanguageByCode
  */
  public function testGetLanguageByCodeExpectingNull() {
    $languages = new PapayaContentLanguages_TestProxy();
    $language = $languages->getLanguageByCode('en-GB');
    $this->assertNull($language);
  }

  /**
  * @covers PapayaContentLanguages::getLanguageByIdentifier
  */
  public function testGetLanguageByIdentifier() {
    $languages = new PapayaContentLanguages_TestProxy();
    $language = $languages->getLanguageByIdentifier('de');
    $this->assertInstanceOf(PapayaContentLanguage::class, $language);
    $this->assertAttributeEquals(
      array(
        'id' => 2,
        'identifier' => 'de',
        'code' => 'de-DE',
        'title' => 'Deutsch',
        'image' => 'de-DE.gif',
        'is_content' => 1,
        'is_interface' => 1
      ),
      '_values',
      $language
    );
  }

  /**
  * @covers PapayaContentLanguages::getLanguageByIdentifier
  */
  public function testGetLanguageByIdentifierExpectingNull() {
    $languages = new PapayaContentLanguages_TestProxy();
    $language = $languages->getLanguageByIdentifier('foo');
    $this->assertNull($language);
  }

  /**
  * @covers PapayaContentLanguages::getIdentiferById
  */
  public function testGetIdentiferById() {
    $languages = new PapayaContentLanguages_TestProxy();
    $languages->papaya($this->mockPapaya()->application());
    $languages->setDatabaseAccess(
      $this->getMock(PapayaDatabaseAccess::class, array(), array(new stdClass))
    );
    $this->assertEquals(
      'de', $languages->getIdentiferById(2)
    );
  }

  /**
  * @covers PapayaContentLanguages::getIdentiferById
  */
  public function testGetIdentiferByIdExpectingNull() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->withAnyParameters()
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->with(array('lng_id' => 99))
      ->will($this->returnValue(" lng_id = '99'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array(PapayaContentTables::LANGUAGES))
      ->will($this->returnValue($databaseResult));
    $languages = new PapayaContentLanguages();
    $languages->setDatabaseAccess($databaseAccess);
    $this->assertNull($languages->getIdentiferById(99));
  }
}

class PapayaContentLanguages_TestProxy extends PapayaContentLanguages {

  public $_records = array(
    1 => array(
      'id' => 1,
      'identifier' => 'en',
      'code' => 'en-US',
      'title' => 'English',
      'image' => 'en-US.gif',
      'is_content' => 1,
      'is_interface' => 1
    ),
    2 => array(
      'id' => 2,
      'identifier' => 'de',
      'code' => 'de-DE',
      'title' => 'Deutsch',
      'image' => 'de-DE.gif',
      'is_content' => 1,
      'is_interface' => 1
    )
  );

  public $_mapCodes = array(
    'en-US' => 1,
    'de-DE' => 2
  );

  public $_mapIdentifiers = array(
    'en' => 1,
    'de' => 2
  );
}
