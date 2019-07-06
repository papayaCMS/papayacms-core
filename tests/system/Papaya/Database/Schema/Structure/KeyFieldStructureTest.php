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
   * @covers \Papaya\Database\Schema\Structure\KeyFieldStructure
   */
  class KeyFieldStructureTest extends TestCase {

    public function testCreateFieldWithoutSize() {
      $field = new KeyFieldStructure('field');
      $this->assertSame('field', $field->name);
      $this->assertSame(0, $field->size);
    }

    public function testCreateFieldWithSize() {
      $field = new KeyFieldStructure('field', 21);
      $this->assertSame('field', $field->name);
      $this->assertSame(21, $field->size);
    }

    public function testCreateFieldWithEmptyNameExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Field name can not be empty.');
      new KeyFieldStructure('');
    }

    /**
     * @param KeyFieldStructure $expectedField
     * @param $xml
     * @dataProvider provideXMLAndKeyFields
     */
    public function testCreateFromXML($expectedField, $xml) {
      $document = new Document();
      $document->loadXML($xml);
      $field = KeyFieldStructure::createFromXML($document->documentElement);
      $this->assertEquals($expectedField, $field);
    }

    public static function provideXMLAndKeyFields() {
      return [
        'simple field' => [
          new KeyFieldStructure('foo'),
          '<field>foo</field>'
        ],
        'simple field, trim whitespace' => [
          new KeyFieldStructure('foo'),
          '<field>  foo  </field>'
        ],
        'field, with size' => [
          new KeyFieldStructure('foo', 42),
          '<field size="42">foo</field>'
        ],
      ];
    }
  }

}
