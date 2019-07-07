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
   * @covers \Papaya\Database\Schema\Structure\TableStructure
   */
  class TableStructureTest extends TestCase {

    const VALID_XML_TABLE_STRUCTURE = '<table name="test_table" prefix="yes">
          <fields>
            <field name="test_id" type="integer" size="4" auto-increment="yes"/>
            <field name="test_field" type="text" size="255" default="default_value"/>
          </fields>
          <keys>
            <primary-key>
              <field>test_id</field>
            </primary-key>
            <key name="test_field">
              <field>test_field</field>
            </key>
           </keys>
        </table>';

    public function testCreateFromXML() {
      $document = new Document();
      $document->loadXML(self::VALID_XML_TABLE_STRUCTURE);
      $structure = TableStructure::createFromXML($document);
      $this->assertSame('test_table', $structure->name);
      $this->assertTrue($structure->usePrefix);
      $this->assertCount(2, $structure->fields);
      $this->assertCount(2, $structure->indizes);
    }

    public function testGetXMLCreatesLoadedXML() {
      $document = new Document();
      $document->loadXML(self::VALID_XML_TABLE_STRUCTURE);
      $structure = TableStructure::createFromXML($document);

      $this->assertXmlStringEqualsXmlString($xml, $structure->getXMLDocument()->saveXML());
    }

    public function testCreateFromXMLWithEmptyXMLExpectingException() {
      $document = new Document();
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Can not find "table" element.');
      TableStructure::createFromXML($document);
    }

    public function testCreateFromXMLWithEmptyTableNameXMLExpectingException() {
      $document = new Document();
      $document->loadXML('<table/>');
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Table name can not be empty.');
      TableStructure::createFromXML($document);
    }

    public function testCreateFromXMLWithTwoPrimaryKeysXMLExpectingException() {
      $xml = '<table name="test_table" prefix="yes">
          <fields>
            <field name="test_id" type="integer" size="4" autoinc="yes"/>
            <field name="test_field" type="string" size="255" default="default_value" null="no"/>
          </fields>
          <keys>
            <primary-key>
              <field>test_id</field>
            </primary-key>
            <primary-key>
              <field>test_field</field>
            </primary-key>
           </keys>
        </table>';
      $document = new Document();
      $document->loadXML($xml);
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Table has more then one primary key.');
      TableStructure::createFromXML($document);
    }

    public function testCloneShouldDuplicateSubObjects() {
      $document = new Document();
      $document->loadXML(self::VALID_XML_TABLE_STRUCTURE);
      $structure = TableStructure::createFromXML($document);
      $clonedStructure = clone $structure;
      $this->assertEquals($structure, $clonedStructure);
      $this->assertNotSame($structure->fields, $clonedStructure->fields);
      $this->assertNotSame($structure->indizes, $clonedStructure->indizes);
    }
  }

}
