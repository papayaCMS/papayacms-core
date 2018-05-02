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

class PapayaUiReferencePageTest extends PapayaTestCase {

  /**
  * @covers PapayaUiReferencePage::create
  */
  public function testStaticFunctionCreate() {
    $this->assertInstanceOf(
      PapayaUiReferencePage::class,
      PapayaUiReferencePage::create()
    );
  }

  /**
  * @covers PapayaUiReferencePage::load
  */
  public function testLoad() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaRequest $request */
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getUrl')
      ->will($this->returnValue(new stdClass));
    $request
      ->expects($this->once())
      ->method('getParameterGroupSeparator')
      ->will($this->returnValue('/'));
    $request
      ->expects($this->any())
      ->method('getParameter')
      ->with(
        $this->isType('string'),
        $this->anything(),
        $this->isNull(),
        $this->equalTo(PapayaRequest::SOURCE_PATH)
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
    $reference = new PapayaUiReferencePage();
    $reference->load($request);
    $this->assertEquals(
      array(
        'title' => 'sample',
        'category_id' => 0,
        'id' => 42,
        'language' => 'en',
        'mode' => 'pdf',
        'preview' => TRUE,
        'preview_time' => 1800
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
  * @covers PapayaUiReferencePage::get
  */
  public function testGetDefault() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $this->assertEquals(
      'http://www.sample.tld/index.html',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::get
  */
  public function testGetWithParameters() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $reference->setParameters(array('foo' => 'bar', 'bar' => 'foo'));
    $this->assertEquals(
      'http://www.sample.tld/index.html?bar=foo&foo=bar',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::setPageId
  * @covers PapayaUiReferencePage::get
  */
  public function testSetPageId() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setPageId(42, FALSE)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 42,
        'language' => '',
        'mode' => 'html',
        'preview' => FALSE,
        'preview_time' => 0
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.42.html',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::setPageId
  * @covers PapayaUiReferencePage::get
  */
  public function testSetPageIdCallsConfigure() {
    $pageReferences = $this->createMock(PapayaUiReferencePageFactory::class);
    $pageReferences
      ->expects($this->once())
      ->method('configure')
      ->with($this->isInstanceOf(PapayaUiReferencePage::class));

    $reference = new PapayaUiReferencePage();
    $reference->papaya(
      $this->mockPapaya()->application(array('pageReferences' => $pageReferences))
    );
    $reference->url($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setPageId(42)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 42,
        'language' => '',
        'mode' => 'html',
        'preview' => FALSE,
        'preview_time' => 0
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.42.html',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::getPageId
  */
  public function testGetPageId() {
    $reference = new PapayaUiReferencePage();
    $this->assertSame(0, $reference->getPageId());
  }

  /**
  * @covers PapayaUiReferencePage::setCategoryId
  * @covers PapayaUiReferencePage::get
  */
  public function testSetCategoryId() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setCategoryId(42)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 42,
        'id' => 0,
        'language' => '',
        'mode' => 'html',
        'preview' => FALSE,
        'preview_time' => 0
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.42.0.html',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::setCategoryId
  * @covers PapayaUiReferencePage::get
  */
  public function testSetCategoryIdToZero() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setCategoryId(0)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 0,
        'language' => '',
        'mode' => 'html',
        'preview' => FALSE,
        'preview_time' => 0
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.html',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::setPageTitle
  * @covers PapayaUiReferencePage::get
  */
  public function testSetPageTitle() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setPageTitle('sample')
    );
    $this->assertEquals(
      array(
        'title' => 'sample',
        'category_id' => 0,
        'id' => 0,
        'language' => '',
        'mode' => 'html',
        'preview' => FALSE,
        'preview_time' => 0
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/sample.html',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::getPageTitle
  */
  public function testGetPageTitle() {
    $reference = new PapayaUiReferencePage();
    $this->assertSame('index', $reference->getPageTitle());
  }

  /**
  * @covers PapayaUiReferencePage::setPageLanguage
  * @covers PapayaUiReferencePage::get
  */
  public function testSetPageLanguage() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setPageLanguage('de')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 0,
        'language' => 'de',
        'mode' => 'html',
        'preview' => FALSE,
        'preview_time' => 0
      ),
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

  /**
  * @covers PapayaUiReferencePage::setPageLanguage
  * @covers PapayaUiReferencePage::get
  */
  public function testSetPageLanguageCallsConfigure() {
    $pageReferences = $this->createMock(PapayaUiReferencePageFactory::class);
    $pageReferences
      ->expects($this->once())
      ->method('configure')
      ->with($this->isInstanceOf(PapayaUiReferencePage::class));

    $reference = new PapayaUiReferencePage();
    $reference->papaya(
      $this->mockPapaya()->application(array('pageReferences' => $pageReferences))
    );
    $reference->url($this->getUrlObjectMockFixture());
    $reference->setPageId(42, FALSE);
    $this->assertSame(
      $reference,
      $reference->setPageLanguage('de')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 42,
        'language' => 'de',
        'mode' => 'html',
        'preview' => FALSE,
        'preview_time' => 0
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.42.de.html',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::getPageLanguage
  */
  public function testGetPageLanguageAfterSet() {
    $reference = new PapayaUiReferencePage();
    $this->assertEquals('', $reference->getPageLanguage());
  }

  /**
  * @covers PapayaUiReferencePage::setOutputMode
  * @covers PapayaUiReferencePage::get
  */
  public function testSetOutputMode() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setOutputMode('pdf')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 0,
        'language' => '',
        'mode' => 'pdf',
        'preview' => FALSE,
        'preview_time' => 0
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.pdf',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::setPreview
  * @covers PapayaUiReferencePage::get
  */
  public function testSetPreview() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setPreview(TRUE, 23)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 0,
        'language' => '',
        'mode' => 'html',
        'preview' => TRUE,
        'preview_time' => 23
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.html.preview.23',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::setPreview
  * @covers PapayaUiReferencePage::get
  */
  public function testRepeatingSetPreviewKeepsPreviewTime() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $reference->setPreview(TRUE, 23);
    $reference->setPreview(TRUE);
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 0,
        'language' => '',
        'mode' => 'html',
        'preview' => TRUE,
        'preview_time' => 23
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.html.preview.23',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::setPreview
  * @covers PapayaUiReferencePage::get
  */
  public function testRepeatingSetPreviewRemovesPreviewTimeIfDisabled() {
    $reference = new PapayaUiReferencePage();
    $reference->url($this->getUrlObjectMockFixture());
    $reference->setPreview(TRUE, 23);
    $reference->setPreview(FALSE);
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 0,
        'language' => '',
        'mode' => 'html',
        'preview' => FALSE,
        'preview_time' => 0
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.html',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferencePage::get
  */
  public function testSetFragment() {
    $reference = new PapayaUiReferencePage();
    $reference->url(new PapayaUrl('http://www.sample.tld/index.html'));
    $this->assertSame(
      $reference,
      $reference->setFragment('sample')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'category_id' => 0,
        'id' => 0,
        'language' => '',
        'mode' => 'html',
        'preview' => FALSE,
        'preview_time' => 0
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.html#sample',
      $reference->get()
    );
  }

  /**
   * @covers PapayaUiReferencePage::pageReferences
   */
  public function testPageReferencesGetAfterSet() {
    $factory = $this->createMock(PapayaUiReferencePageFactory::class);
    $reference = new PapayaUiReferencePage();
    $reference->pageReferences($factory);
    $this->assertSame(
      $factory, $reference->pageReferences()
    );
  }

  /**
   * @covers PapayaUiReferencePage::pageReferences
   */
  public function testPageReferencesFetchFromApplication() {
    $application = $this->mockPapaya()->application(
      array(
        'pageReferences' => $factory = $this->createMock(PapayaUiReferencePageFactory::class)
      )
    );
    $reference = new PapayaUiReferencePage();
    $reference->papaya($application);
    $this->assertSame(
      $factory, $reference->pageReferences()
    );
  }

  /**
   * @covers PapayaUiReferencePage::pageReferences
   */
  public function testPageReferencesExpectingNull() {
    $reference = new PapayaUiReferencePage();
    $this->assertNull(
      $reference->pageReferences()
    );
  }

  /**
   * @covers PapayaUiReferencePage::isStartPage
   */
  public function testIsStartPageWithoutPageReferencesExpectingFalse() {
    $reference = new PapayaUiReferencePage();
    $this->assertFalse(
      $reference->isStartPage()
    );
  }

  /**
   * @covers PapayaUiReferencePage::isStartPage
   */
  public function testIsStartPageExpectingTrue() {
    $references = $this->createMock(PapayaUiReferencePageFactory::class);
    $references
      ->expects($this->once())
      ->method('isStartPage')
      ->willReturn(TRUE);

    $reference = new PapayaUiReferencePage();
    $reference->papaya($this->mockPapaya()->application());
    $reference->pageReferences($references);
    $this->assertTrue(
      $reference->isStartPage()
    );
  }

  /**
   * @covers PapayaUiReferencePage::isStartPage
   */
  public function testIsStartPageExpectingFalse() {
    $references = $this->createMock(PapayaUiReferencePageFactory::class);
    $references
      ->expects($this->once())
      ->method('isStartPage')
      ->willReturn(FALSE);

    $reference = new PapayaUiReferencePage();
    $reference->papaya($this->mockPapaya()->application());
    $reference->pageReferences($references);
    $this->assertFalse(
      $reference->isStartPage()
    );
  }

  /**********************************
  * Fixtures
  **********************************/

  private function getUrlObjectMockFixture() {
    $url = $this->createMock(PapayaUrl::class);
    $url
      ->expects($this->once())
      ->method('getHostUrl')
      ->will($this->returnValue('http://www.sample.tld'));
    return $url;
  }
}
