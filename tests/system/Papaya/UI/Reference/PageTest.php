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

namespace Papaya\UI\Reference {

  use Papaya\Request;
  use Papaya\TestCase;
  use Papaya\URL;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Reference\Page
   */
  class PageTest extends TestCase {

    public function testStaticFunctionCreate() {
      $this->assertInstanceOf(
        Page::class,
        Page::create()
      );
    }

    public function testLoad() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Request $request */
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getUrl')
        ->willReturn(new \stdClass());
      $request
        ->expects($this->once())
        ->method('getParameterGroupSeparator')
        ->willReturn('/');
      $request
        ->method('getParameter')
        ->with(
          $this->isType('string'),
          $this->anything(),
          $this->isNull(),
          $this->equalTo(Request::SOURCE_PATH)
        )
        ->will(
          $this->onConsecutiveCalls(
            $this->returnValue('sample'),
            $this->returnValue(42),
            $this->returnValue('en'),
            $this->returnValue('pdf'),
            $this->returnValue(TRUE),
            $this->returnValue(1800)
          )
        );
      $reference = new Page();
      $reference->load($request);
      $this->assertEquals(
        [
          'title' => 'sample',
          'category_id' => 0,
          'id' => 42,
          'language' => 'en',
          'mode' => 'pdf',
          'preview' => TRUE,
          'preview_time' => 1800
        ],
        $this->readAttribute($reference, '_pageData')
      );
    }

