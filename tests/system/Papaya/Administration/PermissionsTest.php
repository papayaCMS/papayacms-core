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

use Papaya\Administration\Permissions;
use Papaya\Administration\Permission\Groups;
use Papaya\Content\Tables;
use Papaya\Database\Result;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaAdministrationPermissionsTest extends PapayaTestCase {

  /**
   * @covers Permissions::__construct
   * @covers Permissions::getIterator
   */
  public function testGetIterator() {
    $permissions = new Permissions();
    $array = iterator_to_array($permissions);
    $this->assertArrayHasKey(Permissions::MESSAGES, $array);
  }

  /**
   * @covers Permissions::exists
   */
  public function testExistsExpectingTrue() {
    $permissions = new Permissions();
    $this->assertTrue($permissions->exists(Permissions::USER_MANAGE));
  }

  /**
   * @covers Permissions::exists
   */
  public function testExistsExpectingFalse() {
    $permissions = new Permissions();
    $this->assertFalse($permissions->exists(-23));
  }

  /**
   * @covers Permissions::exists
   */
  public function testExistsInGroupExpectingTrue() {
    $permissions = new Permissions();
    $this->assertTrue(
      $permissions->exists(
        Permissions::USER_MANAGE,
        Groups::SYSTEM
      )
    );
  }

  /**
   * @covers Permissions::exists
   */
  public function testExistsInGroupExpectingFalse() {
    $permissions = new Permissions();
    $this->assertFalse(
      $permissions->exists(
        Permissions::USER_MANAGE,
        Groups::MISC
      )
    );
  }

  /**
   * @covers Permissions::inGroup
   */
  public function testInGroupExpectingTrue() {
    $permissions = new Permissions();
    $this->assertTrue(
      $permissions->inGroup(
        Permissions::USER_MANAGE,
        Groups::SYSTEM
      )
    );
  }

  /**
   * @covers Permissions::inGroup
   */
  public function testInGroupExpectingFalse() {
    $permissions = new Permissions();
    $this->assertFalse(
      $permissions->inGroup(
        Permissions::USER_MANAGE,
        Groups::MISC
      )
    );
  }

  /**
   * @covers Permissions::isActive
   */
  public function testIsActiveExpectingTrue() {
    $permissions = new Permissions();
    $this->assertTrue($permissions->isActive(Permissions::USER_MANAGE));
  }

  /**
   * @covers Permissions::isActive
   */
  public function testIsActiveWithInvalidPermissionExpectingFalse() {
    $permissions = new Permissions();
    $this->assertFalse($permissions->isActive(-23));
  }

  /**
   * @covers Permissions::isActive
   * @covers Permissions::reset
   */
  public function testIsActiveAfterLoadingExpectingFalse() {
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'perm_id' => Permissions::USER_MANAGE,
            'perm_active' => '0'
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
        array('table_'.Tables::AUTHENTICATION_PERMISSIONS)
      )
      ->will($this->returnValue($databaseResult));
    $permissions = new Permissions();
    $permissions->setDatabaseAccess($databaseAccess);
    $permissions->load();
    $this->assertFalse(
      $permissions->isActive(Permissions::USER_MANAGE)
    );
  }

  /**
   * @covers Permissions::groups
   */
  public function testGroupsGetAfterSet() {
    $permissions = new Permissions();
    $permissions->groups($groups = $this->createMock(Groups::class));
    $this->assertSame($groups, $permissions->groups());
  }

  /**
   * @covers Permissions::groups
   */
  public function testGroupsGetImplicitCreate() {
    $permissions = new Permissions();
    $this->assertInstanceOf(Groups::class, $permissions->groups());
  }

}
