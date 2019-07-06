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
   * @covers \Papaya\Database\Schema\Structure\KeyStructure
   */
  class KeyStructureTest extends TestCase {

    public function testCreateKey() {
      $key = new KeyStructure('key_name');
      $this->assertSame('key_name', $key->name);
    }

    public function testCreatePrimaryKey() {
      $key = new KeyStructure(KeyStructure::PRIMARY);
      $this->assertSame(KeyStructure::PRIMARY, $key->name);
    }

    public function testCreatePrimaryKeyWithOneField() {
      $key = new KeyStructure(KeyStructure::PRIMARY);
      $key->fields[] = new KeyFieldStructure('id_field');
      $this->assertSame('id_field', $key->fields['id_field']->name);
    }

    public function testCreateKeyWithEmptyNameExpectingException() {
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Key name can not be empty.');
      new KeyStructure('');
    }
    /**
     * @param KeyStructure $expectedField
     * @param $xml
     * @dataProvider provideXMLAndKeyFields
     */
    public function testCreateFromXML($expectedField, $xml) {
      $document = new Document();
      $document->loadXML($xml);
      $field = KeyStructure::createFromXML($document->documentElement);
      $this->assertEquals($expectedField, $field);
    }

    public static function provideXMLAndKeyFields() {
      return [
        'primary key' => [
          new KeyStructure(KeyStructure::PRIMARY),
          '<primary-key/>'
        ],
        'named key' => [
          new KeyStructure('foo'),
          '<key name="foo"/>'
        ],
        'primary key with field' => [
          self::createKeyWithFields(
            KeyStructure::PRIMARY, [new KeyFieldStructure('id_field')]
          ),
          '<primary-key><field>id_field</field></primary-key>'
        ]
      ];
    }

    /**
     * @param string $name
     * @param KeyFieldStructure[] $fields
     * @return KeyStructure
     */
    private static function createKeyWithFields($name, array $fields) {
      $key = new KeyStructure(KeyStructure::PRIMARY);
      foreach ($fields as $field) {
        $key->fields[] = $field;
      }
      return $key;
    }
  }
}