    public function testGetDefault() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $this->assertEquals(
        'http://www.sample.tld/index.html',
        $reference->get()
      );
    }

    public function testGetWithParameters() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $reference->setParameters(['foo' => 'bar', 'bar' => 'foo']);
      $this->assertEquals(
        'http://www.sample.tld/index.html?bar=foo&foo=bar',
        $reference->get()
      );
    }

    public function testSetPageId() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setPageId(42, FALSE)
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 42,
          'language' => '',
          'mode' => 'html',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.42.html',
        $reference->get()
      );
    }

    public function testSetPageIdCallsConfigure() {
      $pageReferences = $this->createMock(Page\Factory::class);
      $pageReferences
        ->expects($this->once())
        ->method('configure')
        ->with($this->isInstanceOf(Page::class));

      $reference = new Page();
      $reference->papaya(
        $this->mockPapaya()->application(['pageReferences' => $pageReferences])
      );
      $reference->url($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setPageId(42)
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 42,
          'language' => '',
          'mode' => 'html',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.42.html',
        $reference->get()
      );
    }

    public function testGetPageId() {
      $reference = new Page();
      $this->assertSame(0, $reference->getPageId());
    }

    public function testSetCategoryId() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setCategoryId(42)
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 42,
          'id' => 0,
          'language' => '',
          'mode' => 'html',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.42.0.html',
        $reference->get()
      );
    }

    public function testSetCategoryIdToZero() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setCategoryId(0)
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 0,
          'language' => '',
          'mode' => 'html',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.html',
        $reference->get()
      );
    }

    public function testSetPageTitle() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setPageTitle('sample')
      );
      $this->assertEquals(
        [
          'title' => 'sample',
          'category_id' => 0,
          'id' => 0,
          'language' => '',
          'mode' => 'html',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/sample.html',
        $reference->get()
      );
    }
    public function testSetPageTitleTriggerNormalization() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $reference->setPageTitle('sample with äöü');
      $this->assertSame(
        'sample-with-aou',
        $reference->getPageTitle()
      );
    }

    public function testGetPageTitle() {
      $reference = new Page();
      $this->assertSame('index', $reference->getPageTitle());
    }

    public function testSetPageLanguage() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setPageLanguage('de')
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 0,
          'language' => 'de',
          'mode' => 'html',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.de.html',
        $reference->get()
      );
      $this->assertEquals(
        'de',
        $reference->getPageLanguage()
      );
    }

    public function testSetPageLanguageCallsConfigure() {
      $pageReferences = $this->createMock(Page\Factory::class);
      $pageReferences
        ->expects($this->once())
        ->method('configure')
        ->with($this->isInstanceOf(Page::class));

      $reference = new Page();
      $reference->papaya(
        $this->mockPapaya()->application(['pageReferences' => $pageReferences])
      );
      $reference->url($this->getUrlObjectMockFixture());
      $reference->setPageId(42, FALSE);
      $this->assertSame(
        $reference,
        $reference->setPageLanguage('de')
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 42,
          'language' => 'de',
          'mode' => 'html',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.42.de.html',
        $reference->get()
      );
    }

    public function testGetPageLanguageAfterSet() {
      $reference = new Page();
      $this->assertEquals('', $reference->getPageLanguage());
    }

    public function testSetOutputMode() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setOutputMode('pdf')
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 0,
          'language' => '',
          'mode' => 'pdf',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertSame('pdf', $reference->getOutputMode());
      $this->assertSame(
        'http://www.sample.tld/index.pdf',
        $reference->get()
      );
    }

    public function testSetPreview() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setPreview(TRUE, 23)
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 0,
          'language' => '',
          'mode' => 'html',
          'preview' => TRUE,
          'preview_time' => 23
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.html.preview.23',
        $reference->get()
      );
    }

    public function testRepeatingSetPreviewKeepsPreviewTime() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $reference->setPreview(TRUE, 23);
      $reference->setPreview(TRUE);
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 0,
          'language' => '',
          'mode' => 'html',
          'preview' => TRUE,
          'preview_time' => 23
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.html.preview.23',
        $reference->get()
      );
    }

    public function testRepeatingSetPreviewRemovesPreviewTimeIfDisabled() {
      $reference = new Page();
      $reference->url($this->getUrlObjectMockFixture());
      $reference->setPreview(TRUE, 23);
      $reference->setPreview(FALSE);
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 0,
          'language' => '',
          'mode' => 'html',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.html',
        $reference->get()
      );
    }

    public function testSetFragment() {
      $reference = new Page();
      $reference->url(new URL('http://www.sample.tld/index.html'));
      $this->assertSame(
        $reference,
        $reference->setFragment('sample')
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'category_id' => 0,
          'id' => 0,
          'language' => '',
          'mode' => 'html',
          'preview' => FALSE,
          'preview_time' => 0
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.html#sample',
        $reference->get()
      );
    }

    public function testPageReferencesGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Page\Factory $factory */
      $factory = $this->createMock(Page\Factory::class);
      $reference = new Page();
      $reference->pageReferences($factory);
      $this->assertSame(
        $factory, $reference->pageReferences()
      );
    }

    public function testPageReferencesFetchFromApplication() {
      $application = $this->mockPapaya()->application(
        [
          'pageReferences' => $factory = $this->createMock(Page\Factory::class)
        ]
      );
      $reference = new Page();
      $reference->papaya($application);
      $this->assertSame(
        $factory, $reference->pageReferences()
      );
    }

    public function testPageReferencesExpectingNull() {
      $reference = new Page();
      $this->assertNull(
        $reference->pageReferences()
      );
    }

    public function testIsStartPageWithoutPageReferencesExpectingFalse() {
      $reference = new Page();
      $this->assertFalse(
        $reference->isStartPage()
      );
    }

    public function testIsStartPageExpectingTrue() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Page\Factory $references */
      $references = $this->createMock(Page\Factory::class);
      $references
        ->expects($this->once())
        ->method('isStartPage')
        ->willReturn(TRUE);

      $reference = new Page();
      $reference->papaya($this->mockPapaya()->application());
      $reference->pageReferences($references);
      $this->assertTrue(
        $reference->isStartPage()
      );
    }

    public function testIsStartPageExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Page\Factory $references */
      $references = $this->createMock(Page\Factory::class);
      $references
        ->expects($this->once())
        ->method('isStartPage')
        ->willReturn(FALSE);

      $reference = new Page();
      $reference->papaya($this->mockPapaya()->application());
      $reference->pageReferences($references);
      $this->assertFalse(
        $reference->isStartPage()
      );
    }

    /**********************************
     * Fixtures
     **********************************/

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|URL
     */
    private function getUrlObjectMockFixture() {
      $url = $this->createMock(URL::class);
      $url
        ->method('getHostUrl')
        ->willReturn('http://www.sample.tld');
      return $url;
    }
  }
}
