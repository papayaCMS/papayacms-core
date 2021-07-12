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

namespace Papaya\UI\Dialog\Field\Input {

  use Papaya\Request\Parameters;
  use Papaya\TestFramework\TestCase;
  use Papaya\UI\Dialog;
  use Papaya\UI\Dialog\Fields;

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\MappedValue
   */
  class MappedValueTest extends TestCase {

    public function testGetXML() {
      $field = new MappedValue(
        'caption', 'field_name'
      );
      $field->callbacks()->mapToDisplay = static function($value) { return strtoupper($value); };
      $field->setDefaultValue('foo');
      $this->assertXmlStringEqualsXmlString(
        '<field caption="caption" class="DialogFieldInputMappedValue" error="no">
          <input maxlength="1024" name="field_name" type="text">FOO</input>
        </field>',
        $field->getXML()
      );
    }

    public function testGetXMLWithoutCallback() {
      $field = new MappedValue(
        'caption', 'field_name'
      );
      $field->setDefaultValue('foo');
      $this->assertXmlStringEqualsXmlString(
        '<field caption="caption" class="DialogFieldInputMappedValue" error="no">
          <input maxlength="1024" name="field_name" type="text">foo</input>
        </field>',
        $field->getXML()
      );
    }

    /**
     * @param array $expected
     * @param string $parameterValue
     * @testWith
     *   ["foo", "foo"]
     *   ["foo", "Foo"]
     *   ["foo", "FOO"]
     */
    public function testGetCurrentValueFromParameter($expected, $parameterValue) {
      $parameters = new Parameters(
        ['field_name' => $parameterValue]
      );

      $dialog = $this->createMock(Dialog::class);
      $dialog
        ->method('parameters')
        ->willReturn($parameters);

      /** @var \PHPUnit_Framework_MockObject_MockObject|Fields $fields */
      $fields = $this->createMock(Fields::class);
      $fields
        ->method('hasOwner')
        ->willReturn(TRUE);
      $fields
        ->method('owner')
        ->willReturn($dialog);

      $field = new MappedValue(
        'caption', 'field_name'
      );
      $field->collection($fields);
      $field->callbacks()->mapFromDisplay = static function($input) { return strtolower($input); };
      $this->assertSame(
        $expected,
        $field->getCurrentValue()
      );
    }

    public function testGetCurrentValueFromParameterWithoutCallback() {
      $parameters = new Parameters(
        ['field_name' => 'FOO']
      );

      $dialog = $this->createMock(Dialog::class);
      $dialog
        ->method('parameters')
        ->willReturn($parameters);

      /** @var \PHPUnit_Framework_MockObject_MockObject|Fields $fields */
      $fields = $this->createMock(Fields::class);
      $fields
        ->method('hasOwner')
        ->willReturn(TRUE);
      $fields
        ->method('owner')
        ->willReturn($dialog);

      $field = new MappedValue(
        'caption', 'field_name'
      );
      $field->collection($fields);
      $this->assertSame(
        'FOO',
        $field->getCurrentValue()
      );
    }

    public function testGetCurrentValueFromDialogData() {
      $parameters = new Parameters();
      $data = new Parameters(
        ['field_name' => 'FOO']
      );

      $dialog = $this->createMock(Dialog::class);
      $dialog
        ->method('parameters')
        ->willReturn($parameters);
      $dialog
        ->method('data')
        ->willReturn($data);

      /** @var \PHPUnit_Framework_MockObject_MockObject|Fields $fields */
      $fields = $this->createMock(Fields::class);
      $fields
        ->method('hasOwner')
        ->willReturn(TRUE);
      $fields
        ->method('owner')
        ->willReturn($dialog);

      $field = new MappedValue(
        'caption', 'field_name'
      );
      $field->collection($fields);
      $field->callbacks()->mapFromDisplay = static function($input) { return strtolower($input); };

      $this->assertSame(
        'FOO',
        $field->getCurrentValue()
      );
    }

    public function testCallbacksGetAfterSet() {
      $field = new MappedValue(
        'caption', 'field_name'
      );
      /** @var \PHPUnit_Framework_MockObject_MockObject|MappedValue\Callbacks $callbacks */
      $callbacks = $this->createMock(MappedValue\Callbacks::class);
      $this->assertSame($callbacks, $field->callbacks($callbacks));
    }
  }

}
