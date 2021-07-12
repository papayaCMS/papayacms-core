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
   * @covers \Papaya\UI\Dialog\Field\Input\MultipleValues
   */
  class MultipleValuesTest extends TestCase {

    public function testGetXML() {
      $field = new MultipleValues(
        'caption', 'field_name'
      );
      $field->setDefaultValue(['foo', 'bar']);
      $this->assertXmlStringEqualsXmlString(
        '<field caption="caption" class="DialogFieldInputMultipleValues" error="no">
          <input maxlength="1024" name="field_name" type="text">foo,bar</input>
        </field>',
        $field->getXML()
      );
    }

    public function testGetXMLWithoutValue() {
      $field = new MultipleValues(
        'caption', 'field_name'
      );
      $this->assertXmlStringEqualsXmlString(
        '<field caption="caption" class="DialogFieldInputMultipleValues" error="no">
          <input maxlength="1024" name="field_name" type="text"/>
        </field>',
        $field->getXML()
      );
    }

    public function testGetXMLWidthDifferentSeparator() {
      $field = new MultipleValues(
        'caption', 'field_name'
      );
      $field->setDefaultValue(['foo', 'bar']);
      $field->setSeparator('|');
      $this->assertXmlStringEqualsXmlString(
        '<field caption="caption" class="DialogFieldInputMultipleValues" error="no">
          <input maxlength="1024" name="field_name" type="text">foo|bar</input>
        </field>',
        $field->getXML()
      );
    }

    /**
     * @param array $expected
     * @param string $parameterValue
     * @testWith
     *   [["foo"], "foo"]
     *   [["foo","bar"], "foo,bar"]
     *   [["foo","bar"], "  foo,  bar"  ]
     *   [["foo","bar"], "  foo,,,  bar"  ]
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

      $field = new MultipleValues(
        'caption', 'field_name'
      );
      $field->collection($fields);
      $this->assertSame(
        $expected,
        $field->getCurrentValue()
      );
    }

    /**
     * @param array $expected
     * @param string $parameterValue
     * @testWith
     *   [["foo"], "foo"]
     *   [["foo","bar"], "foo;bar"]
     *   [["foo,bar"], "foo,bar"]
     */
    public function testGetCurrentValueFromParameterWithSemicolonSeparator($expected, $parameterValue) {
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

      $field = new MultipleValues(
        'caption', 'field_name'
      );
      $field->setSeparator(';');
      $field->collection($fields);
      $this->assertSame(
        $expected,
        $field->getCurrentValue()
      );
    }

    public function testGetCurrentValueFromDialogData() {
      $parameters = new Parameters();
      $data = new Parameters(
        ['field_name' => ['foo', 'bar']]
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

      $field = new MultipleValues(
        'caption', 'field_name'
      );
      $field->collection($fields);
      $this->assertSame(
        ['foo', 'bar'],
        $field->getCurrentValue()
      );
    }
  }

}
