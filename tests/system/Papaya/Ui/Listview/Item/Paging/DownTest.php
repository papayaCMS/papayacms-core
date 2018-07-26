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

class PapayaUiListviewItemPagingDownTest extends PapayaTestCase {

  /**
   * @covers \PapayaUiListviewItemPagingDown::getPages
   * @dataProvider provideDataForPageCalculations
   * @param array $expected
   * @param int $currentPage
   * @param int $itemsPerPage
   */
  public function testGetPages(array $expected, $currentPage, $itemsPerPage) {
    $item = new \PapayaUiListviewItemPagingDown('page', $currentPage, $itemsPerPage);
    $this->assertEquals(
      $expected,
      $item->getPages()
    );
  }

  /**
  * @covers \PapayaUiListviewItemPagingDown::getImagePage
  */
  public function testGetImagePage() {
    $item = new \PapayaUiListviewItemPagingDown('page', 5, 500);
    $this->assertEquals(
      4,
      $item->getImagePage()
    );
  }

  /**
  * @covers \PapayaUiListviewItemPagingDown::getImagePage
  */
  public function testGetImagePageExpectingDefault() {
    $item = new \PapayaUiListviewItemPagingDown('page', 0, 500);
    $this->assertEquals(
      1,
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
        5,
        2
      ),
      array(
        array(),
        1,
        20
      ),
      array(
        array(1, 2),
        3,
        40
      ),
      array(
        array(7, 8, 9),
        10,
        100
      )
    );
  }
}
