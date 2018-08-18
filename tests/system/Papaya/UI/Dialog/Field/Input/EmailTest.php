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

class EmailTest extends \Papaya\TestCase {
  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Email::__construct
   */
  public function testConstructor() {
    $field = new Email('Email', 'email', 'default@example.com', TRUE);
    $this->assertEquals(
      'Email',
      $field->caption
    );
    $this->assertEquals(
      'email',
      $field->name
    );
    $this->assertEquals(
      'default@example.com',
      $field->defaultValue
    );
    $this->assertTrue(
      $field->getMandatory()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Email
   * @dataProvider provideValidEmailInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new Email('Email', 'email');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Email
   * @dataProvider provideInvalidEmailInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new Email('Email', 'email');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Email::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $field = new Email('Email', 'email');
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($document->appendElement('test'));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<test>
        <field caption="Email" class="DialogFieldInputEmail" error="no">
          <input type="email" name="email" maxlength="1024"/>
        </field>
      </test>',
      $document->saveXML($document->documentElement)
    );
  }

  public static function provideValidEmailInputs() {
    return array(
      array('unit@example.com', TRUE),
      array('unit@example.com', FALSE),
      array('', FALSE),
    );
  }

  public static function provideInvalidEmailInputs() {
    return array(
      array(':unit@example.com', TRUE),
      array(':unit@example.com', FALSE),
      array('unit@example.', FALSE),
      array('', TRUE),
    );
  }
}
