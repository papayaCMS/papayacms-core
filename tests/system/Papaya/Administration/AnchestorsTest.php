<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaAdministrationPagesAnchestorsTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesAnchestors::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');

    $menu = $this->createMock(PapayaUiHierarchyMenu::class);
    $menu
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'))
      ->will($this->returnValue($dom->documentElement->appendElement('menu')));
    $ancestors = new PapayaAdministrationPagesAnchestors();
    $ancestors->menu($menu);

    $this->assertInstanceOf(PapayaXmlElement::class, $ancestors->appendTo($dom->documentElement));
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::setIds
  */
  public function testSetIds() {
    $pages = $this->createMock(PapayaContentPages::class);
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

    $ancestors = new PapayaAdministrationPagesAnchestors();
    $ancestors->papaya(
      $this->mockPapaya()->application(
        array(
          'AdministrationLanguage' => $this->getLanguageSwitchFixture()
        )
      )
    );
    $ancestors->pages($pages);

    $ancestors->setIds(array(42));
    $this->assertXmlStringEqualsXmlString(
      // language=xml
      '<hierarchy-menu>
        <items>
          <item caption="test" mode="both"
           href="http://www.test.tld/test.html?tt[page_id]=42"/>
        </items>
      </hierarchy-menu>',
      $ancestors->getXml()
    );
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::pages
  */
  public function testPagesGetAfterSet() {
    $ancestors = new PapayaAdministrationPagesAnchestors();
    $pages = $this->createMock(PapayaContentPages::class);
    $this->assertSame(
      $pages, $ancestors->pages($pages)
    );
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::pages
  */
  public function testPagesGetWithImpliciteCreate() {
    $ancestors = new PapayaAdministrationPagesAnchestors();
    $ancestors->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaContentPages::class, $ancestors->pages()
    );
    $this->assertSame(
      $papaya, $ancestors->papaya()
    );
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::menu
  */
  public function testItemsGetAfterSet() {
    $ancestors = new PapayaAdministrationPagesAnchestors();
    $menu = $this->createMock(PapayaUiHierarchyMenu::class);
    $this->assertSame(
      $menu, $ancestors->menu($menu)
    );
  }

  /**
  * @covers PapayaAdministrationPagesAnchestors::menu
  */
  public function testItemsGetWithImpliciteCreate() {
    $ancestors = new PapayaAdministrationPagesAnchestors();
    $ancestors->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaUiHierarchyMenu::class, $ancestors->menu()
    );
    $this->assertSame(
      $papaya, $ancestors->papaya()
    );
  }

  /************************************
  * Fixtures
  ************************************/

  private function getLanguageSwitchFixture() {
    $language = new stdClass();
    $language->id = 1;
    $switch = $this->createMock(PapayaAdministrationLanguagesSwitch::class);
    $switch
      ->expects($this->any())
      ->method('getCurrent')
      ->will($this->returnValue($language));
    return $switch;
  }
}
