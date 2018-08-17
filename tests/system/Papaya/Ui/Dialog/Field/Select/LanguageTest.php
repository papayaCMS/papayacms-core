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

namespace Papaya\UI\Dialog\Field\Select;
require_once __DIR__.'/../../../../../../bootstrap.php';

class LanguageTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Language
   */
  public function testAppendTo() {
    $select = new Language(
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
      $select->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Language
   */
  public function testAppendToWithAny() {
    $select = new Language(
      'Caption', 'name', $this->getLanguagesFixture(), Language::OPTION_ALLOW_ANY
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
      $select->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Language
   */
  public function testAppendToWithIdentifierKeys() {
    $select = new Language(
      'Caption', 'name', $this->getLanguagesFixture(), Language::OPTION_USE_IDENTIFIER
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
      $select->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Select\Language
   */
  public function testAppendToWithIdentifierKeysAndAny() {
    $select = new Language(
      'Caption',
      'name',
      $this->getLanguagesFixture(),
      Language::OPTION_USE_IDENTIFIER |
      Language::OPTION_ALLOW_ANY
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
      $select->getXML()
    );
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Content\Languages
   */
  private function getLanguagesFixture() {
    $languages = $this->createMock(\Papaya\Content\Languages::class);
    $languages
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new \ArrayIterator(
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
