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

namespace Papaya\CMS\Content\Structure;

require_once __DIR__.'/../../../../../bootstrap.php';

class PageTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Structure\Page::__construct
   */
  public function testConstructor() {
    $page = new Page();
    $this->assertEquals('page', $page->name);
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Page::groups
   */
  public function testGroupsGetAfterSet() {
    $groups = $this
      ->getMockBuilder(Groups::class)
      ->disableOriginalConstructor()
      ->getMock();
    $page = new Page();
    $page->groups($groups);
    $this->assertSame($groups, $page->groups());
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Page::groups
   */
  public function testGroupsGetImplicitCreate() {
    $page = new Page();
    $this->assertInstanceOf(Groups::class, $page->groups());
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Page::getIdentifier
   */
  public function testGetIdentifier() {
    $page = new Page();
    $page->name = 'PAGE';
    $this->assertEquals(
      'PAGE', $page->getIdentifier()
    );
  }
}
