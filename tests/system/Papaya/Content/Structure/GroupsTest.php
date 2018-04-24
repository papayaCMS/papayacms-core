<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentStructureGroupsTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructureGroups::__construct
   */
  public function testConstructor() {
    $page = $this
      ->getMockBuilder(PapayaContentStructurePage::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaContentStructureGroups($page);
    $this->assertEquals(PapayaContentStructureGroup::class, $groups->getItemClass());
  }

  /**
   * @covers PapayaContentStructureGroups::load
   */
  public function testLoad() {
    $page = $this
      ->getMockBuilder(PapayaContentStructurePage::class)
      ->disableOriginalConstructor()
      ->getMock();
    $dom = new PapayaXmlDocument();
    $dom->load(__DIR__.'/../TestData/structure.xml');
    $groups = new PapayaContentStructureGroups($page);
    $groups->load($dom->xpath()->evaluate('//page[1]')->item(0));
    $this->assertCount(3, $groups);
    $this->assertEquals('Sample Group 1.1', $groups[0]->title);
    $this->assertEquals('FONT', $groups[0]->name);
    $this->assertCount(1, $groups[0]->values());
  }
}
