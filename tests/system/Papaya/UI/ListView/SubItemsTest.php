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

namespace Papaya\UI\ListView;
require_once __DIR__.'/../../../../bootstrap.php';

class SubItemsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\ListView\SubItems::__construct
   * @covers \Papaya\UI\ListView\SubItems::owner
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Item $item */
    $item = $this
      ->getMockBuilder(Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $subitems = new SubItems($item);
    $this->assertSame(
      $item, $subitems->owner()
    );
  }

  /**
   * @covers \Papaya\UI\ListView\SubItems::getListItem
   */
  public function testGetListItem() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Item $item */
    $item = $this
      ->getMockBuilder(Item::class)
      ->disableOriginalConstructor()
      ->getMock();
    $subitems = new SubItems($item);
    $this->assertSame(
      $item, $subitems->getListItem()
    );

  }
}
