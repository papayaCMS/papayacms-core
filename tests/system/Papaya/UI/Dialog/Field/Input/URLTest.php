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

namespace Papaya\UI\Dialog\Field\Input;
require_once __DIR__.'/../../../../../../bootstrap.php';

class URLTest extends \Papaya\TestFramework\TestCase {
  /**
   * @covers \Papaya\UI\Dialog\Field\Input\URL::__construct
   */
  public function testConstructor() {
    $field = new URL('URL', 'url', 'http://www.default.com', TRUE);
    $this->assertEquals(
      'URL',
      $field->caption
    );
    $this->assertEquals(
      'url',
      $field->name
    );
    $this->assertEquals(
      'http://www.default.com',
      $field->defaultValue
    );
    $this->assertTrue(
      $field->mandatory
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\URL
   * @dataProvider provideValidUrlInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new URL('URL', 'url');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\URL
   * @dataProvider provideInvalidUrlInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new URL('URL', 'url');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\URL::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $field = new URL('URL', 'url');
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($document->appendElement('test'));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<test>
        <field caption="URL" class="DialogFieldInputURL" error="no">
          <input type="url" name="url" maxlength="1024"/>
        </field>
      </test>',
      $document->saveXML($document->documentElement)
    );
  }

  public static function provideValidUrlInputs() {
    return array(
      array('http://www.example.com', TRUE),
      array('http://www.example.com', FALSE),
      array('', FALSE),
    );
  }

  public static function provideInvalidUrlInputs() {
    return array(
      array(':http://www.example.com', TRUE),
      array(':http://www.example.com', FALSE),
      array('http://www.example.', FALSE),
      array('', TRUE),
    );
  }
}
