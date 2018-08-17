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

class PhoneTest extends \PapayaTestCase {
  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Phone::__construct
   */
  public function testConstructor() {
    $field = new Phone('Phone', 'phone', '1234567890', TRUE);
    $this->assertEquals(
      'Phone',
      $field->caption
    );
    $this->assertEquals(
      'phone',
      $field->name
    );
    $this->assertEquals(
      '1234567890',
      $field->defaultValue
    );
    $this->assertTrue(
      $field->mandatory
    );
  }

  /**
   * @covers       \Papaya\UI\Dialog\Field\Input\Phone
   * @dataProvider provideValidPhoneInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new Phone('Phone', 'phone');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
   * @covers       \Papaya\UI\Dialog\Field\Input\Phone
   * @dataProvider provideInvalidPhoneInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new Phone('Phone', 'phone');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Phone::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $field = new Phone('Phone', 'phone');
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($document->appendElement('test'));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<test>
        <field caption="Phone" class="DialogFieldInputPhone" error="no">
          <input type="phone" name="phone" maxlength="1024"/>
        </field>
      </test>',
      $document->saveXML($document->documentElement)
    );
  }

  public static function provideValidPhoneInputs() {
    return array(
      array('1234567890', TRUE),
      array('1234567890', FALSE),
      array('', FALSE),
    );
  }

  public static function provideInvalidPhoneInputs() {
    return array(
      array(':1234567890', TRUE),
      array(':1234567890', FALSE),
      array('fsdjjsdf', FALSE),
      array('', TRUE),
    );
  }
}
