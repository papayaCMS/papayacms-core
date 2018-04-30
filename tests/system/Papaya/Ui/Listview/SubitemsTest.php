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

class PapayaUiListviewSubitemsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitems::__construct
  * @covers PapayaUiListviewSubitems::owner
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $subitems = new PapayaUiListviewSubitems($item);
    $this->assertSame(
      $item, $subitems->owner()
    );
  }

  /**
  * @covers PapayaUiListviewSubitems::getListview
  */
  public function testGetListview() {
    $listview = $this->createMock(PapayaUiListview::class);
    $collection = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItem $item */
    $item = $this
      ->getMockBuilder(PapayaUiListviewItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item
      ->expects($this->once())
      ->method('collection')
      ->will($this->returnValue($collection));
    $subitems = new PapayaUiListviewSubitems($item);
    $this->assertSame(
      $listview, $subitems->getListview()
    );

  }
}
