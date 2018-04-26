<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentStructureGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructureGroup::__construct
   */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentStructurePage $page */
    $page =$this->createMock(PapayaContentStructurePage::class);
    $group = new PapayaContentStructureGroup($page);
    $this->assertAttributeSame($page, '_page', $group);
  }

  /**
   * @covers PapayaContentStructureGroup::values
   */
  public function testGroupsGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentStructurePage $page */
    $page =$this->createMock(PapayaContentStructurePage::class);
    $group = new PapayaContentStructureGroup($page);
    $values = $this
      ->getMockBuilder(PapayaContentStructureValues::class)
      ->disableOriginalConstructor()
      ->getMock();
    $group->values($values);
    $this->assertSame($values, $group->values());
  }

  /**
   * @covers PapayaContentStructureGroup::values
   */
  public function testGroupsGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentStructurePage $page */
    $page =$this->createMock(PapayaContentStructurePage::class);
    $group = new PapayaContentStructureGroup($page);
    $this->assertInstanceOf(PapayaContentStructureValues::class, $group->values());
  }

  /**
   * @covers PapayaContentStructureGroup::getIdentifier
   */
  public function testGetIdentifier() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentStructurePage $page */
    $page =$this->createMock(PapayaContentStructurePage::class);
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
