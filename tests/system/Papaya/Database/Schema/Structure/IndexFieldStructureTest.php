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
   * @covers \Papaya\Database\Schema\Structure\IndexFieldStructure
   */
  class IndexFieldStructureTest extends TestCase {

    public function testCreateFieldWithoutSize() {
      $field = new IndexFieldStructure('field');
      $this->assertSame('field', $field->name);
      $this->assertSame(0, $field->size);
    }

    public function testCreateFieldWithSize() {
      $field = new IndexFieldStructure('field', 21);
      $this->assertSame('field', $field->name);
      $this->assertSame(21, $field->size);
    }

    public function testCreateFieldWithEmptyNameExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Field name can not be empty.');
      new IndexFieldStructure('');
    }

    /**
     * @param IndexFieldStructure $expectedField
     * @param $xml
     * @dataProvider provideXMLAndKeyFields
     */
    public function testCreateFromXML($expectedField, $xml) {
      $document = new Document();
      $document->loadXML($xml);
      $field = IndexFieldStructure::createFromXML($document->documentElement);
      $this->assertEquals($expectedField, $field);
    }

    public function testAppendTo() {
      $field = new IndexFieldStructure('test_field');
      $document = new Document();
      $document->appendElement('key', $field);
      $this->assertXmlStringEqualsXmlString(
        '<key>
              <field>test_field</field>
            </key>',
        $document->saveXML()
      );
    }

    public function testAppendToWithSize() {
      $field = new IndexFieldStructure('test_field', 42);
      $document = new Document();
      $document->appendElement('key', $field);
      $this->assertXmlStringEqualsXmlString(
        '<key>
              <field size="42">test_field</field>
            </key>',
        $document->saveXML()
      );
    }

    public static function provideXMLAndKeyFields() {
      return [
        'simple field' => [
          new IndexFieldStructure('foo'),
          '<field>foo</field>'
        ],
        'simple field, trim whitespace' => [
          new IndexFieldStructure('foo'),
          '<field>  foo  </field>'
        ],
        'field, with size' => [
          new IndexFieldStructure('foo', 42),
          '<field size="42">foo</field>'
        ],
      ];
    }
  }

}
