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
   * @covers \Papaya\Database\Schema\Structure\IndexStructure
   */
  class IndexStructureTest extends TestCase {

    public function testCreateKey() {
      $index = new IndexStructure('key_name');
      $this->assertSame('key_name', $index->name);
      $this->assertFalse($index->isPrimary());
    }

    public function testCreatePrimaryKey() {
      $index = new IndexStructure(IndexStructure::PRIMARY);
      $this->assertSame(IndexStructure::PRIMARY, $index->name);
      $this->assertTrue($index->isPrimary());
    }

    public function testCreatePrimaryKeyWithOneField() {
      $index = new IndexStructure(IndexStructure::PRIMARY);
      $index->fields[] = new IndexFieldStructure('id_field');
      $this->assertSame('id_field', $index->fields['id_field']->name);
    }

    public function testCreateKeyWithEmptyNameExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Key name can not be empty.');
      new IndexStructure('');
    }
    /**
     * @param IndexStructure $expectedField
     * @param $xml
     * @dataProvider provideXMLAndKeyFields
     */
    public function testCreateFromXML($expectedField, $xml) {
      $document = new Document();
      $document->loadXML($xml);
      $field = IndexStructure::createFromXML($document->documentElement);
      $this->assertEquals($expectedField, $field);
    }

    public function testAppendTo() {
      $index = new IndexStructure('test_key');
      $index->fields[] = new IndexFieldStructure('test_field');
      $document = new Document();
      $document->appendElement('keys', $index);
      $this->assertXmlStringEqualsXmlString(
        '<keys>
            <key name="test_key">
              <field>test_field</field>
            </key>
          </keys>',
        $document->saveXML()
      );
    }

    public function testAppendToWithPrimaryKey() {
      $index = new IndexStructure(IndexStructure::PRIMARY);
      $index->fields[] = new IndexFieldStructure('id_field');
      $document = new Document();
      $document->appendElement('keys', $index);
      $this->assertXmlStringEqualsXmlString(
        '<keys>
            <primary-key>
              <field>id_field</field>
            </primary-key>
          </keys>',
        $document->saveXML()
      );
    }

    public function testAppendToWithUniqueKey() {
      $index = new IndexStructure('foo', TRUE);
      $document = new Document();
      $document->appendElement('keys', $index);
      $this->assertXmlStringEqualsXmlString(
        '<keys>
            <key name="foo" unique="yes"/>
          </keys>',
        $document->saveXML()
      );
    }

    public function testAppendToWithFullTextKey() {
      $index = new IndexStructure('foo', FALSE, TRUE);
      $document = new Document();
      $document->appendElement('keys', $index);
      $this->assertXmlStringEqualsXmlString(
        '<keys>
            <key name="foo" fulltext="yes"/>
          </keys>',
        $document->saveXML()
      );
    }

    public static function provideXMLAndKeyFields() {
      return [
        'primary key' => [
          new IndexStructure(IndexStructure::PRIMARY),
          '<primary-key/>'
        ],
        'named key' => [
          new IndexStructure('foo'),
          '<key name="foo"/>'
        ],
        'primary key with field' => [
          self::createKeyWithFields(
            IndexStructure::PRIMARY, [new IndexFieldStructure('id_field')]
          ),
          '<primary-key><field>id_field</field></primary-key>'
        ]
      ];
    }

    /**
     * @param string $name
     * @param IndexFieldStructure[] $fields
     * @return IndexStructure
     */
    private static function createKeyWithFields($name, array $fields) {
      $index = new IndexStructure(IndexStructure::PRIMARY);
      foreach ($fields as $field) {
        $index->fields[] = $field;
      }
      return $index;
    }
  }
}
