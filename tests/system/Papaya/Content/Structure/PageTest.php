<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentStructurePageTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructurePage::__construct
   */
  public function testConstructor() {
    $page = new PapayaContentStructurePage();
    $this->assertEquals('page', $page->name);
  }

  /**
   * @covers PapayaContentStructurePage::groups
   */
  public function testGroupsGetAfterSet() {
    $groups = $this
      ->getMockBuilder('PapayaContentStructureGroups')
      ->disableOriginalConstructor()
      ->getMock();
    $page = new PapayaContentStructurePage();
    $page->groups($groups);
    $this->assertSame($groups, $page->groups());
  }

  /**
   * @covers PapayaContentStructurePage::groups
   */
  public function testGroupsGetImplicitCreate() {
    $page = new PapayaContentStructurePage();
    $this->assertInstanceOf('PapayaContentStructureGroups', $page->groups());
  }

  /**
   * @covers PapayaContentStructurePage::getIdentifier
   */
  public function testGetIdentifier() {
    $page = new PapayaContentStructurePage();
    $page->name = 'PAGE';
    $this->assertEquals(
      'PAGE', $page->getIdentifier()
    );
  }
}
