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

namespace Papaya\CMS\Output {

  use Papaya\CMS\Content\Page\Translation as PageTranslationContent;
  use Papaya\Plugin\Appendable as AppendablePlugin;
  use Papaya\Plugin\Configurable\Context as ContextAwarePlugin;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\CMS\Plugin\Loader as PluginLoader;
  use Papaya\CMS\Plugin\PageModule;
  use Papaya\Plugin\Quoteable as QuotablePlugin;
  use Papaya\Request\Parameters;
  use Papaya\TestFramework\TestCase;
  use Papaya\CMS\Reference;
  use Papaya\XML\Document;
  use Papaya\XML\Element as XMLElement;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\CMS\Output\Page
   */
  class PageTest extends TestCase {

    public function testConstructor() {
      $page = new Page(
        42, $language = $this->createMock(\Papaya\CMS\Content\Language::class)
      );
      $this->assertEquals(42, $page->getPageId());
      $this->assertSame($language, $page->getPageLanguage());
      $this->assertTrue($page->isPublic());
    }

    public function testConstructorWithAllArguments() {
      $page = new Page(
        42, $language = $this->createMock(\Papaya\CMS\Content\Language::class), FALSE
      );
      $this->assertEquals(42, $page->getPageId());
      $this->assertSame($language, $page->getPageLanguage());
      $this->assertFalse($page->isPublic());
    }

    public function testAssign() {
      $contentPage = $this->createMock(\Papaya\CMS\Content\Page::class);
      $contentPage
        ->expects($this->once())
        ->method('assign')
        ->with(['page_id' => 42]);
      $contentTranslation = $this->createMock(\Papaya\CMS\Content\Page\Translation::class);
      $contentTranslation
        ->expects($this->once())
        ->method('assign')
        ->with(['page_id' => 42]);
      $page = new Page(
        42, $this->createMock(\Papaya\CMS\Content\Language::class)
      );
      $page->page($contentPage);
      $page->translation($contentTranslation);
      $page->assign(['page_id' => 42]);
    }

    /**
     * @covers \Papaya\CMS\Output\Page
     */
    public function testPageGetAfterSet() {
      $contentPage = $this->createMock(\Papaya\CMS\Content\Page::class);
      $page = new Page(
        42, $this->createMock(\Papaya\CMS\Content\Language::class)
      );
      $page->page($contentPage);
      $this->assertSame($contentPage, $page->page());
    }

    public function testPageImplicitCreatePublicPageContent() {
      $page = new Page(
        42, $this->createMock(\Papaya\CMS\Content\Language::class)
      );
      $pageContent = $page->page();
      $this->assertInstanceOf(\Papaya\CMS\Content\Page\Publication::class, $pageContent);
      $this->assertEquals([42], $pageContent->getLazyLoadParameters());
    }

    public function testPageImplicitCreatePreviewPageContent() {
      $page = new Page(
        42, $this->createMock(\Papaya\CMS\Content\Language::class), FALSE
      );
      $pageContent = $page->page();
      $this->assertInstanceOf(\Papaya\CMS\Content\Page::class, $pageContent);
      $this->assertNotInstanceOf(\Papaya\CMS\Content\Page\Publication::class, $pageContent);
      $this->assertEquals([42], $pageContent->getLazyLoadParameters());
    }

    public function testTranslationGetAfterSet() {
      $contentTranslation = $this->createMock(\Papaya\CMS\Content\Page\Translation::class);
      $page = new Page(
        42, $this->createMock(\Papaya\CMS\Content\Language::class)
      );
      $page->translation($contentTranslation);
      $this->assertSame($contentTranslation, $page->translation());
    }

    public function testPageImplicitCreatePublicTranslationContent() {
      $language = $this->createMock(\Papaya\CMS\Content\Language::class);
      $language
        ->expects($this->once())
        ->method('offsetGet')
        ->with('id')
        ->willReturn(21);
      $page = new Page(
        42, $language
      );
      $contentTranslation = $page->translation();
      $this->assertInstanceOf(\Papaya\CMS\Content\Page\Publication\Translation::class, $contentTranslation);
      $this->assertEquals(
        [
          [
            'id' => 42,
            'language_id' => 21
          ]
        ],
        $contentTranslation->getLazyLoadParameters()
      );
    }

    public function testPageImplicitCreatePreviewTranslationContent() {
      $language = $this->createMock(\Papaya\CMS\Content\Language::class);
      $language
        ->expects($this->once())
        ->method('offsetGet')
        ->with('id')
        ->willReturn(21);
      $page = new Page(
        42, $language, FALSE
      );
      $contentTranslation = $page->translation();
      $this->assertInstanceOf(\Papaya\CMS\Content\Page\Translation::class, $contentTranslation);
      $this->assertNotInstanceOf(\Papaya\CMS\Content\Page\Publication\Translation::class, $contentTranslation);
      $this->assertEquals(
        [
          [
            'id' => 42,
            'language_id' => 21
          ]
        ],
        $contentTranslation->getLazyLoadParameters()
      );
    }

    /**
     * @covers \Papaya\CMS\Output\Page
     */
    public function testGetPageViewId() {
      $contentTranslation = $this->createMock(\Papaya\CMS\Content\Page\Translation::class);
      $contentTranslation
        ->expects($this->once())
        ->method('offsetGet')
        ->with('view_id')
        ->willReturn(23);
      $page = new Page(
        42, $this->createMock(\Papaya\CMS\Content\Language::class)
      );
      $page->translation($contentTranslation);
      $this->assertEquals(23, $page->getPageViewId());
    }

