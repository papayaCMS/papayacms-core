<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\CMS\Application\Profile\Administration {

  use Papaya\CMS\Content\Language as ContentLanguage;
  use Papaya\CMS\Content\Languages as ContentLanguages;
  use Papaya\CMS\Administration\Phrases as PhraseTranslations;
  use Papaya\Request\Parameters;
  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\CMS\Application\Profile\Administration\Phrases
   */
  class PhrasesTest extends TestCase {

    public function testCreateObject() {
      $language = $this->createMock(ContentLanguage::class);

      $languages = $this->createMock(ContentLanguages::class);
      $languages
        ->expects($this->once())
        ->method('getLanguage')
        ->with(23)
        ->willReturn($language);

      $papaya = $this->mockPapaya()->application(
        [
          'languages' => $languages,
          'options' => $this->mockPapaya()->options(['PAPAYA_UI_LANGUAGE' => 23])
        ]
      );

      $profile = new Phrases();
      $translations = $profile->createObject($papaya);
      $this->assertSame($language, $translations->getLanguage());
    }

    public function testCreateObjectWithUserDefinedLanguage() {
      $user = new AuthenticationUser_TestDummy();
      $user->options = new Parameters(['PAPAYA_UI_LANGUAGE' => 42]);

      $language = $this->createMock(ContentLanguage::class);
      $languages = $this->createMock(ContentLanguages::class);
      $languages
        ->expects($this->once())
        ->method('getLanguage')
        ->with(42)
        ->willReturn($language);

      $papaya = $this->mockPapaya()->application(
        [
          'languages' => $languages,
          'administrationUser' => $user
        ]
      );

      $profile = new Phrases();
      $translations = $profile->createObject($papaya);
      $this->assertSame($language, $translations->getLanguage());
    }

    public function testCreateObjectWithoutDefinedLanguage() {
      $languages = $this->createMock(ContentLanguages::class);
      $languages
        ->expects($this->once())
        ->method('getLanguage')
        ->with(0)
        ->willReturn(NULL);

      $papaya = $this->mockPapaya()->application(
        [
          'languages' => $languages,
          'options' => $this->mockPapaya()->options(['PAPAYA_UI_LANGUAGE' => 0])
        ]
      );

      $profile = new Phrases();
      $this->assertInstanceOf(PhraseTranslations::class, $profile->createObject($papaya));
    }
  }

  class AuthenticationUser_TestDummy {
    public $options = NULL;
  }

}
