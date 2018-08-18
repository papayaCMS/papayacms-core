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

namespace Papaya\Content;

require_once __DIR__.'/../../../bootstrap.php';

class ViewsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Content\Views::load
   */
  public function testLoad() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'view_id' => 21,
            'view_title' => 'Sample Title',
            'module_guid' => 'ab123456789012345678901234567890',
            'view_checksum' => 'ab123456789012345678901234567890:ab123456789012345678901234567890'
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
        array('table_'.Tables::VIEWS, 'table_'.Tables::MODULES)
      )
      ->will($this->returnValue($databaseResult));
    $pages = new Views();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load());
    $this->assertEquals(
      array(
        21 => array(
          'id' => 21,
          'title' => 'Sample Title',
          'module_id' => 'ab123456789012345678901234567890',
          'checksum' => 'ab123456789012345678901234567890:ab123456789012345678901234567890'
        )
      ),
      $pages->toArray()
    );
  }
}
