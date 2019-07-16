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
   * @covers \Papaya\Database\Schema\Structure\IndicesStructure
   */
  class IndicesStructureTest extends TestCase {

    public function testAddValidKey() {
      $indices = new IndicesStructure();
      $indices[] = new IndexStructure('test_key');
      $this->assertTrue(isset($indices['test_key']));
      $this->assertNull($indices->getPrimary());
    }

    public function testAddValidPrimaryKey() {
      $indices = new IndicesStructure();
      $indices[] = new IndexStructure(IndexStructure::PRIMARY);
      $this->assertTrue(isset($indices[IndexStructure::PRIMARY]));
      $this->assertInstanceOf(IndexStructure::class, $indices->getPrimary());
    }

    public function testAppendTo() {
      $indices = new IndicesStructure();
      $indices[] = new IndexStructure(IndexStructure::PRIMARY);
      $indices[] = new IndexStructure('test_key');
      $document = new Document();
      $document->appendElement('table', $indices);
      $this->assertXmlStringEqualsXmlString(
        '<table>
            <keys>
              <primary-key/>
              <key name="test_key"/>
            </keys>
          </table>',
        $document->saveXML()
      );
    }
  }

}
