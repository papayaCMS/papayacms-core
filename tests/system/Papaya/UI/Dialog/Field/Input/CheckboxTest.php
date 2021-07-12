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

namespace Papaya\UI\Dialog\Field\Input {

  use Papaya\Request\Parameters;
  use Papaya\UI\Dialog;
  use Papaya\UI\Dialog\Fields;

  require_once __DIR__.'/../../../../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Checkbox
   */
  class CheckboxTest extends \Papaya\TestFramework\TestCase {

    public function testConstructor() {
      $checkbox = new Checkbox('caption', 'name', TRUE, TRUE);
      $this->assertEquals(
        TRUE, $checkbox->getMandatory()
      );
    }

    public function testGetFilterWithMandatoryTrue() {
      $checkbox = new Checkbox('caption', 'name', TRUE, TRUE);
      $this->assertInstanceOf(\Papaya\Filter::class, $checkbox->getFilter());
    }

    public function testGetFilterWithMandatoryFalse() {
      $checkbox = new Checkbox('caption', 'name', TRUE, FALSE);
      $this->assertNull($checkbox->getFilter());
    }

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

    public function testSetValues() {
      $checkbox = new Checkbox('caption', 'name', TRUE);
      $checkbox->setValues('yes', 'no');
      $this->assertEquals(
        ['active' => 'yes', 'inactive' => 'no'], $checkbox->getValues()
      );
    }

    public function testSetValuesWithEmptyActiveValueExpectingException() {
      $checkbox = new Checkbox('caption', 'name', TRUE);
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage('The active value can not be empty.');
      $checkbox->setValues('', 'false');
    }

    public function testSetValuesWithEqualValuesExpectingException() {
      $checkbox = new Checkbox('caption', 'name', TRUE);
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage('The active value and the inactive value must be different.');
      $checkbox->setValues('yes', 'yes');
    }

    /**
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

    public function testGetCurrentValueIfDisabled() {
      $dialog = $this->createMock(Dialog::class);
      $fields = $this->createMock(Fields::class);
      $fields
        ->method('hasOwner')
        ->willReturn(TRUE);
      $fields
        ->method('owner')
        ->willReturn($dialog);

      $checkbox = new Checkbox('caption', 'a-name', true);
      $checkbox->collection($fields);
      $checkbox->setDisabled(TRUE);
      $this->assertTrue($checkbox->getCurrentValue());
    }

    public function testGetCurrentValueIfDialogIsNotSubmitted() {
      $parameters = new Parameters(['a-name' => 1]);
      $dialog = $this->createMock(Dialog::class);
      $dialog
        ->method('isSubmitted')
        ->willReturn(FALSE);
      $dialog
        ->method('data')
        ->willReturn($parameters);
      $fields = $this->createMock(Fields::class);
      $fields
        ->method('hasOwner')
        ->willReturn(TRUE);
      $fields
        ->method('owner')
        ->willReturn($dialog);

      $checkbox = new Checkbox('caption', 'a-name', true);
      $checkbox->collection($fields);
      $this->assertTrue($checkbox->getCurrentValue());
    }

    public function testGetCurrentValueIfDialogIsNotSubmittedAndEmptyData() {
      $parameters = new Parameters();
      $dialog = $this->createMock(Dialog::class);
      $dialog
        ->method('isSubmitted')
        ->willReturn(FALSE);
      $dialog
        ->method('data')
        ->willReturn($parameters);
      $fields = $this->createMock(Fields::class);
      $fields
        ->method('hasOwner')
        ->willReturn(TRUE);
      $fields
        ->method('owner')
        ->willReturn($dialog);

      $checkbox = new Checkbox('caption', 'a-name', true);
      $checkbox->collection($fields);
      $this->assertTrue($checkbox->getCurrentValue());
    }

    public function testGetCurrentValueIfDialogIsSubmitted() {
      $parameters = new Parameters(['a-name' => 1]);
      $dialog = $this->createMock(Dialog::class);
      $dialog
        ->method('isSubmitted')
        ->willReturn(TRUE);
      $dialog
        ->method('parameters')
        ->willReturn($parameters);
      $fields = $this->createMock(Fields::class);
      $fields
        ->method('hasOwner')
        ->willReturn(TRUE);
      $fields
        ->method('owner')
        ->willReturn($dialog);

      $checkbox = new Checkbox('caption', 'a-name', true);
      $checkbox->collection($fields);
      $this->assertTrue($checkbox->getCurrentValue());
    }

    /***************************
     * Data Provider
     ***************************/

    public static function provideValidCheckboxInputs() {
      return [
        ['yes', TRUE],
        ['no', FALSE],
        ['1', FALSE],
        [NULL, FALSE],
        ['foo', FALSE],
      ];
    }

    public static function provideInvalidCheckboxInputs() {
      return [
        ['no', TRUE],
        [NULL, TRUE],
        ['foo', TRUE]
      ];
    }

    public static function provideCheckboxValues() {
      return [
        [TRUE, TRUE, TRUE, FALSE],
        [FALSE, FALSE, TRUE, FALSE],
        ['yes', 'yes', 'yes', 'no'],
        ['no', 'no', 'yes', 'no'],
        ['no', NULL, 'yes', 'no'],
        ['no', '', 'yes', 'no'],
        [1, '1', 1, 0],
        ['no', 'unknown', 'yes', 'no'],
      ];
    }
  }
}
