<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentStructureGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructureGroup::__construct
   */
  public function testConstructor() {
    $group = new PapayaContentStructureGroup($page =$this->createMock(PapayaContentStructurePage::class));
    $this->assertAttributeSame($page, '_page', $group);
  }

  /**
   * @covers PapayaContentStructureGroup::values
   */
  public function testGroupsGetAfterSet() {
    $group = new PapayaContentStructureGroup($this->createMock(PapayaContentStructurePage::class));
    $values = $this
      ->getMockBuilder('PapayaContentStructureValues')
      ->disableOriginalConstructor()
      ->getMock();
    $group->values($values);
    $this->assertSame($values, $group->values());
  }

  /**
   * @covers PapayaContentStructureGroup::values
   */
  public function testGroupsGetImplicitCreate() {
    $group = new PapayaContentStructureGroup($this->createMock(PapayaContentStructurePage::class));
    $this->assertInstanceOf('PapayaContentStructureValues', $group->values());
  }

  /**
   * @covers PapayaContentStructureGroup::getIdentifier
   */
  public function testGetIdentifier() {
    $page = $this
      ->getMockBuilder('PapayaContentStructurePage')
      ->disableOriginalConstructor()
      ->getMock();
    $page
      ->expects($this->once())
      ->method('getIdentifier')
      ->will($this->returnValue('PAGE'));
    $group = new PapayaContentStructureGroup($page);
    $group->name = 'GROUP';
    $this->assertEquals(
      'PAGE/GROUP', $group->getIdentifier()
    );
  }
}
