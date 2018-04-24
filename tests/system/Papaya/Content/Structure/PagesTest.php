<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentStructurePagesTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructurePages::__construct
   */
  public function testConstructor() {
    $pages = new PapayaContentStructurePages();
    $this->assertEquals(PapayaContentStructurePage::class, $pages->getItemClass());
  }

  /**
   * @covers PapayaContentStructurePages::load
   */
  public function testLoad() {
    $dom = new PapayaXmlDocument();
    $dom->load(__DIR__.'/../TestData/structure.xml');
    $pages = new PapayaContentStructurePages();
    $pages->load($dom->documentElement);
    $this->assertCount(1, $pages);
    $this->assertEquals('Sample Page 1', $pages[0]->title);
    $this->assertEquals('MAIN', $pages[0]->name);
    $this->assertCount(3, $pages[0]->groups());
  }
}
