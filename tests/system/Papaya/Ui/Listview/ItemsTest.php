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

class PapayaUiListviewItemsTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiListviewItems::__construct
  * @covers \PapayaUiListviewItems::owner
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListview $listview */
    $listview = $this->createMock(\PapayaUiListview::class);
    $items = new \PapayaUiListviewItems($listview);
    $this->assertSame(
      $listview, $items->owner()
    );
  }

  /**
  * @covers \PapayaUiListviewItems::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\PapayaUiReference::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListview $listview */
    $listview = $this->createMock(\PapayaUiListview::class);
    $items = new \PapayaUiListviewItems($listview);
    $this->assertSame(
      $reference, $items->reference($reference)
    );
  }

  /**
  * @covers \PapayaUiListviewItems::reference
  */
  public function testReferenceGetImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListview $listview */
    $listview = $this->createMock(\PapayaUiListview::class);
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($this->createMock(\PapayaUiReference::class)));
    $items = new \PapayaUiListviewItems($listview);
    $this->assertInstanceOf(
      \PapayaUiReference::class, $items->reference()
    );
  }
}
