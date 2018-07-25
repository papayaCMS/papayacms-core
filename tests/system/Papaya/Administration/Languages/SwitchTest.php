<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

use Papaya\Administration\Languages\Selector;
use Papaya\Content\Language;
use Papaya\Content\Languages;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaAdministrationLanguagesSwitchTest extends PapayaTestCase {

  /**
  * @covers Selector::languages
  */
  public function testLanguagesGetAfterSet() {
    $languages = $this->createMock(Languages::class);
    $switch = new Selector();
    $this->assertSame(
      $languages, $switch->languages($languages)
    );
  }

  /**
  * @covers Selector::languages
  */
  public function testLanguagesGetImplicitCreate() {
    $switch = new Selector();
    $this->assertInstanceOf(
      Languages::class, $switch->languages()
    );
  }

  /**
  * @covers Selector::__get
   */
  public function testGetCurrentLanguageIdFromProperty() {
    $switch = new Selector();
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
  * @covers Selector::__get
   */
  public function testGetCurrentLanguageTitleFromProperty() {
    $switch = new Selector();
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
  * @covers Selector::__get
   */
  public function testGetCurrentLanguageImageFromProperty() {
    $switch = new Selector();
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
  * @covers Selector::getCurrent
  * @covers Selector::prepare
  */
  public function testLanguagesGetCurrentFromRequestParameters() {
    $switch = new Selector();
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
  * @covers Selector::getCurrent
  * @covers Selector::prepare
  */
  public function testLanguagesGetCurrentFromSession() {
    $switch = new Selector();
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
  * @covers Selector::getCurrent
  * @covers Selector::prepare
  */
  public function testLanguagesGetCurrentFromList() {
    $switch = new Selector();
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
  * @covers Selector::getCurrent
  * @covers Selector::prepare
  * @covers Selector::getDefault
  */
  public function testLanguagesGetCurrentFromDefault() {
    $switch = new Selector();
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
  * @covers Selector::getCurrent
  * @covers Selector::prepare
  * @covers Selector::getDefault
  */
  public function testLanguagesGetCurrentFromDefaultNoExistingLanguage() {
    $switch = new Selector();
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
   * @covers       Selector::getCurrent
   * @covers       Selector::prepare
   * @dataProvider provideLanguageOptions
   * @param array $options
   */
  public function testLanguagesGetCurrentFromOption(array $options) {
    $switch = new Selector();
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
  * @covers Selector::getCurrent
  * @covers Selector::prepare
  */
  public function testLanguagesGetCurrentFromUserOptionContent() {
    $user = new PapayaAdministrationUser_StubForLanguageSwitch();
    $user->options = array('PAPAYA_CONTENT_LANGUAGE' => 21);
    $switch = new Selector();
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
  * @covers Selector::appendTo
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $switch = new Selector();
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
    $switch->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
       /** @lang XML */
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
      $document->saveXML($document->documentElement)
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
 *
* /*
   *
   * @param array $languages
   * @return PHPUnit_Framework_MockObject_MockObject|Languages
   */
  public function getLanguagesFixture($languages = NULL) {
    $language = $this->createMock(Language::class);
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
    $defaultLanguage = new Language();
    if (is_array($defaultLanguageData)) {
      $defaultLanguage->assign($defaultLanguageData);
    }

    $result = $this->createMock(Languages::class);
    $result
      ->expects($this->once())
      ->method('loadByUsage')
      ->with($this->equalTo(Languages::FILTER_IS_CONTENT))
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
