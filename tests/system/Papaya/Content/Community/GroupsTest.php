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

namespace Papaya\Content\Community;

require_once __DIR__.'/../../../../bootstrap.php';

class GroupsTest extends \Papaya\TestCase {

  /**
   * @covers Groups::loadByPermission
   */
  public function testLoadByPermission() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->with(\Papaya\Database\Result::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array(
            'surfergroup_id' => 42,
            'surfergroup_title' => 'surfer group'
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
          'table_'.\Papaya\Content\Tables::COMMUNITY_GROUPS,
          'table_'.\Papaya\Content\Tables::COMMUNITY_GROUP_PERMISSIONS,
          23
        )
      )
      ->will($this->returnValue($databaseResult));

    $groups = new Groups();
    $groups->setDatabaseAccess($databaseAccess);
    $this->assertTrue($groups->loadByPermission(23));
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'title' => 'surfer group'
        )
      ),
      iterator_to_array($groups)
    );
  }
}
