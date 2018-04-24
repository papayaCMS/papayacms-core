<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaAdministrationPermissionsTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPermissions::__construct
   * @covers PapayaAdministrationPermissions::getIterator
   */
  public function testGetIterator() {
    $permissions = new PapayaAdministrationPermissions();
    $array = iterator_to_array($permissions);
    $this->assertArrayHasKey(PapayaAdministrationPermissions::MESSAGES, $array);
  }

  /**
   * @covers PapayaAdministrationPermissions::exists
   */
  public function testExistsExpectingTrue() {
    $permissions = new PapayaAdministrationPermissions();
    $this->assertTrue($permissions->exists(PapayaAdministrationPermissions::USER_MANAGE));
  }

  /**
   * @covers PapayaAdministrationPermissions::exists
   */
  public function testExistsExpectingFalse() {
    $permissions = new PapayaAdministrationPermissions();
    $this->assertFalse($permissions->exists(-23));
  }

  /**
   * @covers PapayaAdministrationPermissions::exists
   */
  public function testExistsInGroupExpectingTrue() {
    $permissions = new PapayaAdministrationPermissions();
    $this->assertTrue(
      $permissions->exists(
        PapayaAdministrationPermissions::USER_MANAGE,
        PapayaAdministrationPermissionGroups::SYSTEM
      )
    );
  }

  /**
   * @covers PapayaAdministrationPermissions::exists
   */
  public function testExistsInGroupExpectingFalse() {
    $permissions = new PapayaAdministrationPermissions();
    $this->assertFalse(
      $permissions->exists(
        PapayaAdministrationPermissions::USER_MANAGE,
        PapayaAdministrationPermissionGroups::MISC
      )
    );
  }

  /**
   * @covers PapayaAdministrationPermissions::inGroup
   */
  public function testInGroupExpectingTrue() {
    $permissions = new PapayaAdministrationPermissions();
    $this->assertTrue(
      $permissions->inGroup(
        PapayaAdministrationPermissions::USER_MANAGE,
        PapayaAdministrationPermissionGroups::SYSTEM
      )
    );
  }

  /**
   * @covers PapayaAdministrationPermissions::inGroup
   */
  public function testInGroupExpectingFalse() {
    $permissions = new PapayaAdministrationPermissions();
    $this->assertFalse(
      $permissions->inGroup(
        PapayaAdministrationPermissions::USER_MANAGE,
        PapayaAdministrationPermissionGroups::MISC
      )
    );
  }

  /**
   * @covers PapayaAdministrationPermissions::isActive
   */
  public function testIsActiveExpectingTrue() {
    $permissions = new PapayaAdministrationPermissions();
    $this->assertTrue($permissions->isActive(PapayaAdministrationPermissions::USER_MANAGE));
  }

  /**
   * @covers PapayaAdministrationPermissions::isActive
   */
  public function testIsActiveWithInvalidPermissionExpectingFalse() {
    $permissions = new PapayaAdministrationPermissions();
    $this->assertFalse($permissions->isActive(-23));
  }

  /**
   * @covers PapayaAdministrationPermissions::isActive
   * @covers PapayaAdministrationPermissions::reset
   */
  public function testIsActiveAfterLoadingExpectingFalse() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'perm_id' => PapayaAdministrationPermissions::USER_MANAGE,
            'perm_active' => '0'
          ),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(PapayaContentTables::AUTHENTICATION_PERMISSIONS)
      )
      ->will($this->returnValue($databaseResult));
    $permissions = new PapayaAdministrationPermissions();
    $permissions->setDatabaseAccess($databaseAccess);
    $permissions->load();
    $this->assertFalse(
      $permissions->isActive(PapayaAdministrationPermissions::USER_MANAGE)
    );
  }

  /**
   * @covers PapayaAdministrationPermissions::groups
   */
  public function testGroupsGetAfterSet() {
    $permissions = new PapayaAdministrationPermissions();
    $permissions->groups($groups = $this->getMock('PapayaAdministrationPermissionGroups'));
    $this->assertSame($groups, $permissions->groups());
  }

  /**
   * @covers PapayaAdministrationPermissions::groups
   */
  public function testGroupsGetImplicitCreate() {
    $permissions = new PapayaAdministrationPermissions();
    $this->assertInstanceOf('PapayaAdministrationPermissionGroups', $permissions->groups());
  }

}
