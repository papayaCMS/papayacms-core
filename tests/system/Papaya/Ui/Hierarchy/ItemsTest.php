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

class ItemsTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Hierarchy\Items::appendTo
   */
  public function testAppendToInheritance() {
    $items = new Items();
    $this->assertSame('', $items->getXML());
  }

  /**
   * @covers \Papaya\UI\Hierarchy\Items::appendTo
   */
  public function testAppendToWithLimit3() {
    $items = new Items();
    $items->limit = 3;
    $items->spacer = $this->getItemFixture(TRUE);
    $items[] = $this->getItemFixture(TRUE);
    $items[] = $this->getItemFixture(FALSE);
    $items[] = $this->getItemFixture(FALSE);
    $items[] = $this->getItemFixture(TRUE);
    $items[] = $this->getItemFixture(TRUE);

    $this->assertSame(/** @lang XML */
      '<items/>', $items->getXML());
  }

  /**
   * @covers \Papaya\UI\Hierarchy\Items::spacer
   */
  public function testSpacerGetAfterSet() {
    $items = new Items();
    $spacer = $this
      ->getMockBuilder(Item::class)
      ->setConstructorArgs(array('...'))
      ->getMock();
    $this->assertSame(
      $spacer, $items->spacer($spacer)
    );
  }

  /**
   * @covers \Papaya\UI\Hierarchy\Items::spacer
   */
  public function testSpacerGetWithImplicitCreate() {
    $items = new Items();
    $items->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      Item::class, $spacer = $items->spacer()
    );
    $this->assertSame(
      $papaya, $spacer->papaya()
    );
  }

  public function getItemFixture($expectAppend) {
    $item = $this
      ->getMockBuilder(Item::class)
      ->setConstructorArgs(array('item'))
      ->getMock();
    $item
      ->expects($expectAppend ? $this->once() : $this->never())
      ->method('appendTo');
    return $item;
  }
}
