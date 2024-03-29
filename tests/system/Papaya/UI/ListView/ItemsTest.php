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

class ItemsTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\ListView\Items::__construct
   * @covers \Papaya\UI\ListView\Items::owner
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\ListView $listview */
    $listview = $this->createMock(\Papaya\UI\ListView::class);
    $items = new Items($listview);
    $this->assertSame(
      $listview, $items->owner()
    );
  }

  /**
   * @covers \Papaya\UI\ListView\Items::reference
   */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\ListView $listview */
    $listview = $this->createMock(\Papaya\UI\ListView::class);
    $items = new Items($listview);
    $this->assertSame(
      $reference, $items->reference($reference)
    );
  }

  /**
   * @covers \Papaya\UI\ListView\Items::reference
   */
  public function testReferenceGetImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\ListView $listview */
    $listview = $this->createMock(\Papaya\UI\ListView::class);
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($this->createMock(\Papaya\UI\Reference::class)));
    $items = new Items($listview);
    $this->assertInstanceOf(
      \Papaya\UI\Reference::class, $items->reference()
    );
  }
}
