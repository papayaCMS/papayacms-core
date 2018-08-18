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

class CheckboxTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::__construct
   */
  public function testConstructor() {
    $checkbox = new Checkbox('caption', 'name', TRUE, TRUE);
    $this->assertEquals(
      TRUE, $checkbox->getMandatory()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::getFilter
   */
  public function testGetFilterWithMandatoryTrue() {
    $checkbox = new Checkbox('caption', 'name', TRUE, TRUE);
    $this->assertInstanceOf(\Papaya\Filter::class, $checkbox->getFilter());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::getFilter
   */
  public function testGetFilterWithMandatoryFalse() {
    $checkbox = new Checkbox('caption', 'name', TRUE, FALSE);
    $this->assertNull($checkbox->getFilter());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::appendTo
   */
  public function testAppendToWithCheckedCheckbox() {
    $checkbox = new Checkbox('caption', 'name', TRUE, TRUE);
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="no" mandatory="yes">
        <input type="checkbox" name="name" checked="checked">1</input>
      </field>',
      $checkbox->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::appendTo
   */
  public function testAppendToWithUncheckedCheckbox() {
    $checkbox = new Checkbox('caption', 'name', FALSE, TRUE);
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="yes" mandatory="yes">
        <input type="checkbox" name="name">1</input>
      </field>',
      $checkbox->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::appendTo
   */
  public function testAppendToWithUncheckedCheckboxNotMandatory() {
    $checkbox = new Checkbox('caption', 'name', FALSE, FALSE);
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="no">
        <input type="checkbox" name="name">1</input>
      </field>',
      $checkbox->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::appendTo
   */
  public function testAppendToWithChangedValuesCheckbox() {
    $checkbox = new Checkbox('caption', 'name', 'yes', TRUE);
    $checkbox->setValues('yes', 'no');
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="no" mandatory="yes">
        <input type="checkbox" name="name" checked="checked">yes</input>
      </field>',
      $checkbox->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::appendTo
   */
  public function testAppendToWithChangedValuesAndUncheckedCheckbox() {
    $checkbox = new Checkbox('caption', 'name', 'no', FALSE);
    $checkbox->setValues('yes', 'no');
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="no">
        <input type="checkbox" name="name">yes</input>
      </field>',
      $checkbox->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::setValues
   */
  public function testSetValues() {
    $checkbox = new Checkbox('caption', 'name', TRUE);
    $checkbox->setValues('yes', 'no');
    $this->assertAttributeEquals(
      array('active' => 'yes', 'inactive' => 'no'), '_values', $checkbox
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::setValues
   */
  public function testSetValuesWithEmptyActiveValueExpectingException() {
    $checkbox = new Checkbox('caption', 'name', TRUE);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The active value can not be empty.');
    $checkbox->setValues('', 'false');
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::setValues
   */
  public function testSetValuesWithEqualValuesExpectingException() {
    $checkbox = new Checkbox('caption', 'name', TRUE);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The active value and the inactive value must be different.');
    $checkbox->setValues('yes', 'yes');
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::setValues
   * @dataProvider provideValidCheckboxInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $checkbox = new Checkbox('caption', 'name', $value, $mandatory);
    $checkbox->setValues('yes', 'no');
    $this->assertTrue(
      $checkbox->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::setValues
   * @dataProvider provideInvalidCheckboxInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $checkbox = new Checkbox('caption', 'name', $value, $mandatory);
    $checkbox->setValues('yes', 'no');
    $this->assertFalse(
      $checkbox->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox::getCurrentValue
   * @dataProvider provideCheckboxValues
   * @param mixed $expected
   * @param mixed $default
   * @param mixed $active
   * @param mixed $inactive
   */
  public function testGetCurrentValue($expected, $default, $active, $inactive) {
    $checkbox = new Checkbox('caption', 'name', $default);
    $checkbox->setValues($active, $inactive);
    $this->assertSame(
      $expected, $checkbox->getCurrentValue()
    );
  }

  /***************************
   * Data Provider
   ***************************/

  public static function provideValidCheckboxInputs() {
    return array(
      array('yes', TRUE),
      array('no', FALSE),
      array('1', FALSE),
      array(NULL, FALSE),
      array('foo', FALSE),
    );
  }

  public static function provideInvalidCheckboxInputs() {
    return array(
      array('no', TRUE),
      array(NULL, TRUE),
      array('foo', TRUE)
    );
  }

  public static function provideCheckboxValues() {
    return array(
      array(TRUE, TRUE, TRUE, FALSE),
      array(FALSE, FALSE, TRUE, FALSE),
      array('yes', 'yes', 'yes', 'no'),
      array('no', 'no', 'yes', 'no'),
      array('no', NULL, 'yes', 'no'),
      array('no', '', 'yes', 'no'),
      array(1, '1', 1, 0),
      array('no', 'unknown', 'yes', 'no'),
    );
  }
}
