<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiContentPageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiContentPage
   */
  public function testConstructor() {
    $page = new PapayaUiContentPage(
      42, $language = $this->getMock('PapayaContentLanguage')
    );
    $this->assertEquals(42, $page->getPageId());
    $this->assertSame($language, $page->getPageLanguage());
    $this->assertTrue($page->isPublic());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testConstructorWithAllArguments() {
    $page = new PapayaUiContentPage(
      42, $language = $this->getMock('PapayaContentLanguage'), FALSE
    );
    $this->assertEquals(42, $page->getPageId());
    $this->assertSame($language, $page->getPageLanguage());
    $this->assertFalse($page->isPublic());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testAssign() {
    $contentPage = $this->getMock('PapayaContentPage');
    $contentPage
      ->expects($this->once())
      ->method('assign')
      ->with(array('page_id' => 42));
    $contentTranslation = $this->getMock('PapayaContentPageTranslation');
    $contentTranslation
      ->expects($this->once())
      ->method('assign')
      ->with(array('page_id' => 42));
    $page = new PapayaUiContentPage(
      42, $this->getMock('PapayaContentLanguage')
    );
    $page->page($contentPage);
    $page->translation($contentTranslation);
    $page->assign(array('page_id' => 42));
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testPageGetAfterSet() {
    $contentPage = $this->getMock('PapayaContentPage');
    $page = new PapayaUiContentPage(
      42, $this->getMock('PapayaContentLanguage')
    );
    $page->page($contentPage);
    $this->assertSame($contentPage, $page->page());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testPageImpliciteCreatePublicPageContent() {
    $page = new PapayaUiContentPage(
      42, $this->getMock('PapayaContentLanguage')
    );
    $pageContent = $page->page();
    $this->assertInstanceOf('PapayaContentPagePublication', $pageContent);
    $this->assertEquals(array(42), $pageContent->getLazyLoadParameters());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testPageImpliciteCreatePreviewPageContent() {
    $page = new PapayaUiContentPage(
      42, $this->getMock('PapayaContentLanguage'), FALSE
    );
    $pageContent = $page->page();
    $this->assertInstanceOf('PapayaContentPage', $pageContent);
    $this->assertNotInstanceOf('PapayaContentPagePublication', $pageContent);
    $this->assertEquals(array(42), $pageContent->getLazyLoadParameters());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testTranslationGetAfterSet() {
    $contentTranslation = $this->getMock('PapayaContentPageTranslation');
    $page = new PapayaUiContentPage(
      42, $this->getMock('PapayaContentLanguage')
    );
    $page->translation($contentTranslation);
    $this->assertSame($contentTranslation, $page->translation());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testPageImplicCreatePublicTranslationContent() {
    $language = $this->getMock('PapayaContentLanguage');
    $language
      ->expects($this->once())
      ->method('offsetGet')
      ->with('id')
      ->will($this->returnValue(21));
    $page = new PapayaUiContentPage(
      42, $language
    );
    $contentTranslation = $page->translation();
    $this->assertInstanceOf('PapayaContentPagePublicationTranslation', $contentTranslation);
    $this->assertEquals(
      array(
        array(
          'page_id' => 42,
          'language_id' => 21
        )
      ),
      $contentTranslation->getLazyLoadParameters()
    );
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testPageImplicCreatePreviewTranslationContent() {
    $language = $this->getMock('PapayaContentLanguage');
    $language
      ->expects($this->once())
      ->method('offsetGet')
      ->with('id')
      ->will($this->returnValue(21));
    $page = new PapayaUiContentPage(
      42, $language, FALSE
    );
    $contentTranslation = $page->translation();
    $this->assertInstanceOf('PapayaContentPageTranslation', $contentTranslation);
    $this->assertNotInstanceOf('PapayaContentPagePublicationTranslation', $contentTranslation);
    $this->assertEquals(
      array(
        array(
          'page_id' => 42,
          'language_id' => 21
        )
      ),
      $contentTranslation->getLazyLoadParameters()
    );
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testGetPageViewId() {
    $contentTranslation = $this->getMock('PapayaContentPageTranslation');
    $contentTranslation
      ->expects($this->once())
      ->method('offsetGet')
      ->with('view_id')
      ->will($this->returnValue(23));
    $page = new PapayaUiContentPage(
      42, $this->getMock('PapayaContentLanguage')
    );
    $page->translation($contentTranslation);
    $this->assertEquals(23, $page->getPageViewId());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testGetPageLanguageFromApplication() {
    $languages = $this->getMock('PapayaContentLanguages');
    $languages
      ->expects($this->once())
      ->method('getLanguage')
      ->with('de')
      ->will($this->returnValue($this->getMock('PapayaContentLanguage')));
    $page = new PapayaUiContentPage(42, 'de');
    $page->papaya($this->mockPapaya()->application(array('languages' => $languages)));
    $this->assertInstanceOf('PapayaContentLanguage', $page->getPageLanguage());
    $this->assertSame($page->getPageLanguage(), $page->getPageLanguage());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testGetPageLanguageFromApplicationFailed() {
    $languages = $this->getMock('PapayaContentLanguages');
    $languages
      ->expects($this->once())
      ->method('getLanguage')
      ->with('de')
      ->will($this->returnValue(NULL));
    $page = new PapayaUiContentPage(42, 'de');
    $page->papaya($this->mockPapaya()->application(array('languages' => $languages)));
    $this->assertNull($page->getPageLanguage());
    $this->assertNull($page->getPageLanguage());
  }
}
