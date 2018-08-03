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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiListviewItemPagingUpTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Ui\Listview\Item\Paging\Up::getPages
   * @dataProvider provideDataForPageCalculations
   * @param array $expected
   * @param int $currentPage
   * @param int $itemsPerPage
   */
  public function testGetPages($expected, $currentPage, $itemsPerPage) {
    $item = new \Papaya\Ui\Listview\Item\Paging\Up('page', $currentPage, $itemsPerPage);
    $this->assertEquals(
      $expected,
      $item->getPages()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item\Paging\Up::getImagePage
  */
  public function testGetImagePage() {
    $item = new \Papaya\Ui\Listview\Item\Paging\Up('page', 2, 40);
    $this->assertEquals(
      3,
      $item->getImagePage()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item\Paging\Up::getImagePage
  */
  public function testGetImagePageExpectingDefault() {
    $item = new \Papaya\Ui\Listview\Item\Paging\Up('page', 8, 50);
    $this->assertEquals(
      5,
      $item->getImagePage()
    );
  }

  /*************************
  * Data Provider
  *************************/

  public static function provideDataForPageCalculations() {
    return array(
      array(
        array(),
        10,
        100
      ),
      array(
        array(),
        5,
        2
      ),
      array(
        array(3, 4),
        2,
        40
      )
    );
  }
}
