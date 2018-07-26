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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputCheckboxTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::__construct
  */
  public function testConstructor() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', TRUE, TRUE);
    $this->assertEquals(
      TRUE, $checkbox->getMandatory()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::getFilter
  */
  public function testGetFilterWithMandatoryTrue() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', TRUE, TRUE);
    $this->assertInstanceOf(\Papaya\Filter::class, $checkbox->getFilter());
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::getFilter
  */
  public function testGetFilterWithMandatoryFalse() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', TRUE, FALSE);
    $this->assertNull($checkbox->getFilter());
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::appendTo
  */
  public function testAppendToWithCheckedCheckbox() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', TRUE, TRUE);
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="no" mandatory="yes">
        <input type="checkbox" name="name" checked="checked">1</input>
      </field>',
      $checkbox->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::appendTo
  */
  public function testAppendToWithUncheckedCheckbox() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', FALSE, TRUE);
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="yes" mandatory="yes">
        <input type="checkbox" name="name">1</input>
      </field>',
      $checkbox->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::appendTo
  */
  public function testAppendToWithUncheckedCheckboxNotMandatory() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', FALSE, FALSE);
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="no">
        <input type="checkbox" name="name">1</input>
      </field>',
      $checkbox->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::appendTo
  */
  public function testAppendToWithChangedValuesCheckbox() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', 'yes', TRUE);
    $checkbox->setValues('yes', 'no');
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="no" mandatory="yes">
        <input type="checkbox" name="name" checked="checked">yes</input>
      </field>',
      $checkbox->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::appendTo
  */
  public function testAppendToWithChangedValuesAndUncheckedCheckbox() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', 'no', FALSE);
    $checkbox->setValues('yes', 'no');
    $checkbox->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="caption" class="DialogFieldInputCheckbox" error="no">
        <input type="checkbox" name="name">yes</input>
      </field>',
      $checkbox->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::setValues
  */
  public function testSetValues() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', TRUE);
    $checkbox->setValues('yes', 'no');
    $this->assertAttributeEquals(
      array('active' => 'yes', 'inactive' => 'no'), '_values', $checkbox
    );
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::setValues
  */
  public function testSetValuesWithEmptyActiveValueExpectingException() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', TRUE);
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The active value can not be empty.');
    $checkbox->setValues('', 'false');
  }

  /**
  * @covers \PapayaUiDialogFieldInputCheckbox::setValues
  */
  public function testSetValuesWithEqualValuesExpectingException() {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', TRUE);
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The active value and the inactive value must be different.');
    $checkbox->setValues('yes', 'yes');
  }

  /**
   * @covers \PapayaUiDialogFieldInputCheckbox::setValues
   * @dataProvider provideValidCheckboxInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', $value, $mandatory);
    $checkbox->setValues('yes', 'no');
    $this->assertTrue(
      $checkbox->validate()
    );
  }

  /**
   * @covers \PapayaUiDialogFieldInputCheckbox::setValues
   * @dataProvider provideInvalidCheckboxInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', $value, $mandatory);
    $checkbox->setValues('yes', 'no');
    $this->assertFalse(
      $checkbox->validate()
    );
  }

  /**
   * @coversPapayaUiDialogFieldInputCheckbox::getCurrentValue
   * @dataProvider provideCheckboxValues
   * @param mixed $expected
   * @param mixed $default
   * @param mixed $active
   * @param mixed $inactive
   */
  public function testGetCurrentValue($expected, $default, $active, $inactive) {
    $checkbox = new \PapayaUiDialogFieldInputCheckbox('caption', 'name', $default);
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