    public function testGetPageLanguageFromApplication() {
      $languages = $this->createMock(\Papaya\CMS\Content\Languages::class);
      $languages
        ->expects($this->once())
        ->method('getLanguage')
        ->with('de')
        ->willReturn($this->createMock(\Papaya\CMS\Content\Language::class));
      $page = new Page(42, 'de');
      $page->papaya($this->mockPapaya()->application(['languages' => $languages]));
      $this->assertInstanceOf(\Papaya\CMS\Content\Language::class, $page->getPageLanguage());
      $this->assertSame($page->getPageLanguage(), $page->getPageLanguage());
    }

    public function testGetPageLanguageFromApplicationFailed() {
      $languages = $this->createMock(\Papaya\CMS\Content\Languages::class);
      $languages
        ->expects($this->once())
        ->method('getLanguage')
        ->with('de')
        ->willReturn(NULL);
      $page = new Page(42, 'de');
      $page->papaya($this->mockPapaya()->application(['languages' => $languages]));
      $this->assertNull($page->getPageLanguage());
      $this->assertNull($page->getPageLanguage());
    }

    public function testGetReferenceAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Reference\Page $reference */
      $reference = $this->createMock(Reference\Page::class);
      $page = new Page(42, 'de');
      $this->assertSame($reference, $page->reference($reference));
    }

    public function testGetReferenceImplicitCreate() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Reference\Page $reference */
      $reference = $this->createMock(Reference\Page::class);
      $page = new Page(42, 'de');
      $page->papaya($papaya = $this->mockPapaya()->application());
      $this->assertSame($papaya, $page->reference()->papaya());
    }

    public function testAppendQuoteTo() {
      $page = new Page(42, 'de');

      $pluginContext = $this->createMock(Parameters::class);
      $pluginContext
        ->expects($this->once())
        ->method('merge')
        ->with(['query_string' => 'some=data']);

      $pagePlugin = $this->createMock(PagePlugin_TestDummy::class);
      $pagePlugin
        ->method('configuration')
        ->willReturn($pluginContext);
      $pagePlugin
        ->expects($this->once())
        ->method('appendQuoteTo')
        ->withAnyParameters()
        ->willReturnCallback(
          static function(XMLElement $parent) {
            $parent->appendElement('some-content');
          }
        );

      $plugins = $this->createMock(PluginLoader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with('guid1234', $page, 'content')
        ->willReturn($pagePlugin);

      $translation = $this->createMock(PageTranslationContent::class);
      $translation
        ->method('__get')
        ->willReturnMap(
          [
            ['moduleGuid', 'guid1234'],
            ['content', 'content'],
            ['viewName', 'test-view']
          ]
        );

      $page->papaya($this->mockPapaya()->application(['plugins' => $plugins]));
      $page->translation($translation);

      $document = new Document();
      $document->appendElement('test');
      $page->appendQuoteTo($document->documentElement, ['query_string' => 'some=data'], ['name' => 'test-view']);

      $this->assertXmlStringEqualsXmlString(
        '<test>
          <teaser 
            page-id="42" 
            href="http://www.test.tld/index.42.html?some=data"
            plugin="Mock_PagePlugin_TestDummy" 
            plugin-guid="guid1234" 
            view="test-view"
            created="1970-01-01 00:00:00+0000 Thu" 
            published="1970-01-01 00:00:00+0000 Thu">
            <some-content/>
          </teaser>
        </test>',
        preg_replace('((Mock_PagePlugin_TestDummy)(_[a-z\d]+))', '$1', $document->saveXML())
      );
    }

    public function testAppendQuoteToWithEmptyQuote() {
      $page = new Page(42, 'de');

      $pluginContext = $this->createMock(Parameters::class);
      $pluginContext
        ->expects($this->once())
        ->method('merge')
        ->with(['query_string' => 'some=data']);

      $pagePlugin = $this->createMock(PagePlugin_TestDummy::class);
      $pagePlugin
        ->method('configuration')
        ->willReturn($pluginContext);
      $pagePlugin
        ->expects($this->once())
        ->method('appendQuoteTo')
        ->withAnyParameters();

      $plugins = $this->createMock(PluginLoader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with('guid1234', $page, 'content')
        ->willReturn($pagePlugin);

      $translation = $this->createMock(PageTranslationContent::class);
      $translation
        ->method('__get')
        ->willReturnMap(
          [
            ['moduleGuid', 'guid1234'],
            ['content', 'content'],
            ['viewName', 'test-view']
          ]
        );

      $page->papaya($this->mockPapaya()->application(['plugins' => $plugins]));
      $page->translation($translation);

      $document = new Document();
      $document->appendElement('test');
      $page->appendQuoteTo($document->documentElement, ['query_string' => 'some=data']);

      $this->assertXmlStringEqualsXmlString(
        '<test/>',
        preg_replace('((Mock_PagePlugin_TestDummy)(_[a-z\d]+))', '$1', $document->saveXML())
      );
    }
  }

  abstract class PagePlugin_TestDummy implements
    PageModule,
    EditablePlugin, ContextAwarePlugin,
    AppendablePlugin, QuotablePlugin {

  }
}
