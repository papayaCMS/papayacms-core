<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaAdministrationPagesAnchestorsTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesAnchestors::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');

    $menu = $this->getMock('PapayaUiHierarchyMenu');
    $menu
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'))
      ->will($this->returnValue($dom->documentElement->appendElement('menu')));
    $anchestors = new PapayaAdministrationPagesAnchestors();
    $anchestors->menu($menu);

    $this->assertInstanceOf('PapayaXmlElement', $anchestors->appendTo($dom->documentElement));
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::setIds
  */
  public function testSetIds() {
    $pages = $this->getMock('PapayaContentPages');
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => array(42), 'language_id' => 1));
    $pages
      ->expects($this->any())
      ->method('offsetExists')
      ->will(
        $this->onConsecutiveCalls(TRUE, FALSE)
      );
    $pages
      ->expects($this->once())
      ->method('offsetGet')
      ->with(42)
      ->will(
        $this->returnValue(
          array('title' => 'test')
        )
      );

    $anchestors = new PapayaAdministrationPagesAnchestors();
    $anchestors->papaya(
      $this->mockPapaya()->application(
        array(
          'AdministrationLanguage' => $this->getLanguageSwitchFixture()
        )
      )
    );
    $anchestors->pages($pages);

    $anchestors->setIds(array(42));
    $this->assertEquals(
      '<hierarchy-menu>'.
        '<items>'.
          '<item caption="test" mode="both"'.
          ' href="http://www.test.tld/test.html?tt[page_id]=42"/>'.
        '</items>'.
      '</hierarchy-menu>',
      $anchestors->getXml()
    );
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::pages
  */
  public function testPagesGetAfterSet() {
    $anchestors = new PapayaAdministrationPagesAnchestors();
    $pages = $this->getMock('PapayaContentPages');
    $this->assertSame(
      $pages, $anchestors->pages($pages)
    );
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::pages
  */
  public function testPagesGetWithImpliciteCreate() {
    $anchestors = new PapayaAdministrationPagesAnchestors();
    $anchestors->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      'PapayaContentPages', $anchestors->pages()
    );
    $this->assertSame(
      $papaya, $anchestors->papaya()
    );
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::menu
  */
  public function testItemsGetAfterSet() {
    $anchestors = new PapayaAdministrationPagesAnchestors();
    $menu = $this->getMock('PapayaUiHierarchyMenu');
    $this->assertSame(
      $menu, $anchestors->menu($menu)
    );
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::menu
  */
  public function testItemsGetWithImpliciteCreate() {
    $anchestors = new PapayaAdministrationPagesAnchestors();
    $anchestors->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      'PapayaUiHierarchyMenu', $anchestors->menu()
    );
    $this->assertSame(
      $papaya, $anchestors->papaya()
    );
  }

  /************************************
  * Fixtures
  ************************************/

  private function getLanguageSwitchFixture() {
    $language = new stdClass();
    $language->id = 1;
    $switch = $this->getMock('PapayaAdministrationLanguagesSwitch');
    $switch
      ->expects($this->any())
      ->method('getCurrent')
      ->will($this->returnValue($language));
    return $switch;
  }
}
