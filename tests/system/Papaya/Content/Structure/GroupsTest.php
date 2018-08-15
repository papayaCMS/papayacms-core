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

namespace Papaya\Content\Structure;

require_once __DIR__.'/../../../../bootstrap.php';

class GroupsTest extends \PapayaTestCase {

  /**
   * @covers Groups::__construct
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Page $page */
    $page = $this
      ->getMockBuilder(Page::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new Groups($page);
    $this->assertEquals(Group::class, $groups->getItemClass());
  }

  /**
   * @covers Groups::load
   */
  public function testLoad() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Page $page */
    $page = $this
      ->getMockBuilder(Page::class)
      ->disableOriginalConstructor()
      ->getMock();
    $document = new \Papaya\XML\Document();
    $document->load(__DIR__.'/../TestData/structure.xml');
    $groups = new Groups($page);
    $groups->load($document->xpath()->evaluate('//page[1]')->item(0));
    $this->assertCount(3, $groups);
    $this->assertEquals('Sample Group 1.1', $groups[0]->title);
    $this->assertEquals('FONT', $groups[0]->name);
    $this->assertCount(1, $groups[0]->values());
  }
}
