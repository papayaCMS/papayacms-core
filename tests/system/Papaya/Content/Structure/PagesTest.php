<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

use Papaya\Content\Structure\Page;
use Papaya\Content\Structure\Pages;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentStructurePagesTest extends \PapayaTestCase {

  /**
   * @covers Pages::__construct
   */
  public function testConstructor() {
    $pages = new Pages();
    $this->assertEquals(Page::class, $pages->getItemClass());
  }

  /**
   * @covers Pages::load
   */
  public function testLoad() {
    $document = new \Papaya\XML\Document();
    $document->load(__DIR__.'/../TestData/structure.xml');
    $pages = new Pages();
    $pages->load($document->documentElement);
    $this->assertCount(1, $pages);
    $this->assertEquals('Sample Page 1', $pages[0]->title);
    $this->assertEquals('MAIN', $pages[0]->name);
    $this->assertCount(3, $pages[0]->groups());
  }
}
