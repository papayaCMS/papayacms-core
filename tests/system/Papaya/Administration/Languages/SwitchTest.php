<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaAdministrationLanguagesSwitchTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationLanguagesSwitch::languages
  */
  public function testLanguagesGetAfterSet() {
    $languages = $this->createMock(PapayaContentLanguages::class);
    $switch = new PapayaAdministrationLanguagesSwitch();
    $this->assertSame(
      $languages, $switch->languages($languages)
    );
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::languages
  */
  public function testLanguagesGetImplicitCreate() {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $this->assertInstanceOf(
      PapayaContentLanguages::class, $switch->languages()
    );
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::__get
   */
  public function testGetCurrentLanguageIdFromProperty() {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture());
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Session' => $this->getSessionFixture(21, 21)
        )
      )
    );
    $this->assertEquals(21, $switch->id);
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::__get
   */
  public function testGetCurrentLanguageTitleFromProperty() {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture());
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Session' => $this->getSessionFixture(21, 21)
        )
      )
    );
    $this->assertEquals('English', $switch->title);
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::__get
   */
  public function testGetCurrentLanguageImageFromProperty() {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture());
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Session' => $this->getSessionFixture(21, 21)
        )
      )
    );
    $this->assertEquals('./pics/language/us.gif', $switch->image);
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::getCurrent
  * @covers PapayaAdministrationLanguagesSwitch::prepare
  */
  public function testLanguagesGetCurrentFromRequestParameters() {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture());
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $this->mockPapaya()->request(
            array('lngsel' => array('language_select' => 21))
          ),
          'Session' => $this->getSessionFixture(21)
        )
      )
    );
    $language = $switch->getCurrent();
    $this->assertSame(21, $language->id);
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::getCurrent
  * @covers PapayaAdministrationLanguagesSwitch::prepare
  */
  public function testLanguagesGetCurrentFromSession() {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture());
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Session' => $this->getSessionFixture(21, 21)
        )
      )
    );
    $language = $switch->getCurrent();
    $this->assertSame(21, $language->id);
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::getCurrent
  * @covers PapayaAdministrationLanguagesSwitch::prepare
  */
  public function testLanguagesGetCurrentFromList() {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture(array(21 => array('id' => 21))));
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Session' => $this->getSessionFixture()
        )
      )
    );
    $language = $switch->getCurrent();
    $this->assertSame(21, $language->id);
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::getCurrent
  * @covers PapayaAdministrationLanguagesSwitch::prepare
  * @covers PapayaAdministrationLanguagesSwitch::getDefault
  */
  public function testLanguagesGetCurrentFromDefault() {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture());
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Session' => $this->getSessionFixture()
        )
      )
    );
    $language = $switch->getCurrent();
    $this->assertSame(21, $language->id);
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::getCurrent
  * @covers PapayaAdministrationLanguagesSwitch::prepare
  * @covers PapayaAdministrationLanguagesSwitch::getDefault
  */
  public function testLanguagesGetCurrentFromDefaultNoExistingLanguage() {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture(array()));
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Session' => $this->getSessionFixture()
        )
      )
    );
    $language = $switch->getCurrent();
    $this->assertSame(1, $language->id);
  }

  /**
   * @covers       PapayaAdministrationLanguagesSwitch::getCurrent
   * @covers       PapayaAdministrationLanguagesSwitch::prepare
   * @dataProvider provideLanguageOptions
   * @param array $options
   */
  public function testLanguagesGetCurrentFromOption(array $options) {
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture());
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Options' => $this->mockPapaya()->options($options),
          'Session' => $this->getSessionFixture()
        )
      )
    );
    $language = $switch->getCurrent();
    $this->assertSame(21, $language->id);
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::getCurrent
  * @covers PapayaAdministrationLanguagesSwitch::prepare
  */
  public function testLanguagesGetCurrentFromUserOptionContent() {
    $user = new PapayaAdministrationUser_StubForLanguageSwitch();
    $user->options = array('PAPAYA_CONTENT_LANGUAGE' => 21);
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture());
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'AdministrationUser' => $user,
          'Session' => $this->getSessionFixture()
        )
      )
    );
    $language = $switch->getCurrent();
    $this->assertSame(21, $language->id);
  }

  /**
  * @covers PapayaAdministrationLanguagesSwitch::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $switch = new PapayaAdministrationLanguagesSwitch();
    $switch->languages($this->getLanguagesFixture());
    $switch->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $this->mockPapaya()->request(
            array('lngsel' => array('language_select' => 21))
          ),
          'Session' => $this->getSessionFixture(21)
        )
      )
    );
    $switch->appendTo($dom->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
       // language=xml
      '<sample>
        <links title="Content Language">
          <link 
            href="http://www.test.tld/test.html?lngsel[language_select]=21" 
            title="English" 
            image="us.gif" 
            selected="selected"/>
          <link href="http://www.test.tld/test.html?lngsel[language_select]=23" title="German" image="de.gif"/>
        </links>
      </sample>',
      $dom->saveXML($dom->documentElement)
    );
  }

  /*************************
  * Data Provider
  **************************/

  public static function provideLanguageOptions() {
    return array(
      array(array('PAPAYA_CONTENT_LANGUAGE' => 21)),
      array(array('PAPAYA_UI_LANGUAGE' => 'de-DE'))
    );
  }


  /*************************
   * Fixtures
   *************************

  /*
   *
   * @param array $languages
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaContentLanguages
   */
  public function getLanguagesFixture($languages = NULL) {
    $language = $this->createMock(PapayaContentLanguage::class);
    $language
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackLanguageData')));

    $defaultLanguageData = is_array($languages)
      ? reset($languages)
      : array(
        'id' => 21,
        'identifier' => 'en',
        'code' => 'en-US',
        'title' => 'English',
        'image' => 'us.gif',
        'is_content' => 1,
        'is_interface' => 1
      );
    $defaultLanguage = new PapayaContentLanguage();
    if (is_array($defaultLanguageData)) {
      $defaultLanguage->assign($defaultLanguageData);
    }

    $result = $this->createMock(PapayaContentLanguages::class);
    $result
      ->expects($this->once())
      ->method('loadByUsage')
      ->with($this->equalTo(PapayaContentLanguages::FILTER_IS_CONTENT))
      ->will($this->returnValue(TRUE));
    $result
      ->expects($this->any())
      ->method('getLanguage')
      ->withAnyParameters()
      ->will($this->returnValue($language));
    $result
      ->expects($this->any())
      ->method('getLanguageByCode')
      ->withAnyParameters()
      ->will($this->returnValue($language));
    $result
      ->expects($this->any())
      ->method('getDefault')
      ->willReturn(is_array($defaultLanguageData) ? $defaultLanguage : NULL);
    $result
      ->expects($this->any())
      ->method('getIterator')
      ->withAnyParameters()
      ->will(
        $this->returnValue(
          new ArrayIterator(
            NULL !== $languages
              ? $languages
              : array(
                  21 => array(
                    'id' => 21,
                    'identifier' => 'en',
                    'code' => 'en-US',
                    'title' => 'English',
                    'image' => 'us.gif',
                    'is_content' => 1,
                    'is_interface' => 1
                  ),
                  23 => array(
                    'id' => 23,
                    'identifier' => 'de',
                    'code' => 'de-DE',
                    'title' => 'German',
                    'image' => 'de.gif',
                    'is_content' => 1,
                    'is_interface' => 1
                  )
                )
          )
        )
      );

    return $result;
  }

  public function callbackLanguageData($name) {
    $data = array(
      'id' => 21,
      'identifier' => 'en',
      'code' => 'en-US',
      'title' => 'English',
      'image' => 'us.gif',
      'is_content' => 1,
      'is_interface' => 1
    );
    return $data[$name];
  }

  public function getSessionFixture($languageSet = NULL, $languageGet = NULL) {
    $values = $this
      ->getMockBuilder(PapayaSessionValues::class)
      ->disableOriginalConstructor()
      ->getMock();
    if (NULL !== $languageSet) {
      $values
        ->expects($this->once())
        ->method('set')
        ->with($this->isType('array'), $languageSet);
    }
    if (NULL !== $languageGet) {
      $values
        ->expects($this->once())
        ->method('get')
        ->with($this->isType('array'))
        ->will($this->returnValue($languageGet));
    } else {
      $values
        ->expects($this->any())
        ->method('get')
        ->withAnyParameters()
        ->will($this->returnValue(NULL));
    }
    $session = $this->createMock(PapayaSession::class);
    $session
      ->expects($this->any())
      ->method('values')
      ->will($this->returnValue($values));
    return $session;
  }
}


class PapayaAdministrationUser_StubForLanguageSwitch {

  public $options = array();
}
