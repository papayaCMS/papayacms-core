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

namespace Papaya\Database\Schema\Structure {

  use Papaya\Test\TestCase;
  use Papaya\XML\Document;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Schema\Structure\FieldStructure
   */
  class FieldStructureTest extends TestCase {

    public function testCreateStringField() {
      $field = new FieldStructure('test_field', FieldStructure::TYPE_TEXT, 42);
      $this->assertSame('test_field', $field->name);
      $this->assertSame(FieldStructure::TYPE_TEXT, $field->type);
      $this->assertSame(42, $field->size);
    }

    public function testCreateIntegerField() {
      $field = new FieldStructure('test_field', FieldStructure::TYPE_INTEGER, 4);
      $this->assertSame('test_field', $field->name);
      $this->assertSame(FieldStructure::TYPE_INTEGER, $field->type);
      $this->assertSame(4, $field->size);
    }

    public function testCreateDecimalField() {
      $field = new FieldStructure('test_field', FieldStructure::TYPE_DECIMAL, '4,2');
      $this->assertSame('test_field', $field->name);
      $this->assertSame(FieldStructure::TYPE_DECIMAL, $field->type);
      $this->assertSame([4, 2], $field->size);
    }

    /**
     * @dataProvider provideXMLAndFields
     * @param FieldStructure $expectedField
     * @param string $xml
     */
    public function testCreateFromXML($expectedField, $xml) {
      $document = new Document();
      $document->loadXML($xml);
      $field = FieldStructure::createFromXML($document->documentElement);
      $this->assertEquals($expectedField, $field);
    }

    public function testCreateWithEmptyFieldNameExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Field name can not be empty.');
      new FieldStructure('', FieldStructure::TYPE_TEXT, 1);
    }

    public function testCreateWithInvalidFieldTypeExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Invalid field type "invalid".');
      new FieldStructure('foo', 'invalid', 1);
    }

    public function testCreateWithAutoIncrementTextFieldExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Only integer fields can be auto increment.');
      new FieldStructure('foo', FieldStructure::TYPE_TEXT, 1, TRUE);
    }

    public function testCreateWithAutoIncrementAndAllowsNullIgnoresAllowsNull() {
      $field = new FieldStructure('foo', FieldStructure::TYPE_INTEGER, 1, TRUE, TRUE);
      $this->assertFalse($field->allowsNull);
    }

    public function testCreateIntegerWithNaNDefaultValueExpectingZero() {
      $field = new FieldStructure('foo', FieldStructure::TYPE_INTEGER, 1, FALSE, FALSE, NAN);
      $this->assertSame(0, $field->defaultValue);
    }

    public function testCreateDecimalWithNaNDefaultValueExpectingZero() {
      $field = new FieldStructure('foo', FieldStructure::TYPE_DECIMAL, 1, FALSE, FALSE, NAN);
      $this->assertSame(0, $field->defaultValue);
    }

    public function testAppendTo() {
      $field = new FieldStructure('test_field', FieldStructure::TYPE_TEXT, 42);
      $document = new Document();
      $document->appendElement('fields', $field);
      $this->assertXmlStringEqualsXmlString(
        '<fields>
              <field name="test_field" size="42" type="text"/>
            </fields>',
        $document->saveXML()
      );
    }

    public function testAppendToWithAutoIncrementField() {
      $field = new FieldStructure('test_field', FieldStructure::TYPE_INTEGER, 4, TRUE);
      $document = new Document();
      $document->appendElement('fields', $field);
      $this->assertXmlStringEqualsXmlString(
        '<fields>
              <field name="test_field" size="4" type="integer" auto-increment="yes"/>
            </fields>',
        $document->saveXML()
      );
    }

    public function testAppendToWithFieldAllowingNull() {
      $field = new FieldStructure('test_field', FieldStructure::TYPE_TEXT, 42, FALSE, TRUE);
      $document = new Document();
      $document->appendElement('fields', $field);
      $this->assertXmlStringEqualsXmlString(
        '<fields>
              <field name="test_field" size="42" type="text" allows-null="yes"/>
            </fields>',
        $document->saveXML()
      );
    }

    public function testAppendToWithDefaultValue() {
      $field = new FieldStructure('test_field', FieldStructure::TYPE_TEXT, 42, FALSe, FALSe, 'test');
      $document = new Document();
      $document->appendElement('fields', $field);
      $this->assertXmlStringEqualsXmlString(
        '<fields>
              <field name="test_field" size="42" type="text" default="test"/>
            </fields>',
        $document->saveXML()
      );
    }

    public static function provideXMLAndFields() {
      return [
        'decimal field with fraction' => [
          new FieldStructure('foo', FieldStructure::TYPE_DECIMAL, '42,21'),
          '<field name="foo" type="decimal" size="42,21"/>'
        ],
        'decimal field without fraction' => [
          new FieldStructure('foo', FieldStructure::TYPE_DECIMAL, 21),
          '<field name="foo" type="float" size="21"/>'
        ],
        'text field' => [
          new FieldStructure('foo', FieldStructure::TYPE_TEXT, 42),
          '<field name="foo" type="text" size="42"/>'
        ],
        'auto increment field' => [
          new FieldStructure('foo', FieldStructure::TYPE_INTEGER, 4, TRUE),
          '<field name="foo" type="int" size="4" auto-increment="yes"/>'
        ],
        'auto increment field, old attribute' => [
          new FieldStructure('foo', FieldStructure::TYPE_INTEGER, 4, TRUE),
          '<field name="foo" type="int" size="4" autoinc="yes"/>'
        ],
        'nullable field' => [
          new FieldStructure('foo', FieldStructure::TYPE_INTEGER, 4, FALSE, TRUE),
          '<field name="foo" type="int" size="4" allows-null="yes"/>'
        ],
        'nullable field, old attribute' => [
          new FieldStructure('foo', FieldStructure::TYPE_INTEGER, 4, FALSE, TRUE),
          '<field name="foo" type="int" size="4" null="yes"/>'
        ],
        'text field with default' => [
          new FieldStructure('foo', FieldStructure::TYPE_TEXT, 42, FALSE, FALSE, 'hello'),
          '<field name="foo" type="text" size="42" default="hello"/>'
        ],
        'integer field with to large size' => [
          new FieldStructure('foo', FieldStructure::TYPE_INTEGER, 8),
          '<field name="foo" type="integer" size="42"/>'
        ],
        'integer field with to small size' => [
          new FieldStructure('foo', FieldStructure::TYPE_INTEGER, 4),
          '<field name="foo" type="integer" size="0"/>'
        ],
        'decimal field with to small size' => [
          new FieldStructure('foo', FieldStructure::TYPE_DECIMAL, [6,5]),
          '<field name="foo" type="decimal" size="1,5"/>'
        ],
      ];
    }
  }

}
