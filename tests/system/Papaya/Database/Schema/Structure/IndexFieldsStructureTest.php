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

  use Papaya\TestFramework\TestCase;
  use Papaya\XML\Document;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Schema\Structure\IndexFieldsStructure
   */
  class IndexFieldsStructureTest extends TestCase {

    public function testAddValidKey() {
      $keyFields = new IndexFieldsStructure();
      $keyFields[] = new IndexFieldStructure('test_field');
      $this->assertTrue(isset($keyFields['test_field']));
    }

    public function testAppendTo() {
      $keyFields = new IndexFieldsStructure();
      $keyFields[] = new IndexFieldStructure('one');
      $document = new Document();
      $document->appendElement('key', $keyFields);
      $this->assertXmlStringEqualsXmlString(
        '<key>
              <field>one</field>
            </key>',
        $document->saveXML()
      );
    }
  }
}
