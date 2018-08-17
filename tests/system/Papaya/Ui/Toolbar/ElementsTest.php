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

namespace Papaya\UI\Toolbar;
require_once __DIR__.'/../../../../bootstrap.php';

class ElementsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Toolbar\Elements::__construct
   * @covers \Papaya\UI\Toolbar\Elements::owner
   */
  public function testConstructor() {
    $menu = $this->createMock(\Papaya\UI\Menu::class);
    $elements = new Elements($menu);
    $this->assertSame(
      $menu, $elements->owner()
    );
  }

  /**
   * @covers \Papaya\UI\Toolbar\Elements::validateItemClass
   */
  public function testAddElementWhileGroupsAllowed() {
    $elements = new Elements($this->createMock(\Papaya\UI\Menu::class));
    $elements->allowGroups = TRUE;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Group $group */
    $group = $this
      ->getMockBuilder(Group::class)
      ->setConstructorArgs(array('caption'))
      ->getMock();
    $elements->add($group);
    $this->assertEquals(
      $group, $elements[0]
    );
  }

  /**
   * @covers \Papaya\UI\Toolbar\Elements::validateItemClass
   */
  public function testAddElementWhileGroupsNotAllowedExpectingException() {
    $elements = new Elements($this->createMock(\Papaya\UI\Menu::class));
    $elements->allowGroups = FALSE;
    $group = new Group('caption');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Invalid item class "Papaya\UI\Toolbar\Group".');
    $elements->add($group);
  }
}
