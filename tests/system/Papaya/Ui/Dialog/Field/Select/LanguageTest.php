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

use Papaya\Content\Languages;

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldSelectLanguageTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Dialog\Field\Select\Language
  */
  public function testAppendTo() {
    $select = new \Papaya\UI\Dialog\Field\Select\Language(
      'Caption', 'name', $this->getLanguagesFixture()
    );
    $select->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectLanguage" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option value="1">Deutsch (de-DE)</option>
          <option value="2">English (en-US)</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select\Language
  */
  public function testAppendToWithAny() {
    $select = new \Papaya\UI\Dialog\Field\Select\Language(
      'Caption', 'name', $this->getLanguagesFixture(), \Papaya\UI\Dialog\Field\Select\Language::OPTION_ALLOW_ANY
    );
    $select->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectLanguage" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option value="0" selected="selected">Any</option>
          <option value="1">Deutsch (de-DE)</option>
          <option value="2">English (en-US)</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select\Language
  */
  public function testAppendToWithIdentifierKeys() {
    $select = new \Papaya\UI\Dialog\Field\Select\Language(
      'Caption', 'name', $this->getLanguagesFixture(), \Papaya\UI\Dialog\Field\Select\Language::OPTION_USE_IDENTIFIER
    );
    $select->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectLanguage" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option value="de">Deutsch (de-DE)</option>
          <option value="en">English (en-US)</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Select\Language
  */
  public function testAppendToWithIdentifierKeysAndAny() {
    $select = new \Papaya\UI\Dialog\Field\Select\Language(
      'Caption',
      'name',
      $this->getLanguagesFixture(),
      \Papaya\UI\Dialog\Field\Select\Language::OPTION_USE_IDENTIFIER |
      \Papaya\UI\Dialog\Field\Select\Language::OPTION_ALLOW_ANY
    );
    $select->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldSelectLanguage" error="yes" mandatory="yes">
        <select name="name" type="dropdown">
          <option value="*">Any</option>
          <option value="de">Deutsch (de-DE)</option>
          <option value="en">English (en-US)</option>
        </select>
      </field>',
      $select->getXml()
    );
  }

  /**
   * @return PHPUnit_Framework_MockObject_MockObject|Languages
   */
  private function getLanguagesFixture() {
    $languages = $this->createMock(Languages::class);
    $languages
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              1 => array(
                'identifier' => 'de',
                'code' => 'de-DE',
                'title' => 'Deutsch'
              ),
              2 => array(
                'identifier' => 'en',
                'code' => 'en-US',
                'title' => 'English'
              )
            )
          )
        )
      );
    return $languages;
  }
}
