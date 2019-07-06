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
   * @covers \Papaya\Database\Schema\Structure\FieldsStructure
   */
  class FieldsStructureTest extends TestCase {

    public function testAddValidField() {
      $fields = new FieldsStructure();
      $fields[] = new FieldStructure('test_field', FieldStructure::TYPE_STRING, 42);
      $this->assertTrue(isset($fields['test_field']));
    }

    public function testAppendTo() {
      $fields = new FieldsStructure();
      $fields[] = new FieldStructure('test_field', FieldStructure::TYPE_STRING, 42);
      $document = new Document();
      $document->appendElement('table', $fields);
      $this->assertXmlStringEqualsXmlString(
        '<table>  
            <fields>
              <field name="test_field" size="42" type="text"/>
            </fields>
          </table>',
        $document->saveXML()
      );
    }
  }

}
