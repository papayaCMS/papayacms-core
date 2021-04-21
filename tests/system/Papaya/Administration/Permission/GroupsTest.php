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

namespace Papaya\Administration\Permission;

require_once __DIR__.'/../../../../bootstrap.php';

class GroupsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Administration\Permission\Groups::__construct
   */
  public function testConstructorPreparesIndex() {
    $permissions = new Groups();
    $this->assertNotEmpty(iterator_to_array($permissions));
  }

  /**
   * @covers \Papaya\Administration\Permission\Groups::getIterator
   */
  public function testGetIterator() {
    $permissions = new Groups();
    $array = iterator_to_array($permissions);
    $this->assertArrayHasKey(Groups::MISC, $array);
  }

  /**
   * @covers \Papaya\Administration\Permission\Groups::getIterator
   */
  public function testGetIteratorReturnsItems() {
    $permissions = new Groups();
    $array = iterator_to_array(new \RecursiveIteratorIterator($permissions));
    $this->assertArrayHasKey(Groups::MISC, $array);
    $this->assertArrayHasKey(
      \Papaya\Administration\Permissions::MESSAGES,
      $array[Groups::MISC]
    );
  }

  /**
   * @covers \Papaya\Administration\Permission\Groups::getGroupId
   */
  public function testGetGroupId() {
    $permissions = new Groups();
    $this->assertEquals(
      Groups::MISC,
      $permissions->getGroupId(\Papaya\Administration\Permissions::MESSAGES)
    );
  }

  /**
   * @covers \Papaya\Administration\Permission\Groups::getGroupId
   */
  public function testGetGroupIdExpectingZero() {
    $permissions = new Groups();
    $this->assertEquals(
      0,
      $permissions->getGroupId(-23)
    );
  }
}
