<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaAdministrationLanguagesSwitchTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationLanguagesSwitch::languages
  */
  public function testLanguagesGetAfterSet() {
    $languages = $this->getMock('PapayaContentLanguages');
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
      'PapayaContentLanguages', $switch->languages()
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
    $switch->languages($this->getLanguagesFixture(array('id' => 21)));
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
  * @covers PapayaAdministrationLanguagesSwitch::getCurrent
  * @covers PapayaAdministrationLanguagesSwitch::prepare
  * @dataProvider provideLanguageOptions
  */
  public function testLanguagesGetCurrentFromOption($options) {
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
    $this->assertEquals(
      '<sample>'.
        '<links title="Content Language">'.
          '<link href="http://www.test.tld/test.html?lngsel[language_select]=21"'.
          ' title="English" image="us.gif" selected="selected"/>'.
          '<link href="http://www.test.tld/test.html?lngsel[language_select]=23"'.
          ' title="German" image="de.gif"/>'.
        '</links>'.
      '</sample>',
      $dom->saveXml($dom->documentElement)
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
  **************************/

  public function getLanguagesFixture($languages = NULL) {
    $language = $this->getMock('PapayaContentLanguage');
    $language
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackLangugageData')));

    $result = $this->getMock(
      'PapayaContentLanguages',
      array('load', 'getLanguage', 'getLanguageByCode', 'getIterator', 'itemAt')
    );
    $result
      ->expects($this->once())
      ->method('load')
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
      ->method('getIterator')
      ->withAnyParameters()
      ->will(
        $this->returnValue(
          new ArrayIterator(
            isset($languages)
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

  public function callbackLangugageData($name) {
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
      ->getMockBuilder('PapayaSessionValues')
      ->disableOriginalConstructor()
      ->getMock();
    if (isset($languageSet)) {
      $values
        ->expects($this->once())
        ->method('set')
        ->with($this->isType('array'), $languageSet);
    }
    if (isset($languageGet)) {
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
    $session = $this->getMock('PapayaSession', array('values'));
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