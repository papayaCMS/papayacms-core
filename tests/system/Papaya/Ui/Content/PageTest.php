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

namespace Papaya\UI\Content;

require_once __DIR__.'/../../../../bootstrap.php';

class PageTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testConstructor() {
    $page = new Page(
      42, $language = $this->createMock(\Papaya\Content\Language::class)
    );
    $this->assertEquals(42, $page->getPageId());
    $this->assertSame($language, $page->getPageLanguage());
    $this->assertTrue($page->isPublic());
  }

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testConstructorWithAllArguments() {
    $page = new Page(
      42, $language = $this->createMock(\Papaya\Content\Language::class), FALSE
    );
    $this->assertEquals(42, $page->getPageId());
    $this->assertSame($language, $page->getPageLanguage());
    $this->assertFalse($page->isPublic());
  }

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testAssign() {
    $contentPage = $this->createMock(\Papaya\Content\Page::class);
    $contentPage
      ->expects($this->once())
      ->method('assign')
      ->with(array('page_id' => 42));
    $contentTranslation = $this->createMock(\Papaya\Content\Page\Translation::class);
    $contentTranslation
      ->expects($this->once())
      ->method('assign')
      ->with(array('page_id' => 42));
    $page = new Page(
      42, $this->createMock(\Papaya\Content\Language::class)
    );
    $page->page($contentPage);
    $page->translation($contentTranslation);
    $page->assign(array('page_id' => 42));
  }

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testPageGetAfterSet() {
    $contentPage = $this->createMock(\Papaya\Content\Page::class);
    $page = new Page(
      42, $this->createMock(\Papaya\Content\Language::class)
    );
    $page->page($contentPage);
    $this->assertSame($contentPage, $page->page());
  }

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testPageImpliciteCreatePublicPageContent() {
    $page = new Page(
      42, $this->createMock(\Papaya\Content\Language::class)
    );
    $pageContent = $page->page();
    $this->assertInstanceOf(\Papaya\Content\Page\Publication::class, $pageContent);
    $this->assertEquals(array(42), $pageContent->getLazyLoadParameters());
  }

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testPageImpliciteCreatePreviewPageContent() {
    $page = new Page(
      42, $this->createMock(\Papaya\Content\Language::class), FALSE
    );
    $pageContent = $page->page();
    $this->assertInstanceOf(\Papaya\Content\Page::class, $pageContent);
    $this->assertNotInstanceOf(\Papaya\Content\Page\Publication::class, $pageContent);
    $this->assertEquals(array(42), $pageContent->getLazyLoadParameters());
  }

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testTranslationGetAfterSet() {
    $contentTranslation = $this->createMock(\Papaya\Content\Page\Translation::class);
    $page = new Page(
      42, $this->createMock(\Papaya\Content\Language::class)
    );
    $page->translation($contentTranslation);
    $this->assertSame($contentTranslation, $page->translation());
  }

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testPageImplicCreatePublicTranslationContent() {
    $language = $this->createMock(\Papaya\Content\Language::class);
    $language
      ->expects($this->once())
      ->method('offsetGet')
      ->with('id')
      ->will($this->returnValue(21));
    $page = new Page(
      42, $language
    );
    $contentTranslation = $page->translation();
    $this->assertInstanceOf(\Papaya\Content\Page\Publication\Translation::class, $contentTranslation);
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
   * @covers \Papaya\UI\Content\Page
   */
  public function testPageImplicCreatePreviewTranslationContent() {
    $language = $this->createMock(\Papaya\Content\Language::class);
    $language
      ->expects($this->once())
      ->method('offsetGet')
      ->with('id')
      ->will($this->returnValue(21));
    $page = new Page(
      42, $language, FALSE
    );
    $contentTranslation = $page->translation();
    $this->assertInstanceOf(\Papaya\Content\Page\Translation::class, $contentTranslation);
    $this->assertNotInstanceOf(\Papaya\Content\Page\Publication\Translation::class, $contentTranslation);
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
   * @covers \Papaya\UI\Content\Page
   */
  public function testGetPageViewId() {
    $contentTranslation = $this->createMock(\Papaya\Content\Page\Translation::class);
    $contentTranslation
      ->expects($this->once())
      ->method('offsetGet')
      ->with('view_id')
      ->will($this->returnValue(23));
    $page = new Page(
      42, $this->createMock(\Papaya\Content\Language::class)
    );
    $page->translation($contentTranslation);
    $this->assertEquals(23, $page->getPageViewId());
  }

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testGetPageLanguageFromApplication() {
    $languages = $this->createMock(\Papaya\Content\Languages::class);
    $languages
      ->expects($this->once())
      ->method('getLanguage')
      ->with('de')
      ->will($this->returnValue($this->createMock(\Papaya\Content\Language::class)));
    $page = new Page(42, 'de');
    $page->papaya($this->mockPapaya()->application(array('languages' => $languages)));
    $this->assertInstanceOf(\Papaya\Content\Language::class, $page->getPageLanguage());
    $this->assertSame($page->getPageLanguage(), $page->getPageLanguage());
  }

  /**
   * @covers \Papaya\UI\Content\Page
   */
  public function testGetPageLanguageFromApplicationFailed() {
    $languages = $this->createMock(\Papaya\Content\Languages::class);
    $languages
      ->expects($this->once())
      ->method('getLanguage')
      ->with('de')
      ->will($this->returnValue(NULL));
    $page = new Page(42, 'de');
    $page->papaya($this->mockPapaya()->application(array('languages' => $languages)));
    $this->assertNull($page->getPageLanguage());
    $this->assertNull($page->getPageLanguage());
  }
}
