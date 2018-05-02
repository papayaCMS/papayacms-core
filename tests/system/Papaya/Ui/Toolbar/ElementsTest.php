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

class PapayaUiToolbarElementsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarElements::__construct
  * @covers PapayaUiToolbarElements::owner
  */
  public function testConstructor() {
    $menu = $this->createMock(PapayaUiMenu::class);
    $elements = new PapayaUiToolbarElements($menu);
    $this->assertSame(
      $menu, $elements->owner()
    );
  }

  /**
  * @covers PapayaUiToolbarElements::validateItemClass
  */
  public function testAddElementWhileGroupsAllowed() {
    $elements = new PapayaUiToolbarElements($this->createMock(PapayaUiMenu::class));
    $elements->allowGroups = TRUE;
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiToolbarGroup $group */
    $group = $this
      ->getMockBuilder(PapayaUiToolbarGroup::class)
      ->setConstructorArgs(array('caption'))
      ->getMock();
    $elements->add($group);
    $this->assertEquals(
      $group, $elements[0]
    );
  }

  /**
  * @covers PapayaUiToolbarElements::validateItemClass
  */
  public function testAddElementWhileGroupsNotAllowedExpectingException() {
    $elements = new PapayaUiToolbarElements($this->createMock(PapayaUiMenu::class));
    $elements->allowGroups = FALSE;
    $group = new PapayaUiToolbarGroup('caption');
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Invalid item class "PapayaUiToolbarGroup".');
    $elements->add($group);
  }
}
