<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiContentPageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiContentPage
   */
  public function testConstructor() {
    $page = new PapayaUiContentPage(
      42, $language = $this->createMock(PapayaContentLanguage::class)
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
      42, $language = $this->createMock(PapayaContentLanguage::class), FALSE
    );
    $this->assertEquals(42, $page->getPageId());
    $this->assertSame($language, $page->getPageLanguage());
    $this->assertFalse($page->isPublic());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testAssign() {
    $contentPage = $this->createMock(PapayaContentPage::class);
    $contentPage
      ->expects($this->once())
      ->method('assign')
      ->with(array('page_id' => 42));
    $contentTranslation = $this->createMock(PapayaContentPageTranslation::class);
    $contentTranslation
      ->expects($this->once())
      ->method('assign')
      ->with(array('page_id' => 42));
    $page = new PapayaUiContentPage(
      42, $this->createMock(PapayaContentLanguage::class)
    );
    $page->page($contentPage);
    $page->translation($contentTranslation);
    $page->assign(array('page_id' => 42));
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testPageGetAfterSet() {
    $contentPage = $this->createMock(PapayaContentPage::class);
    $page = new PapayaUiContentPage(
      42, $this->createMock(PapayaContentLanguage::class)
    );
    $page->page($contentPage);
    $this->assertSame($contentPage, $page->page());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testPageImpliciteCreatePublicPageContent() {
    $page = new PapayaUiContentPage(
      42, $this->createMock(PapayaContentLanguage::class)
    );
    $pageContent = $page->page();
    $this->assertInstanceOf(PapayaContentPagePublication::class, $pageContent);
    $this->assertEquals(array(42), $pageContent->getLazyLoadParameters());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testPageImpliciteCreatePreviewPageContent() {
    $page = new PapayaUiContentPage(
      42, $this->createMock(PapayaContentLanguage::class), FALSE
    );
    $pageContent = $page->page();
    $this->assertInstanceOf(PapayaContentPage::class, $pageContent);
    $this->assertNotInstanceOf(PapayaContentPagePublication::class, $pageContent);
    $this->assertEquals(array(42), $pageContent->getLazyLoadParameters());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testTranslationGetAfterSet() {
    $contentTranslation = $this->createMock(PapayaContentPageTranslation::class);
    $page = new PapayaUiContentPage(
      42, $this->createMock(PapayaContentLanguage::class)
    );
    $page->translation($contentTranslation);
    $this->assertSame($contentTranslation, $page->translation());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testPageImplicCreatePublicTranslationContent() {
    $language = $this->createMock(PapayaContentLanguage::class);
    $language
      ->expects($this->once())
      ->method('offsetGet')
      ->with('id')
      ->will($this->returnValue(21));
    $page = new PapayaUiContentPage(
      42, $language
    );
    $contentTranslation = $page->translation();
    $this->assertInstanceOf(PapayaContentPagePublicationTranslation::class, $contentTranslation);
    $this->assertEquals(
      array(
        array(
          'id' => 42,
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
    $language = $this->createMock(PapayaContentLanguage::class);
    $language
      ->expects($this->once())
      ->method('offsetGet')
      ->with('id')
      ->will($this->returnValue(21));
    $page = new PapayaUiContentPage(
      42, $language, FALSE
    );
    $contentTranslation = $page->translation();
    $this->assertInstanceOf(PapayaContentPageTranslation::class, $contentTranslation);
    $this->assertNotInstanceOf(PapayaContentPagePublicationTranslation::class, $contentTranslation);
    $this->assertEquals(
      array(
        array(
          'id' => 42,
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
    $contentTranslation = $this->createMock(PapayaContentPageTranslation::class);
    $contentTranslation
      ->expects($this->once())
      ->method('offsetGet')
      ->with('view_id')
      ->will($this->returnValue(23));
    $page = new PapayaUiContentPage(
      42, $this->createMock(PapayaContentLanguage::class)
    );
    $page->translation($contentTranslation);
    $this->assertEquals(23, $page->getPageViewId());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testGetPageLanguageFromApplication() {
    $languages = $this->createMock(PapayaContentLanguages::class);
    $languages
      ->expects($this->once())
      ->method('getLanguage')
      ->with('de')
      ->will($this->returnValue($this->createMock(PapayaContentLanguage::class)));
    $page = new PapayaUiContentPage(42, 'de');
    $page->papaya($this->mockPapaya()->application(array('languages' => $languages)));
    $this->assertInstanceOf(PapayaContentLanguage::class, $page->getPageLanguage());
    $this->assertSame($page->getPageLanguage(), $page->getPageLanguage());
  }

  /**
   * @covers PapayaUiContentPage
   */
  public function testGetPageLanguageFromApplicationFailed() {
    $languages = $this->createMock(PapayaContentLanguages::class);
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
