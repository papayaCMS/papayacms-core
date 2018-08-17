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

namespace Papaya\UI\Hierarchy;
require_once __DIR__.'/../../../../bootstrap.php';

class MenuTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Hierarchy\Menu::appendTo
   */
  public function testAppendTo() {
    $items = $this->createMock(Items::class);
    $items
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));

    $menu = new Menu();
    $menu->items($items);

    $this->assertAppendedXmlEqualsXmlFragment(
    /** @lang XML */
      '<hierarchy-menu/>', $menu
    );
  }

  /**
   * @covers \Papaya\UI\Hierarchy\Menu::appendTo
   */
  public function testAppendToWithoutItemsExpectingEmptyString() {
    $items = $this->createMock(Items::class);
    $items
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $menu = new Menu();
    $menu->items($items);

    $this->assertAppendedXmlEqualsXmlFragment(
      '', $menu
    );
  }

  /**
   * @covers \Papaya\UI\Hierarchy\Menu::items
   */
  public function testItemsGetAfterSet() {
    $menu = new Menu();
    $items = $this->createMock(Items::class);
    $this->assertSame(
      $items, $menu->items($items)
    );
  }

  /**
   * @covers \Papaya\UI\Hierarchy\Menu::items
   */
  public function testItemsGetWithImpliciteCreate() {
    $menu = new Menu();
    $menu->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      Items::class, $menu->items()
    );
    $this->assertSame(
      $papaya, $menu->papaya()
    );
  }
}
