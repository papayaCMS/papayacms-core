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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiToolbarElementsTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Toolbar\Elements::__construct
  * @covers \Papaya\Ui\Toolbar\Elements::owner
  */
  public function testConstructor() {
    $menu = $this->createMock(\PapayaUiMenu::class);
    $elements = new \Papaya\Ui\Toolbar\Elements($menu);
    $this->assertSame(
      $menu, $elements->owner()
    );
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Elements::validateItemClass
  */
  public function testAddElementWhileGroupsAllowed() {
    $elements = new \Papaya\Ui\Toolbar\Elements($this->createMock(\PapayaUiMenu::class));
    $elements->allowGroups = TRUE;
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Toolbar\Group $group */
    $group = $this
      ->getMockBuilder(\Papaya\Ui\Toolbar\Group::class)
      ->setConstructorArgs(array('caption'))
      ->getMock();
    $elements->add($group);
    $this->assertEquals(
      $group, $elements[0]
    );
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Elements::validateItemClass
  */
  public function testAddElementWhileGroupsNotAllowedExpectingException() {
    $elements = new \Papaya\Ui\Toolbar\Elements($this->createMock(\PapayaUiMenu::class));
    $elements->allowGroups = FALSE;
    $group = new \Papaya\Ui\Toolbar\Group('caption');
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Invalid item class "Papaya\Ui\Toolbar\Group".');
    $elements->add($group);
  }
}
