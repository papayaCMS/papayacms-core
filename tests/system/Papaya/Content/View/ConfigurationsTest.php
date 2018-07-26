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

use Papaya\Content\Tables;
use Papaya\Content\View\Configurations;
use Papaya\Database\Result;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentViewConfigurationsTest extends \PapayaTestCase {

  /**
   * @covers Configurations
   */
  public function testLoad() {
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(Result::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'view_id' => '42',
            'viewmode_id' => '123',
            'viewlink_data' => 'DATA',
            'module_guid' => '123456789012345678901234567890ab',
            'module_type' => 'page'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          'table_'.Tables::VIEW_CONFIGURATIONS,
          'table_'.Tables::VIEW_MODES,
          'table_'.Tables::MODULES,
          'table_'.Tables::VIEW_DATAFILTER_CONFIGURATIONS,
          'table_'.Tables::VIEW_DATAFILTERS,
          'table_'.Tables::MODULES
        ),
        10,
        0
      )
      ->will($this->returnValue($databaseResult));
    $list = new Configurations();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $list->load(42, 10, 0)
    );
    $this->assertEquals(
      array(
        array(
          'id' => '42',
          'mode_id' => 123,
          'options' => 'DATA',
          'module_guid' => '123456789012345678901234567890ab',
          'type' => 'page'
        )
      ),
      iterator_to_array($list)
    );
  }
}
