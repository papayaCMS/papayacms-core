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

namespace Papaya\Content {

  require_once __DIR__.'/../../../bootstrap.php';

  class LanguagesTest extends \PapayaTestCase {

    /**
     * @covers Languages::load
     */
    public function testLoad() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
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
        ->with($this->isType('string'), array('table_'.Tables::LANGUAGES))
        ->will($this->returnValue($databaseResult));
      $languages = new Languages();
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
     * @covers       Languages::getLanguage
     * @dataProvider provideLanguageFilterVariants
     * @param mixed $languageFilter
     */
    public function testGetLanguage($languageFilter) {
      $languages = new Languages_TestProxy();
      $languages->papaya($this->mockPapaya()->application());
      $languages->setDatabaseAccess(
        $this->mockPapaya()->databaseAccess()
      );
      $language = $languages->getLanguage($languageFilter);
      $this->assertInstanceOf(Language::class, $language);
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
     * @covers Languages::getLanguage
     */
    public function testGetLanguageImplicitLoad() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
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
        ->with($this->isType('string'), array('table_'.Tables::LANGUAGES))
        ->will($this->returnValue($databaseResult));
      $languages = new Languages();
      $languages->setDatabaseAccess($databaseAccess);
      $language = $languages->getLanguage(2);
      $this->assertInstanceOf(Language::class, $language);
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
     * @covers Languages::getLanguage
     */
    public function testGetLanguageImplicitLoadExpectingNull() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
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
        ->with($this->isType('string'), array('table_'.Tables::LANGUAGES))
        ->will($this->returnValue($databaseResult));
      $languages = new Languages();
      $languages->setDatabaseAccess($databaseAccess);
      $language = $languages->getLanguage(99);
      $this->assertNull($language);
    }

    /**
     * @covers Languages::getLanguageByCode
     */
    public function testGetLanguageByCode() {
      $languages = new Languages_TestProxy();
      $language = $languages->getLanguageByCode('de-DE');
      $this->assertInstanceOf(Language::class, $language);
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
     * @covers Languages::getLanguageByCode
     */
    public function testGetLanguageByCodeExpectingNull() {
      $languages = new Languages_TestProxy();
      $language = $languages->getLanguageByCode('en-GB');
      $this->assertNull($language);
    }

    /**
     * @covers Languages::getLanguageByIdentifier
     */
    public function testGetLanguageByIdentifier() {
      $languages = new Languages_TestProxy();
      $language = $languages->getLanguageByIdentifier('de');
      $this->assertInstanceOf(Language::class, $language);
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
     * @covers Languages::getLanguageByIdentifier
     */
    public function testGetLanguageByIdentifierExpectingNull() {
      $languages = new Languages_TestProxy();
      $language = $languages->getLanguageByIdentifier('foo');
      $this->assertNull($language);
    }

    /**
     * @covers Languages::getIdentiferById
     */
    public function testGetIdentifierById() {
      $languages = new Languages_TestProxy();
      $languages->papaya($this->mockPapaya()->application());
      $languages->setDatabaseAccess(
        $this->mockPapaya()->databaseAccess()
      );
      $this->assertEquals(
        'de', $languages->getIdentiferById(2)
      );
    }

    /**
     * @covers Languages::getIdentiferById
     */
    public function testGetIdentifierByIdExpectingNull() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
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
        ->with($this->isType('string'), array('table_'.Tables::LANGUAGES))
        ->will($this->returnValue($databaseResult));
      $languages = new Languages();
      $languages->setDatabaseAccess($databaseAccess);
      $this->assertNull($languages->getIdentiferById(99));
    }
  }

  class Languages_TestProxy extends Languages {

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
}
