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

class GroupTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Structure\Group::__construct
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Page $page */
    $page = $this->createMock(Page::class);
    $group = new Group($page);
    $this->assertSame($page, $group->getPage());
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Group::values
   */
  public function testGroupsGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Page $page */
    $page = $this->createMock(Page::class);
    $group = new Group($page);
    $values = $this
      ->getMockBuilder(Values::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group->values($values);
    $this->assertSame($values, $group->values());
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Group::values
   */
  public function testGroupsGetImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Page $page */
    $page = $this->createMock(Page::class);
    $group = new Group($page);
    $this->assertInstanceOf(Values::class, $group->values());
  }

  /**
   * @covers \Papaya\CMS\Content\Structure\Group::getIdentifier
   */
  public function testGetIdentifier() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Page $page */
    $page = $this->createMock(Page::class);
    $page
      ->expects($this->once())
      ->method('getIdentifier')
      ->will($this->returnValue('PAGE'));
    $group = new Group($page);
    $group->name = 'GROUP';
    $this->assertEquals(
      'PAGE/GROUP', $group->getIdentifier()
    );
  }
}
