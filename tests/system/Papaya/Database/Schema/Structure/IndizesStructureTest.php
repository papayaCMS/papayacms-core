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
   * @covers \Papaya\Database\Schema\Structure\IndizesStructure
   */
  class IndizesStructureTest extends TestCase {

    public function testAddValidKey() {
      $indizes = new IndizesStructure();
      $indizes[] = new IndexStructure('test_key');
      $this->assertTrue(isset($indizes['test_key']));
      $this->assertNull($indizes->getPrimary());
    }

    public function testAddValidPrimaryKey() {
      $indizes = new IndizesStructure();
      $indizes[] = new IndexStructure(IndexStructure::PRIMARY);
      $this->assertTrue(isset($indizes[IndexStructure::PRIMARY]));
      $this->assertInstanceOf(IndexStructure::class, $indizes->getPrimary());
    }

    public function testAppendTo() {
      $indizes = new IndizesStructure();
      $indizes[] = new IndexStructure(IndexStructure::PRIMARY);
      $indizes[] = new IndexStructure('test_key');
      $document = new Document();
      $document->appendElement('table', $indizes);
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
