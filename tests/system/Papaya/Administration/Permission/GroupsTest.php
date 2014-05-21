<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaAdministrationPermissionGroupsTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPermissionGroups::__construct
   */
  public function testConstructorPreparesIndex() {
    $permissions = new PapayaAdministrationPermissionGroups();
    $this->assertAttributeNotEmpty('_index', $permissions);
  }

  /**
   * @covers PapayaAdministrationPermissionGroups::getIterator
   */
  public function testGetIterator() {
    $permissions = new PapayaAdministrationPermissionGroups();
    $array = iterator_to_array($permissions);
    $this->assertArrayHasKey(PapayaAdministrationPermissionGroups::MISC, $array);
  }

  /**
   * @covers PapayaAdministrationPermissionGroups::getIterator
   */
  public function testGetIteratorReturnsItems() {
    $permissions = new PapayaAdministrationPermissionGroups();
    $array = iterator_to_array(new RecursiveIteratorIterator($permissions));
    $this->assertArrayHasKey(PapayaAdministrationPermissionGroups::MISC, $array);
    $this->assertArrayHasKey(
      PapayaAdministrationPermissions::MESSAGES,
      $array[PapayaAdministrationPermissionGroups::MISC]
    );
  }

  /**
   * @covers PapayaAdministrationPermissionGroups::getGroupId
   */
  public function testGetGroupId() {
    $permissions = new PapayaAdministrationPermissionGroups();
    $this->assertEquals(
      PapayaAdministrationPermissionGroups::MISC,
      $permissions->getGroupId(PapayaAdministrationPermissions::MESSAGES)
    );
  }

  /**
   * @covers PapayaAdministrationPermissionGroups::getGroupId
   */
  public function testGetGroupIdExpectingZero() {
    $permissions = new PapayaAdministrationPermissionGroups();
    $this->assertEquals(
      0,
      $permissions->getGroupId(-23)
    );
  }
}